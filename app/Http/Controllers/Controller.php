<?php

namespace App\Http\Controllers;

abstract class Controller
{
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
