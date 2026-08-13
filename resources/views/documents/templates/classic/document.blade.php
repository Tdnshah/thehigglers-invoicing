@php
    /**
     * Classic document template.
     *
     * Receives:
     *   $document (App\Support\Documents\DocumentData) - the only data source
     *   $options  (array)  - presentation options from config/document-templates.php
     *   $mode     (string) - 'screen' for the print preview, 'pdf' for Dompdf
     */
    $issuer = $document->issuer;
    $recipient = $document->recipient;
    $totals = $document->totals;
    $hasTaxSummary = ($options['show_tax_summary'] ?? true)
        && collect($document->taxSummary)->sum(fn ($row) => $row['cgst'] + $row['sgst'] + $row['igst']) > 0;
    $hasBank = ($options['show_bank_details'] ?? true) && ! empty($document->bank);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ $document->title }} {{ $document->number }}</title>
    @include('documents.templates.classic.styles')
</head>
<body>

@if($mode === 'screen')
    @include('documents.partials.toolbar')
@endif

<div class="{{ $mode === 'screen' ? 'sheet' : '' }}">

    {{-- Header ---------------------------------------------------------- --}}
    <table class="header">
        <tr>
            <td style="width: 56%;">
                {{-- The logo carries the company name, so the text name is only a fallback. --}}
                @if($issuer['logo'])
                    <img src="{{ $issuer['logo'] }}" alt="{{ $issuer['name'] }}" class="company-logo">
                @else
                    <div class="company-name">{{ $issuer['name'] }}</div>
                @endif
                <div class="company-meta">
                    @if($issuer['address'])
                        {!! nl2br(e($issuer['address'])) !!}<br>
                    @endif
                    @if($issuer['phone'])<span class="nowrap">T {{ $issuer['phone'] }}</span> @endif
                    @if($issuer['email'])<span class="nowrap">{{ $issuer['email'] }}</span>@endif
                    @if($issuer['website'])<br>{{ $issuer['website'] }}@endif
                </div>
                @if($issuer['gstin'])
                    <div class="company-meta" style="margin-top: 4px;">
                        <span class="label">GSTIN</span> <span class="strong">{{ $issuer['gstin'] }}</span>
                    </div>
                @endif
            </td>
            <td style="width: 44%; text-align: right;">
                <div class="doc-title">{{ $document->title }}</div>
                @if($document->supplyLabel)
                    <span class="badge">{{ $document->supplyLabel }}</span>
                @endif
                @if($document->status)
                    <span class="badge badge-status">{{ ucfirst($document->status) }}</span>
                @endif
            </td>
        </tr>
    </table>

    <div class="rule"></div>

    {{-- Parties and document meta --------------------------------------- --}}
    <table class="parties">
        <tr>
            <td style="width: 52%;">
                <div class="panel" style="min-height: 92px;">
                    <div class="panel-title">{{ $document->isInvoice() ? 'Billed To' : 'Prepared For' }}</div>
                    <div class="party-name">{{ $recipient['name'] }}</div>
                    @if($recipient['address'])
                        <div>{!! nl2br(e($recipient['address'])) !!}</div>
                    @endif
                    <div style="margin-top: 4px; font-size: 8.5pt;">
                        @if($recipient['gstin'])
                            <span class="label">GSTIN</span> <span class="strong">{{ $recipient['gstin'] }}</span><br>
                        @endif
                        @if($recipient['email'])<span class="muted">{{ $recipient['email'] }}</span>@endif
                        @if($recipient['phone'])<span class="muted"> &middot; {{ $recipient['phone'] }}</span>@endif
                    </div>
                </div>
            </td>
            <td class="gap"></td>
            <td style="width: 44%;">
                <div class="panel" style="min-height: 92px;">
                    <div class="panel-title">{{ $document->isInvoice() ? 'Invoice Details' : 'Quotation Details' }}</div>
                    <table class="meta-table">
                        @foreach($document->metaRows() as $row)
                            <tr>
                                <td class="meta-label">{{ $row['label'] }}</td>
                                <td class="meta-value {{ ($row['strong'] ?? false) ? 'strong' : '' }}">{{ $row['value'] }}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </td>
        </tr>
    </table>

    {{-- Line items ------------------------------------------------------- --}}
    <table class="items">
        <thead>
            <tr>
                <th style="width: 4%;">#</th>
                <th style="width: 42%;">Description</th>
                <th style="width: 11%;" class="text-right">HSN/SAC</th>
                <th style="width: 8%;" class="text-right">Qty</th>
                <th style="width: 14%;" class="text-right">Rate</th>
                <th style="width: 7%;" class="text-right">GST</th>
                <th style="width: 14%;" class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($document->items as $item)
                <tr>
                    <td>{{ $item['index'] }}</td>
                    <td class="item-desc">{!! $item['description'] !!}</td>
                    <td class="text-right">{{ $item['hsn_code'] ?: '-' }}</td>
                    <td class="text-right">{{ $item['quantity'] }}</td>
                    <td class="text-right">{{ $document->money($item['unit_price']) }}</td>
                    <td class="text-right">{{ rtrim(rtrim(number_format($item['tax_rate'], 2), '0'), '.') }}%</td>
                    <td class="text-right">{{ $document->money($item['taxable']) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" class="text-right">Taxable Value</td>
                <td class="text-right">{{ $document->money($totals['subtotal']) }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- Tax summary and totals ------------------------------------------- --}}
    <table class="summary avoid-break">
        <tr>
            <td style="width: 52%;">
                @if($hasTaxSummary)
                    <table class="tax-table">
                        <thead>
                            <tr>
                                <th>Tax Rate</th>
                                <th>Taxable Value</th>
                                @if($document->taxMode === 'igst')
                                    <th>IGST</th>
                                @else
                                    <th>CGST</th>
                                    <th>SGST</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($document->taxSummary as $row)
                                <tr>
                                    <td>{{ rtrim(rtrim(number_format($row['rate'], 2), '0'), '.') }}%</td>
                                    <td>{{ $document->money($row['taxable']) }}</td>
                                    @if($document->taxMode === 'igst')
                                        <td>{{ $document->money($row['igst']) }}</td>
                                    @else
                                        <td>{{ $document->money($row['cgst']) }}</td>
                                        <td>{{ $document->money($row['sgst']) }}</td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

                @if($hasBank)
                    <div class="panel" style="margin-top: {{ $hasTaxSummary ? '10px' : '0' }};">
                        <div class="panel-title">Bank Details</div>
                        <table class="meta-table">
                            @foreach($document->bank as $label => $value)
                                <tr>
                                    <td class="meta-label">{{ $label }}</td>
                                    <td class="meta-value strong">{{ $value }}</td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                @endif
            </td>
            <td class="gap"></td>
            <td style="width: 44%;">
                <table class="totals-table">
                    <tr>
                        <td class="t-label">Taxable Value</td>
                        <td class="t-value">{{ $document->money($totals['subtotal']) }}</td>
                    </tr>
                    @if($document->taxMode !== 'igst' && ($totals['cgst'] > 0 || $totals['sgst'] > 0))
                        <tr>
                            <td class="t-label">CGST</td>
                            <td class="t-value">{{ $document->money($totals['cgst']) }}</td>
                        </tr>
                        <tr>
                            <td class="t-label">SGST</td>
                            <td class="t-value">{{ $document->money($totals['sgst']) }}</td>
                        </tr>
                    @endif
                    @if($document->taxMode === 'igst' && $totals['igst'] > 0)
                        <tr>
                            <td class="t-label">IGST</td>
                            <td class="t-value">{{ $document->money($totals['igst']) }}</td>
                        </tr>
                    @endif
                    <tr class="grand">
                        <td>Total ({{ $document->currency }})</td>
                        <td class="t-value">{{ $document->money($totals['total'], true) }}</td>
                    </tr>
                    @if($totals['paid'] !== null)
                        <tr>
                            <td class="t-label">Amount Received</td>
                            <td class="t-value">{{ $document->money($totals['paid']) }}</td>
                        </tr>
                        <tr class="divider">
                            <td class="t-label strong">Balance Due</td>
                            <td class="t-value strong">{{ $document->money($totals['balance'], true) }}</td>
                        </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <div class="words avoid-break">
        <span class="label">Amount in words</span><br>
        <span class="strong">{{ $totals['in_words'] }}</span>
    </div>

    @if($document->declaration)
        <div class="declaration avoid-break">{{ $document->declaration }}</div>
    @endif

    @if($document->notes)
        <div class="note-block avoid-break">
            <div class="panel-title">Notes</div>
            <div>{!! nl2br(e($document->notes)) !!}</div>
        </div>
    @endif

    @if($document->terms)
        <div class="note-block avoid-break">
            <div class="panel-title">Terms &amp; Conditions</div>
            <div>{!! nl2br(e($document->terms)) !!}</div>
        </div>
    @endif

    @if($options['show_signature'] ?? true)
        <table class="signature avoid-break">
            <tr>
                <td></td>
                <td style="width: 250px; text-align: center; font-size: 8.5pt;">
                    <div class="muted">For</div>
                    <div class="strong">{{ $issuer['name'] }}</div>
                    <div class="sign-space"></div>
                    <div class="sign-line">Authorised Signatory</div>
                </td>
            </tr>
        </table>
    @endif

    {{-- In PDF mode the running footer is stamped onto every page by
         App\Services\DocumentRenderer, which also knows the page count. --}}
    @if($mode === 'screen')
        <table class="page-footer">
            <tr>
                <td style="width: 50%;">{{ $document->title }} {{ $document->number }}</td>
                <td style="width: 50%; text-align: right;">
                    {{ $options['footer_note'] ?? 'This is a computer generated document.' }}
                </td>
            </tr>
        </table>
    @endif

</div>
</body>
</html>
