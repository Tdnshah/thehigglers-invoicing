<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class Company extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'gst_number',
        'address',
        'phone',
        'email',
        'website',
        'logo_path',
        'bank_name',
        'bank_account_number',
        'bank_ifsc',
        'custom_fields',
        'invoice_number_format',
        'quotation_number_format',
        'terms_conditions',
    ];

    protected $casts = [
        'custom_fields' => 'array',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function bankAccounts()
    {
        return $this->hasMany(BankAccount::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * The account a document uses when it has not picked one.
     */
    public function defaultBankAccount(): ?BankAccount
    {
        $accounts = $this->relationLoaded('bankAccounts') ? $this->bankAccounts : $this->bankAccounts()->get();

        return $accounts->firstWhere('is_default', true) ?? $accounts->first();
    }

    /**
     * LUT registrations kept in the company custom fields, e.g.
     * "LUT Number - 2026-2027" => "AD270426032855V". An export document quotes one
     * of these instead of a place of supply.
     *
     * @return \Illuminate\Support\Collection<int, array{label: string, value: string}>
     */
    public function lutOptions(): Collection
    {
        return collect($this->custom_fields ?? [])
            ->filter(fn ($field) => is_array($field)
                && filled($field['key'] ?? null)
                && filled($field['value'] ?? null)
                && str_contains(strtolower($field['key']), 'lut'))
            ->map(fn ($field) => ['label' => $field['key'], 'value' => $field['value']])
            ->values();
    }

    /**
     * The logo as a base64 data URI so it embeds in a PDF without a file lookup.
     */
    public function logoDataUri(): ?string
    {
        if (! $this->logo_path) {
            return null;
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($this->logo_path)) {
            return null;
        }

        $mime = $disk->mimeType($this->logo_path) ?: 'image/png';

        return 'data:' . $mime . ';base64,' . base64_encode($disk->get($this->logo_path));
    }
}
