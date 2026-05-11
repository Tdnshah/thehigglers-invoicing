<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
{
    /**
     * Show the form for editing the company settings.
     */
    public function edit()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->isCompanyAdmin()) {
            abort(403);
        }

        $company = $user->company;
        return view('company.edit', compact('company'));
    }

    /**
     * Update the company settings.
     */
    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->isCompanyAdmin()) {
            abort(403);
        }

        $company = $user->company;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'gst_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'required|string',
            'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_ifsc' => 'nullable|string|max:20',
            'custom_fields' => 'nullable|array',
            'custom_fields.*.key' => 'required|string|max:255',
            'custom_fields.*.value' => 'nullable|string|max:255',
            'custom_fields.*.show_in_invoice' => 'nullable|boolean',
            'custom_fields.*.show_in_quotation' => 'nullable|boolean',
        ]);

        $customFields = [];
        foreach ($request->input('custom_fields', []) as $field) {
            if (!empty($field['key'])) {
                $customFields[] = [
                    'key' => $field['key'],
                    'value' => $field['value'] ?? '',
                    'show_in_invoice' => !empty($field['show_in_invoice']),
                    'show_in_quotation' => !empty($field['show_in_quotation']),
                ];
            }
        }

        $data = [
            'name' => $validated['name'],
            'gst_number' => $validated['gst_number'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'bank_name' => $validated['bank_name'],
            'bank_account_number' => $validated['bank_account_number'],
            'bank_ifsc' => $validated['bank_ifsc'],
            'custom_fields' => $customFields,
        ];

        if ($request->hasFile('company_logo')) {
            // Delete old logo if exists
            if ($company->logo_path) {
                Storage::disk('public')->delete($company->logo_path);
            }
            $path = $request->file('company_logo')->store('company-logos', 'public');
            $data['logo_path'] = $path;
        }

        $company->update($data);

        return redirect()->back()->with('success', 'Company settings updated successfully.');
    }
}
