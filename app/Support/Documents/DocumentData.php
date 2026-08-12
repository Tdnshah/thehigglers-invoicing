<?php

namespace App\Support\Documents;

use App\Models\Invoice;
use App\Models\Quotation;
use App\Support\Money;
use App\Support\RichText;
use Illuminate\Support\Collection;

/**
 * A print-ready, template agnostic view of an invoice or a quotation.
 *
 * Templates only ever read from this object, so a new template never has to know
 * whether it is rendering an Invoice model or a Quotation model, and the two
 * documents can never drift apart in what they show.
 */
class DocumentData
{
    public function __construct(
        public string $kind,
        public string $title,
        public string $number,
        public ?string $status,
        public ?string $supplyLabel,
        public array $issuer,
        public array $recipient,
        public array $meta,
        public array $items,
        public array $taxSummary,
        public array $totals,
        public array $bank,
        public ?string $notes,
        public ?string $terms,
        public ?string $declaration,
        public string $currency,
        public string $taxMode = 'cgst_sgst',
        public ?string $downloadUrl = null,
    ) {}

    public static function fromInvoice(Invoice $invoice): self
    {
        $invoice->loadMissing(['client', 'items', 'user.company']);

        $paid = (float) $invoice->payments()->sum('amount');

        return new self(
            kind: 'invoice',
            title: 'Tax Invoice',
            number: (string) $invoice->invoice_number,
            // "sent" is internal workflow state; only states the reader needs are printed.
            status: in_array($invoice->status, ['draft', 'paid', 'overdue'], true) ? $invoice->status : null,
            supplyLabel: self::supplyLabel($invoice->invoice_type),
            issuer: self::issuer($invoice),
            recipient: self::recipient($invoice),
            meta: array_merge([
                ['label' => 'Invoice No', 'value' => $invoice->invoice_number, 'strong' => true],
                ['label' => 'Invoice Date', 'value' => optional($invoice->invoice_date)->format('d M Y')],
                ['label' => 'Due Date', 'value' => optional($invoice->due_date)->format('d M Y')],
                ['label' => 'Place of Supply', 'value' => self::placeOfSupply($invoice->place_of_supply, $invoice->client?->gst_number)],
                ['label' => 'Currency', 'value' => $invoice->currency],
            ], self::customFieldRows($invoice->custom_fields)),
            items: self::items($invoice->items, $invoice->invoice_type, $invoice->currency),
            taxSummary: self::taxSummary($invoice->items, $invoice->invoice_type, $invoice->currency),
            totals: self::totals($invoice, $paid),
            bank: self::bank($invoice),
            // An invoice's notes are internal, so they never reach the printed document.
            notes: null,
            terms: self::terms($invoice),
            declaration: self::declaration($invoice->invoice_type, $invoice->lut_number),
            currency: $invoice->currency ?? 'INR',
            taxMode: self::taxMode($invoice->invoice_type),
        );
    }

    public static function fromQuotation(Quotation $quotation): self
    {
        $quotation->loadMissing(['client', 'items', 'user.company']);

        $meta = [
            ['label' => 'Quotation No', 'value' => $quotation->quotation_number, 'strong' => true],
            ['label' => 'Quotation Date', 'value' => optional($quotation->quotation_date)->format('d M Y')],
            ['label' => 'Valid Until', 'value' => optional($quotation->valid_until)->format('d M Y')],
            ['label' => 'Place of Supply', 'value' => self::placeOfSupply($quotation->place_of_supply, $quotation->client?->gst_number)],
            ['label' => 'Currency', 'value' => $quotation->currency],
        ];

        if ($quotation->revision_number) {
            $meta[] = ['label' => 'Revision', 'value' => 'R' . $quotation->revision_number];
        }

        return new self(
            kind: 'quotation',
            title: 'Quotation',
            number: (string) $quotation->quotation_number,
            status: $quotation->status,
            supplyLabel: self::supplyLabel($quotation->quotation_type),
            issuer: self::issuer($quotation),
            recipient: self::recipient($quotation),
            meta: array_merge($meta, self::customFieldRows($quotation->custom_fields)),
            items: self::items($quotation->items, $quotation->quotation_type, $quotation->currency),
            taxSummary: self::taxSummary($quotation->items, $quotation->quotation_type, $quotation->currency),
            totals: self::totals($quotation),
            bank: self::bank($quotation),
            notes: $quotation->client_notes,
            terms: self::terms($quotation),
            declaration: self::declaration($quotation->quotation_type, $quotation->lut_number ?? null),
            currency: $quotation->currency ?? 'INR',
            taxMode: self::taxMode($quotation->quotation_type),
        );
    }

    public function isInvoice(): bool
    {
        return $this->kind === 'invoice';
    }

    /**
     * Format an amount in this document's currency.
     */
    public function money($amount, bool $withSymbol = false): string
    {
        return $withSymbol
            ? Money::formatWithSymbol($amount, $this->currency)
            : Money::format($amount, $this->currency);
    }

    public function currencySymbol(): string
    {
        return Money::symbol($this->currency);
    }

    /**
     * Meta rows that actually have a value.
     */
    public function metaRows(): array
    {
        return array_values(array_filter($this->meta, fn ($row) => filled($row['value'] ?? null)));
    }

    protected static function issuer(Invoice|Quotation $document): array
    {
        $company = $document->user?->company;

        return [
            'name' => $company?->name ?? config('app.name'),
            'address' => $company?->address,
            'gstin' => $company?->gst_number,
            'email' => $company?->email ?: $document->user?->email,
            'phone' => $company?->phone,
            'website' => $company?->website,
            'logo' => $company?->logoDataUri(),
        ];
    }

    protected static function recipient(Invoice|Quotation $document): array
    {
        $client = $document->client;

        return [
            'name' => $client?->name ?? '',
            'address' => $client?->address,
            'gstin' => $client?->gst_number,
            'email' => $client?->email,
            'phone' => $client?->phone,
        ];
    }

    /**
     * Bank details are company level by default. A document may replace them or
     * add to them, e.g. an export invoice appending SWIFT and IBAN rows to the
     * domestic block. A custom row with the same label as a global one wins.
     */
    protected static function bank(Invoice|Quotation $document): array
    {
        $company = $document->user?->company;
        // "global" is the pre bank-accounts name for "the selected account".
        $mode = $document->bank_mode ?: 'account';
        $mode = $mode === 'global' ? 'account' : $mode;
        $rows = [];

        if (in_array($mode, ['account', 'both'], true)) {
            $account = $document->bankAccount ?: $company?->defaultBankAccount();

            if ($account) {
                $rows = $account->rows();
            }
        }

        if (in_array($mode, ['custom', 'both'], true)) {
            foreach ((array) ($document->bank_details ?? []) as $row) {
                if (is_array($row) && filled($row['label'] ?? null) && filled($row['value'] ?? null)) {
                    $rows[$row['label']] = $row['value'];
                }
            }
        }

        return $rows;
    }

    /**
     * Terms follow the same global / custom / both switch. In "both" the
     * company block is printed first, then the document's own, one blank line
     * apart, so the general terms read before the deal specific ones.
     */
    protected static function terms(Invoice|Quotation $document): ?string
    {
        $company = $document->user?->company;
        $mode = $document->terms_mode ?: 'global';
        $parts = [];

        if (in_array($mode, ['global', 'both'], true) && filled($company?->terms_conditions)) {
            $parts[] = trim($company->terms_conditions);
        }

        if (in_array($mode, ['custom', 'both'], true) && filled($document->terms_conditions)) {
            $parts[] = trim($document->terms_conditions);
        }

        return $parts ? implode("\n\n", $parts) : null;
    }

    /**
     * Line items with the taxable value and tax split resolved per line, so the
     * amount column always adds up to the subtotal shown in the totals block.
     */
    protected static function items(Collection $items, ?string $type, ?string $currency): array
    {
        return $items->values()->map(function ($item, $index) use ($type) {
            $taxable = (float) $item->quantity * (float) $item->unit_price;
            $taxRate = (float) $item->tax_rate;
            $tax = $taxable * $taxRate / 100;
            $split = self::splitTax($tax, $type);

            return [
                'index' => $index + 1,
                'description' => RichText::clean($item->description),
                'hsn_code' => $item->hsn_code,
                'quantity' => rtrim(rtrim(number_format((float) $item->quantity, 2, '.', ''), '0'), '.'),
                'unit_price' => (float) $item->unit_price,
                'tax_rate' => $taxRate,
                'tax_amount' => $tax,
                'cgst' => $split['cgst'],
                'sgst' => $split['sgst'],
                'igst' => $split['igst'],
                'taxable' => $taxable,
                'total' => $taxable + $tax,
            ];
        })->all();
    }

    /**
     * Tax grouped by rate, as required on a GST invoice.
     */
    protected static function taxSummary(Collection $items, ?string $type, ?string $currency): array
    {
        $summary = [];

        foreach ($items as $item) {
            $rate = (float) $item->tax_rate;
            $taxable = (float) $item->quantity * (float) $item->unit_price;
            $split = self::splitTax($taxable * $rate / 100, $type);

            $key = (string) $rate;
            $summary[$key] ??= ['rate' => $rate, 'taxable' => 0.0, 'cgst' => 0.0, 'sgst' => 0.0, 'igst' => 0.0];
            $summary[$key]['taxable'] += $taxable;
            $summary[$key]['cgst'] += $split['cgst'];
            $summary[$key]['sgst'] += $split['sgst'];
            $summary[$key]['igst'] += $split['igst'];
        }

        ksort($summary, SORT_NUMERIC);

        return array_values($summary);
    }

    /**
     * Which tax columns this document uses: a single IGST column for inter-state
     * and export supplies, CGST + SGST for intra-state.
     */
    protected static function taxMode(?string $type): string
    {
        return in_array($type, ['export', 'interstate'], true) ? 'igst' : 'cgst_sgst';
    }

    protected static function splitTax(float $tax, ?string $type): array
    {
        if (in_array($type, ['export', 'interstate'], true)) {
            return ['cgst' => 0.0, 'sgst' => 0.0, 'igst' => $tax];
        }

        return ['cgst' => $tax / 2, 'sgst' => $tax / 2, 'igst' => 0.0];
    }

    protected static function totals(Invoice|Quotation $document, ?float $paid = null): array
    {
        $currency = $document->currency ?? 'INR';
        $total = (float) $document->total;

        $totals = [
            'subtotal' => (float) $document->subtotal,
            'cgst' => (float) $document->cgst,
            'sgst' => (float) $document->sgst,
            'igst' => (float) $document->igst,
            'total' => $total,
            'in_words' => Money::inWords($total, $currency),
            'paid' => null,
            'balance' => null,
        ];

        if ($paid !== null && $paid > 0) {
            $totals['paid'] = $paid;
            $totals['balance'] = $total - $paid;
        }

        return $totals;
    }

    protected static function supplyLabel(?string $type): ?string
    {
        return match ($type) {
            'export' => 'Export Supply',
            'interstate' => 'Inter-State Supply',
            'regular' => 'Intra-State Supply',
            default => null,
        };
    }

    protected static function declaration(?string $type, ?string $lutNumber): ?string
    {
        if ($type !== 'export') {
            return null;
        }

        return 'Supply meant for export of services under Letter of Undertaking without payment of Integrated Tax'
            . ($lutNumber ? ' (LUT ARN: ' . $lutNumber . ')' : '') . '.';
    }

    protected static function placeOfSupply(?string $code, ?string $clientGstin): ?string
    {
        $code = $code ?: ($clientGstin ? substr($clientGstin, 0, 2) : null);

        if (! $code) {
            return null;
        }

        $state = config('gst-states.states.' . $code);

        return $state ? $code . ' - ' . $state : $code;
    }

    protected static function customFieldRows(?array $customFields): array
    {
        return collect($customFields ?? [])
            ->filter(fn ($field) => is_array($field) && filled($field['key'] ?? null) && filled($field['value'] ?? null))
            ->map(fn ($field) => ['label' => $field['key'], 'value' => $field['value']])
            ->values()
            ->all();
    }
}
