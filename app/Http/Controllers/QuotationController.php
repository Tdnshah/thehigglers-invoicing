<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Quotation;
use App\Models\QuotationItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Services\DocumentNumberGenerator;
use App\Services\DocumentRenderer;

class QuotationController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->isCompanyAdmin()) {
            $quotations = Quotation::where('user_id', $user->id)
                ->whereNull('parent_id')
                ->with(['client', 'revisions', 'invoice'])
                ->latest()
                ->paginate(10);
        } elseif ($user->isClientUser()) {
            $quotations = Quotation::where('client_id', $user->client_id)
                ->whereNull('parent_id')
                ->with(['client', 'revisions', 'invoice'])
                ->latest()
                ->paginate(10);
        } else {
            $quotations = collect([]); 
        }

        return view('quotations.index', compact('quotations'));
    }

    public function create(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->isCompanyAdmin()) {
            abort(403, 'Only administrators can create quotations.');
        }

        $clients = $user->clients;
        $companyCustomFields = $this->getCompanyCustomFields($user, 'show_in_quotation');
        $existingCF = collect();

        $sourceQuotation = null;
        if ($request->has('source_id')) {
            $sourceQuotation = Quotation::with('items')->where('user_id', $user->id)->findOrFail($request->source_id);
            if ($sourceQuotation->isLocked()) {
                return redirect()->route('quotations.show', $sourceQuotation)->with('error', 'This quotation series is locked because a version has already been approved.');
            }
            $existingCF = collect($sourceQuotation->custom_fields ?? [])->keyBy('key');
        }

        $company = $user->company;
        $lutOptions = $company?->lutOptions() ?? collect();

        return view('quotations.create', compact('clients', 'sourceQuotation', 'companyCustomFields', 'existingCF', 'company', 'lutOptions'));
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->isCompanyAdmin()) {
            abort(403, 'Only administrators can create quotations.');
        }

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'quotation_number' => ['nullable', 'string', 'max:' . DocumentNumberGenerator::MAX_LENGTH, 'regex:' . DocumentNumberGenerator::ALLOWED_PATTERN, 'unique:quotations,quotation_number'],
            'quotation_date' => 'required|date',
            'valid_until' => 'nullable|date|after_or_equal:quotation_date',
            'quotation_type' => 'required|string|in:regular,export,interstate',
            'place_of_supply' => 'nullable|string|size:2',
            'lut_number' => 'nullable|required_if:quotation_type,export|string',
            'currency' => 'required|string|size:3',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.hsn_code' => 'nullable|string',
            'items.*.tax_rate' => 'nullable|numeric|in:0,5,12,18,28',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'custom_fields' => 'nullable|array',
            'custom_fields.*.key' => 'required|string|max:100',
            'custom_fields.*.value' => 'nullable|string|max:255',
            'client_notes' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'parent_id' => 'nullable|exists:quotations,id',
            'revision_number' => 'nullable|integer',
        ] + $this->documentPresentationRules());

        $client = Client::findOrFail($validated['client_id']);
        
        if ($client->user_id !== Auth::id()) {
            abort(403);
        }

        $subtotal = 0;
        $cgst = 0;
        $sgst = 0;
        $igst = 0;
        $quotationType = $validated['quotation_type'];

        foreach ($validated['items'] as $item) {
            $itemTotal = $item['quantity'] * $item['unit_price'];
            $subtotal += $itemTotal;
            
            $taxRate = $item['tax_rate'] ?? 0;
            $taxAmount = $itemTotal * ($taxRate / 100);

            if ($quotationType === 'export' || $quotationType === 'interstate') {
                 $igst += $taxAmount; 
            } else {
                 $cgst += $taxAmount / 2;
                 $sgst += $taxAmount / 2;
            }
        }

        $total = $subtotal + $cgst + $sgst + $igst;

        $revisionNumber = 0;
        if (!empty($validated['parent_id'])) {
            $rootParentId = Quotation::where('id', $validated['parent_id'])->value('parent_id') ?? $validated['parent_id'];
            $maxRevision = Quotation::where('id', $rootParentId)
                ->orWhere('parent_id', $rootParentId)
                ->max('revision_number');
            $revisionNumber = max(0, (int)$maxRevision) + 1;
        }

        $quotationNumber = filled($validated['quotation_number'] ?? null)
            ? $validated['quotation_number']
            : app(DocumentNumberGenerator::class)->next(
                DocumentNumberGenerator::QUOTATION,
                $user->company,
                $client,
                \Illuminate\Support\Carbon::parse($validated['quotation_date'])
            );

        $quotation = $user->quotations()->create([
            'client_id' => $client->id,
            'parent_id' => $validated['parent_id'] ?? null,
            'revision_number' => $revisionNumber,
            'quotation_number' => $quotationNumber,
            'quotation_date' => $validated['quotation_date'],
            'valid_until' => $validated['valid_until'] ?? null,
            'quotation_type' => $validated['quotation_type'],
            'place_of_supply' => $validated['place_of_supply'] ?? null,
            'lut_number' => $validated['lut_number'] ?? null,
            'currency' => $validated['currency'],
            'subtotal' => $subtotal,
            'cgst' => $cgst,
            'sgst' => $sgst,
            'igst' => $igst,
            'total' => $total,
            'status' => 'draft',
            'terms_mode' => $validated['terms_mode'] ?? 'global',
            'bank_mode' => $validated['bank_mode'] ?? 'account',
            'bank_account_id' => $validated['bank_account_id'] ?? null,
            'bank_details' => $this->normalizeBankRows($validated['bank_details'] ?? []),
            'client_notes' => $validated['client_notes'] ?? null,
            'terms_conditions' => $validated['terms_conditions'] ?? null,
            'is_active' => empty($validated['parent_id']), // Only V0 is active by default
            'custom_fields' => $this->normalizeDocumentCustomFields($validated['custom_fields'] ?? []),
        ]);

        foreach ($validated['items'] as $item) {
            $quotation->items()->create([
                'description' => $item['description'],
                'hsn_code' => $item['hsn_code'] ?? null,
                'tax_rate' => $item['tax_rate'] ?? 0,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'amount' => $item['quantity'] * $item['unit_price'],
            ]);
        }

        return redirect()->route('quotations.index')->with('success', 'Quotation created successfully.');
    }

    public function show(Quotation $quotation)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->isCompanyAdmin() && $quotation->user_id !== $user->id) {
            abort(403);
        } elseif ($user->isClientUser() && $quotation->client_id !== $user->client_id) {
            abort(403);
        } elseif (!$user->isCompanyAdmin() && !$user->isClientUser()) {
            abort(403);
        }

        $rootId = $quotation->parent_id ?? $quotation->id;
        $revisions = Quotation::where('id', $rootId)
            ->orWhere('parent_id', $rootId)
            ->orderBy('revision_number', 'asc')
            ->get();

        $quotation->load(['client', 'items', 'notes.user', 'invoice']);

        $allowed = array_values(array_filter([
            'document',
            $user->isCompanyAdmin() ? 'actions' : null,
            $user->isCompanyAdmin() ? 'notes' : null,
            'bank',
        ]));

        $tab = $this->resolveTab(request('tab'), $allowed);

        return view('quotations.show', compact('quotation', 'revisions', 'tab'));
    }

    public function markAsActive(Quotation $quotation)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($quotation->user_id !== $user->id) abort(403);

        if ($quotation->isLocked() && !$quotation->isApproved()) {
            return redirect()->back()->with('error', 'The active revision is locked to the approved version.');
        }

        $rootId = $quotation->parent_id ?? $quotation->id;

        // Set all related versions to inactive
        Quotation::where('id', $rootId)
            ->orWhere('parent_id', $rootId)
            ->update(['is_active' => false]);

        // Set this version to active
        $quotation->update(['is_active' => true]);

        return redirect()->back()->with('success', 'Revision V' . $quotation->revision_number . ' marked as active.');
    }

    public function edit(Quotation $quotation)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($quotation->user_id !== $user->id) abort(403);
        if ($quotation->isLocked()) {
            return redirect()->route('quotations.show', $quotation)->with('error', 'This quotation series is locked and cannot be edited.');
        }

        $clients = $user->clients;
        $quotation->load('items');
        $companyCustomFields = $this->getCompanyCustomFields($user, 'show_in_quotation');
        $existingCF = collect($quotation->custom_fields ?? [])->keyBy('key');

        $quotationItems = $quotation->items->map(function($item) {
            return [
                'description' => $item->description,
                'hsn_code' => $item->hsn_code,
                'tax_rate' => (int) $item->tax_rate,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price
            ];
        });

        $company = $user->company;
        $lutOptions = $company?->lutOptions() ?? collect();

        return view('quotations.edit', compact('quotation', 'clients', 'quotationItems', 'companyCustomFields', 'existingCF', 'company', 'lutOptions'));
    }

    public function update(Request $request, Quotation $quotation)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($quotation->user_id !== $user->id) abort(403);
        if ($quotation->isLocked()) {
            abort(403, 'Approved quotations cannot be updated.');
        }

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'quotation_number' => ['required', 'string', 'max:' . DocumentNumberGenerator::MAX_LENGTH, 'regex:' . DocumentNumberGenerator::ALLOWED_PATTERN, 'unique:quotations,quotation_number,' . $quotation->id],
            'quotation_date' => 'required|date',
            'valid_until' => 'nullable|date|after_or_equal:quotation_date',
            'quotation_type' => 'required|string|in:regular,export,interstate',
            'place_of_supply' => 'nullable|string|size:2',
            'lut_number' => 'nullable|required_if:quotation_type,export|string',
            'currency' => 'required|string|size:3',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.hsn_code' => 'nullable|string',
            'items.*.tax_rate' => 'required|numeric|in:0,5,12,18,28',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'custom_fields' => 'nullable|array',
            'custom_fields.*.key' => 'required|string|max:100',
            'custom_fields.*.value' => 'nullable|string|max:255',
            'client_notes' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
        ] + $this->documentPresentationRules());

        $client = Client::findOrFail($validated['client_id']);
         if ($client->user_id !== $user->id) abort(403);

        $subtotal = 0;
        $cgst = 0;
        $sgst = 0;
        $igst = 0;
        $quotationType = $validated['quotation_type'];

        foreach ($validated['items'] as $item) {
            $itemTotal = $item['quantity'] * $item['unit_price'];
            $subtotal += $itemTotal;
            
            $taxRate = $item['tax_rate'] ?? 0;
            $taxAmount = $itemTotal * ($taxRate / 100);

            if ($quotationType === 'export' || $quotationType === 'interstate') {
                 $igst += $taxAmount; 
            } else {
                 $cgst += $taxAmount / 2;
                 $sgst += $taxAmount / 2;
            }
        }

        $total = $subtotal + $cgst + $sgst + $igst;

        $quotation->update([
            'terms_mode' => $validated['terms_mode'] ?? 'global',
            'bank_mode' => $validated['bank_mode'] ?? 'account',
            'bank_account_id' => $validated['bank_account_id'] ?? null,
            'bank_details' => $this->normalizeBankRows($validated['bank_details'] ?? []),
            'client_id' => $client->id,
            'quotation_number' => $validated['quotation_number'],
            'quotation_date' => $validated['quotation_date'],
            'valid_until' => $validated['valid_until'] ?? null,
            'quotation_type' => $validated['quotation_type'],
            'place_of_supply' => $validated['place_of_supply'] ?? null,
            'lut_number' => $validated['lut_number'] ?? null,
            'currency' => $validated['currency'],
            'subtotal' => $subtotal,
            'cgst' => $cgst,
            'sgst' => $sgst,
            'igst' => $igst,
            'total' => $total,
            'client_notes' => $validated['client_notes'] ?? null,
            'terms_conditions' => $validated['terms_conditions'] ?? null,
            'custom_fields' => $this->normalizeDocumentCustomFields($validated['custom_fields'] ?? []),
        ]);

        $quotation->items()->delete();
        foreach ($validated['items'] as $item) {
            $quotation->items()->create([
                'description' => $item['description'],
                'hsn_code' => $item['hsn_code'] ?? null,
                'tax_rate' => $item['tax_rate'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'amount' => $item['quantity'] * $item['unit_price'],
            ]);
        }

        return redirect()->route('quotations.show', $quotation)->with('success', 'Quotation updated successfully.');
    }

    /**
     * @return array<string, string>
     */
    public static function selectableStatuses(Quotation $quotation): array
    {
        // A quotation an invoice was cloned from is a matter of record.
        if ($quotation->isConverted()) {
            return [];
        }

        return [
            'draft' => 'Draft',
            'sent' => 'Sent',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
        ];
    }

    /**
     * Change the status without opening the edit form.
     */
    public function updateStatus(Request $request, Quotation $quotation)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! $user->isCompanyAdmin() || $quotation->user_id !== $user->id) {
            abort(403);
        }

        $allowed = array_keys(self::selectableStatuses($quotation));

        if ($allowed === []) {
            return redirect()->back()->with('error', 'This quotation has been cloned to an invoice. Its status cannot be changed.');
        }

        $validated = $request->validate(['status' => ['required', Rule::in($allowed)]]);

        $quotation->update(['status' => $validated['status']]);

        return redirect()->back()->with('success', 'Quotation marked as ' . $validated['status'] . '.');
    }

    /**
     * Delete a quotation.
     *
     * Approval alone is not a reason to keep a record: what must be protected is
     * a quotation an invoice was cloned from, because a financial document
     * depends on it. Deleting a root takes its revisions with it, so the tree is
     * never left with orphans pointing at a missing parent.
     */
    public function destroy(Quotation $quotation)
    {
        if ($quotation->user_id !== Auth::id()) abort(403);

        $isRoot = $quotation->parent_id === null;
        $tree = $isRoot
            ? Quotation::where('id', $quotation->id)->orWhere('parent_id', $quotation->id)->get()
            : collect([$quotation]);

        if ($converted = $tree->firstWhere('invoice_id', '!=', null)) {
            return redirect()->back()->with(
                'error',
                'This quotation cannot be deleted: ' . $converted->quotation_number . ' has already been cloned to an invoice.'
            );
        }

        $count = $tree->count();

        DB::transaction(function () use ($tree) {
            foreach ($tree as $record) {
                $record->items()->delete();
                $record->notes()->delete();
                $record->delete();
            }
        });

        $message = $count > 1
            ? 'Quotation and its ' . ($count - 1) . ' revision(s) deleted.'
            : 'Quotation deleted.';

        return redirect()->route('quotations.index')->with('success', $message);
    }

    public function convertToInvoice(Quotation $quotation)
    {
        if ($quotation->user_id !== Auth::id()) abort(403);
        if (!$quotation->isApproved() || $quotation->isConverted()) {
            return redirect()->back()->with('error', 'Only approved quotations can be converted to an invoice, and only once.');
        }

        // The clone takes a number from the company's numbering format, like any other invoice.
        $invoiceNumber = app(DocumentNumberGenerator::class)->next(
            DocumentNumberGenerator::INVOICE,
            $quotation->user->company,
            $quotation->client,
            now()
        );

        $invoice = Invoice::create([
            'user_id' => $quotation->user_id,
            'client_id' => $quotation->client_id,
            'invoice_number' => $invoiceNumber,
            'invoice_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(14)->format('Y-m-d'),
            'invoice_type' => $quotation->quotation_type,
            'place_of_supply' => $quotation->place_of_supply,
            // An export invoice is not compliant without the LUT the quotation was priced under.
            'lut_number' => $quotation->lut_number,
            'custom_fields' => $quotation->custom_fields,
            // Coalesced: these columns are NOT NULL, and a quotation predating
            // the presentation settings can still carry a null in memory.
            'terms_mode' => $quotation->terms_mode ?: 'global',
            'terms_conditions' => $quotation->terms_conditions,
            'bank_mode' => $quotation->bank_mode ?: 'account',
            'bank_account_id' => $quotation->bank_account_id,
            'bank_details' => $quotation->bank_details ?? [],
            'currency' => $quotation->currency,
            'subtotal' => $quotation->subtotal,
            'cgst' => $quotation->cgst,
            'sgst' => $quotation->sgst,
            'igst' => $quotation->igst,
            'total' => $quotation->total,
            'status' => 'draft',
            // The source quotation is a relation, not a note; notes stay free for the team.
            'notes' => null,
        ]);

        foreach ($quotation->items as $item) {
            $invoice->items()->create([
                'description' => $item->description,
                'hsn_code' => $item->hsn_code,
                'tax_rate' => $item->tax_rate,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'amount' => $item->amount,
            ]);
        }

        $quotation->update([
            'invoice_id' => $invoice->id,
            'status' => 'approved' // ensure it stays approved even though it's converted
        ]);

        return redirect()->route('invoices.show', $invoice)->with('success', 'Successfully converted quotation to invoice.');
    }

    public function print(Quotation $quotation, DocumentRenderer $renderer)
    {
        $user = Auth::user();
        if ($user->isCompanyAdmin() && $quotation->user_id !== $user->id) abort(403);
        elseif ($user->isClientUser() && $quotation->client_id !== $user->client_id) abort(403);
        
        return $renderer->view($renderer->forQuotation($quotation));
    }

    public function downloadPdf(Quotation $quotation, DocumentRenderer $renderer)
    {
        $user = Auth::user();
        if ($user->isCompanyAdmin() && $quotation->user_id !== $user->id) abort(403);
        elseif ($user->isClientUser() && $quotation->client_id !== $user->client_id) abort(403);

        $document = $renderer->forQuotation($quotation);

        return $renderer->pdf($document)->download($renderer->filename($document));
    }
}
