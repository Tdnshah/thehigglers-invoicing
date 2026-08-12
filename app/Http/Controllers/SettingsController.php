<?php

namespace App\Http\Controllers;

use App\Services\DocumentNumberGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Company settings, split into addressable sections.
 *
 * Each section is its own URL and its own form, so a save touches only the
 * fields on screen, feedback names what changed, and a validation error returns
 * to the section being edited instead of the top of a single long page.
 */
class SettingsController extends Controller
{
    /**
     * The section registry drives the navigation, the titles, and routing.
     * Adding a section means adding an entry and a view partial.
     */
    public const SECTIONS = [
        'company' => [
            'group' => 'Organisation',
            'label' => 'Company Information',
            'description' => 'The identity printed on every quotation and invoice.',
            'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
        ],
        'bank' => [
            'group' => 'Organisation',
            'label' => 'Bank Details',
            'description' => 'One account per way you get paid. Documents pick one.',
            'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
        ],
        'terms' => [
            'group' => 'Documents',
            'label' => 'Terms & Conditions',
            'description' => 'The global terms each document can inherit.',
            'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        ],
        'numbering' => [
            'group' => 'Documents',
            'label' => 'Numbering',
            'description' => 'How invoice and quotation numbers are built.',
            'icon' => 'M7 20l4-16m2 16l4-16M6 9h14M4 15h14',
        ],
        'custom-fields' => [
            'group' => 'Documents',
            'label' => 'Custom Fields',
            'description' => 'Extra fields such as LUT registrations, selectable per document.',
            'icon' => 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z',
        ],
    ];

    public function show(string $section)
    {
        $this->guard($section);

        return view('settings.index', [
            'section' => $section,
            'meta' => self::SECTIONS[$section],
            'sections' => self::SECTIONS,
            'company' => Auth::user()->company,
        ]);
    }

    public function update(Request $request, string $section)
    {
        $this->guard($section);

        $company = Auth::user()->company;

        $data = match ($section) {
            'company' => $this->companyData($request, $company),
            'bank' => $this->syncBankAccounts($request, $company),
            'terms' => $request->validate([
                'terms_conditions' => 'nullable|string|max:5000',
            ]),
            'numbering' => $this->numberingData($request),
            'custom-fields' => ['custom_fields' => $this->customFieldsData($request)],
        };

        if ($data instanceof \Illuminate\Http\RedirectResponse) {
            return $data;
        }

        if ($data !== []) {
            $company->update($data);
        }

        return redirect()
            ->route('settings.show', $section)
            ->with('success', self::SECTIONS[$section]['label'] . ' updated.');
    }

    protected function companyData(Request $request, $company): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'gst_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'website' => 'nullable|string|max:255',
            'address' => 'required|string',
            'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $data = collect($validated)->except('company_logo')->all();

        if ($request->hasFile('company_logo')) {
            if ($company->logo_path) {
                Storage::disk('public')->delete($company->logo_path);
            }
            $data['logo_path'] = $request->file('company_logo')->store('company-logos', 'public');
        }

        return $data;
    }

    /**
     * @return array|\Illuminate\Http\RedirectResponse
     */
    protected function numberingData(Request $request)
    {
        $validated = $request->validate([
            'invoice_number_format' => 'nullable|string|max:60',
            'quotation_number_format' => 'nullable|string|max:60',
        ]);

        // A bad format would produce non compliant document numbers.
        $generator = app(DocumentNumberGenerator::class);

        foreach (['invoice_number_format', 'quotation_number_format'] as $field) {
            if ($error = $generator->validationError($validated[$field] ?? null)) {
                return back()->withInput()->withErrors([$field => $error]);
            }
        }

        return [
            'invoice_number_format' => $validated['invoice_number_format'] ?? null,
            'quotation_number_format' => $validated['quotation_number_format'] ?? null,
        ];
    }

    /**
     * Replace the company's accounts with what the form submitted: rows with an
     * id are updated, rows without are created, and anything missing from the
     * payload was removed on screen. Documents already pointing at a deleted
     * account fall back to the default, because the foreign key nulls out.
     *
     * Returns [] because the accounts live in their own table, not on companies.
     */
    protected function syncBankAccounts(Request $request, $company): array
    {
        $validated = $request->validate([
            'accounts' => 'nullable|array',
            'accounts.*.id' => 'nullable|integer',
            'accounts.*.label' => 'required|string|max:80',
            'accounts.*.bank_name' => 'nullable|string|max:255',
            'accounts.*.account_number' => 'nullable|string|max:50',
            'accounts.*.ifsc' => 'nullable|string|max:20',
            'accounts.*.swift' => 'nullable|string|max:20',
            'accounts.*.iban' => 'nullable|string|max:40',
            'accounts.*.branch' => 'nullable|string|max:255',
            'default_account' => 'nullable|string|max:20',
        ], [
            'accounts.*.label.required' => 'Every bank account needs a name.',
        ]);

        $rows = $validated['accounts'] ?? [];
        $default = $validated['default_account'] ?? null;
        $keptIds = [];
        $defaultId = null;

        DB::transaction(function () use ($rows, $company, $default, &$keptIds, &$defaultId) {
            foreach (array_values($rows) as $index => $row) {
                $attributes = [
                    'label' => $row['label'],
                    'bank_name' => $row['bank_name'] ?? null,
                    'account_number' => $row['account_number'] ?? null,
                    'ifsc' => $row['ifsc'] ?? null,
                    'swift' => $row['swift'] ?? null,
                    'iban' => $row['iban'] ?? null,
                    'branch' => $row['branch'] ?? null,
                    'sort_order' => $index,
                    'is_default' => false,
                ];

                $account = filled($row['id'] ?? null)
                    ? $company->bankAccounts()->whereKey($row['id'])->first()
                    : null;

                if ($account) {
                    $account->update($attributes);
                } else {
                    $account = $company->bankAccounts()->create($attributes);
                }

                $keptIds[] = $account->id;

                // The radio carries either an existing id or "new-<index>".
                if ($default === (string) ($row['id'] ?? '') && filled($row['id'] ?? null)) {
                    $defaultId = $account->id;
                } elseif ($default === 'new-' . $index) {
                    $defaultId = $account->id;
                }
            }

            $company->bankAccounts()->whereNotIn('id', $keptIds ?: [0])->delete();

            $defaultId = $defaultId ?? ($keptIds[0] ?? null);

            if ($defaultId) {
                $company->bankAccounts()->whereKey($defaultId)->update(['is_default' => true]);
            }
        });

        return [];
    }

    protected function customFieldsData(Request $request): array
    {
        $request->validate([
            'custom_fields' => 'nullable|array',
            'custom_fields.*.key' => 'required|string|max:255',
            'custom_fields.*.value' => 'nullable|string|max:255',
        ]);

        $fields = [];

        foreach ($request->input('custom_fields', []) as $field) {
            if (! empty($field['key'])) {
                $fields[] = [
                    'key' => $field['key'],
                    'value' => $field['value'] ?? '',
                    'show_in_invoice' => ! empty($field['show_in_invoice']),
                    'show_in_quotation' => ! empty($field['show_in_quotation']),
                ];
            }
        }

        return $fields;
    }

    protected function guard(string $section): void
    {
        abort_unless(array_key_exists($section, self::SECTIONS), 404);
        abort_unless(Auth::user()->isCompanyAdmin(), 403);
    }
}
