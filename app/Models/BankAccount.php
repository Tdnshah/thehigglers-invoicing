<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One bank account a company can be paid into. A document attaches the account
 * its client should use, so an export invoice can quote a different account,
 * with SWIFT and IBAN, from a domestic one.
 */
class BankAccount extends Model
{
    protected $fillable = [
        'company_id',
        'label',
        'bank_name',
        'account_number',
        'ifsc',
        'swift',
        'iban',
        'branch',
        'is_default',
        'sort_order',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * The label / value pairs printed in a document's payment panel. Only the
     * fields that are filled appear, so a domestic account does not print an
     * empty SWIFT line.
     *
     * @return array<string, string>
     */
    public function rows(): array
    {
        return array_filter([
            'Bank' => $this->bank_name,
            'Branch' => $this->branch,
            'Account No' => $this->account_number,
            'IFSC' => $this->ifsc,
            'SWIFT' => $this->swift,
            'IBAN' => $this->iban,
        ], fn ($value) => filled($value));
    }

    /**
     * "HDFC Bank ····5678", for pickers where the label alone is ambiguous.
     */
    public function summary(): string
    {
        $tail = $this->account_number ? '····' . mb_substr($this->account_number, -4) : null;

        return trim(implode(' ', array_filter([$this->bank_name, $tail])));
    }
}
