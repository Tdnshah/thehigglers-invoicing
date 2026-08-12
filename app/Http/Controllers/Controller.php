<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * The record page section to render, taken from ?tab= and checked against
     * what this viewer is allowed to see. Anything unknown falls back to the
     * first allowed tab rather than 404ing on a stale bookmark.
     */
    protected function resolveTab(?string $requested, array $allowed): string
    {
        return in_array($requested, $allowed, true) ? $requested : ($allowed[0] ?? 'document');
    }

    /**
     * Get company custom fields filtered by a visibility flag (show_in_invoice / show_in_quotation).
     */
    protected function getCompanyCustomFields($user, string $visibility): \Illuminate\Support\Collection
    {
        $company = $user->company;
        if (!$company) {
            return collect();
        }

        $rawFields = $company->custom_fields ?? [];

        return collect($rawFields)->filter(function ($field) use ($visibility) {
            if (!is_array($field)) {
                return false;
            }
            return !empty($field[$visibility]);
        })->values();
    }

    /**
     * Normalize document level bank rows from a form submission.
     * Each element: ['label' => '...', 'value' => '...']
     */
    protected function normalizeBankRows(array $rawRows): array
    {
        return collect($rawRows)
            ->filter(fn ($row) => !empty($row['label']) && !empty($row['value']))
            ->map(fn ($row) => ['label' => $row['label'], 'value' => $row['value']])
            ->values()
            ->toArray();
    }

    /**
     * Validation rules shared by the invoice and quotation forms for the
     * global / custom / both switches on terms and bank details.
     */
    protected function documentPresentationRules(): array
    {
        return [
            'terms_mode' => 'nullable|in:global,custom,both,none',
            'terms_conditions' => 'nullable|string|max:5000',
            'bank_mode' => 'nullable|in:account,global,custom,both,none',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'bank_details' => 'nullable|array',
            'bank_details.*.label' => 'nullable|string|max:60',
            'bank_details.*.value' => 'nullable|string|max:120',
        ];
    }

    /**
     * Normalize the raw custom_fields input from a form submission into a clean array.
     * Each element: ['key' => '...', 'value' => '...']
     */
    protected function normalizeDocumentCustomFields(array $rawFields): array
    {
        return collect($rawFields)
            ->filter(fn($f) => !empty($f['key']))
            ->map(fn($f) => ['key' => $f['key'], 'value' => $f['value'] ?? ''])
            ->values()
            ->toArray();
    }
}
