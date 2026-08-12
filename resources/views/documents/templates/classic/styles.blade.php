@php
    $accent = $options['accent'] ?? '#1f2937';
@endphp
<style>
    /*
     * Dompdf renders with the "screen" media type and supports neither flexbox
     * nor grid, so this stylesheet is deliberately table + block only, and the
     * screen-only rules are gated on $mode instead of a @media print block.
     */
    @page {
        margin: 14mm 12mm 22mm 12mm;
    }

    * { box-sizing: border-box; }

    body {
        font-family: 'DejaVu Sans', sans-serif;
        font-size: 9.5pt;
        line-height: 1.45;
        color: #1f2937;
        margin: 0;
        padding: 0;
        background: #fff;
    }

    table { width: 100%; border-collapse: collapse; }
    td, th { vertical-align: top; }
    p { margin: 0 0 4px; }
    p:last-child { margin-bottom: 0; }

    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .muted { color: #6b7280; }
    .strong { font-weight: bold; }
    .nowrap { white-space: nowrap; }
    .avoid-break { page-break-inside: avoid; }

    .label {
        font-size: 7.5pt;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #6b7280;
        font-weight: bold;
    }

    /* Header ------------------------------------------------------------- */
    .header td { vertical-align: top; padding: 0; }
    .company-logo { max-height: 62px; max-width: 190px; margin-bottom: 6px; }
    .company-name {
        font-size: 15pt;
        font-weight: bold;
        color: {{ $accent }};
        margin: 0 0 3px;
        line-height: 1.2;
    }
    .company-meta { font-size: 8.5pt; color: #4b5563; }

    .doc-title {
        font-size: 17pt;
        font-weight: bold;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: {{ $accent }};
        margin: 0 0 6px;
        line-height: 1.1;
    }
    .badge {
        display: inline-block;
        padding: 2px 7px;
        border: 1px solid {{ $accent }};
        color: {{ $accent }};
        font-size: 7.5pt;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .badge-status { border-color: #9ca3af; color: #4b5563; margin-left: 3px; }

    .rule { border-bottom: 2px solid {{ $accent }}; margin: 10px 0 12px; }

    /* Parties + meta ------------------------------------------------------ */
    .parties { margin-bottom: 12px; }
    .parties > tbody > tr > td { padding: 0; }
    .parties .gap { width: 16px; }

    .panel {
        border: 1px solid #e5e7eb;
        padding: 8px 10px;
    }
    .panel-title {
        font-size: 7.5pt;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #6b7280;
        font-weight: bold;
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 4px;
        margin-bottom: 6px;
    }
    .party-name { font-size: 10.5pt; font-weight: bold; margin-bottom: 2px; }

    .meta-table td { padding: 1.5px 0; font-size: 8.5pt; }
    .meta-table td.meta-label { color: #6b7280; width: 42%; padding-right: 8px; }
    .meta-table td.meta-value { text-align: right; }

    /* Items --------------------------------------------------------------- */
    .items { margin-bottom: 10px; }
    .items thead th {
        background: #f3f4f6;
        border-top: 1px solid #d1d5db;
        border-bottom: 1px solid #d1d5db;
        padding: 6px 6px;
        font-size: 7.5pt;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #374151;
        text-align: left;
        font-weight: bold;
    }
    .items tbody td {
        padding: 6px;
        border-bottom: 1px solid #eceff3;
        font-size: 9pt;
    }
    .items tbody tr { page-break-inside: avoid; }
    .items tfoot td {
        padding: 7px 6px;
        border-top: 1px solid #d1d5db;
        font-weight: bold;
        background: #fafafa;
    }
    .item-desc ul, .item-desc ol { margin: 3px 0 0; padding-left: 14px; }
    .item-desc li { margin-bottom: 1px; }
    .item-desc p { margin: 0 0 2px; }

    /* Summary ------------------------------------------------------------- */
    .summary { margin-top: 4px; }
    .summary > tbody > tr > td { padding: 0; }
    .summary .gap { width: 16px; }

    .tax-table th, .tax-table td {
        border: 1px solid #e5e7eb;
        padding: 4px 6px;
        font-size: 8pt;
    }
    .tax-table th {
        background: #f9fafb;
        text-transform: uppercase;
        font-size: 7pt;
        letter-spacing: 0.04em;
        color: #4b5563;
        text-align: right;
        font-weight: bold;
    }
    .tax-table th:first-child, .tax-table td:first-child { text-align: left; }
    .tax-table td { text-align: right; }

    .totals-table td { padding: 4px 8px; font-size: 9pt; }
    .totals-table td.t-label { color: #4b5563; }
    .totals-table td.t-value { text-align: right; }
    .totals-table tr.divider td { border-top: 1px solid #e5e7eb; }
    .totals-table tr.grand td {
        border-top: 1.5px solid {{ $accent }};
        border-bottom: 1.5px solid {{ $accent }};
        font-size: 11pt;
        font-weight: bold;
        padding: 7px 8px;
        color: {{ $accent }};
    }

    .words {
        border: 1px solid #e5e7eb;
        background: #f9fafb;
        padding: 6px 10px;
        margin-top: 10px;
        font-size: 8.5pt;
    }

    .note-block { margin-top: 10px; font-size: 8.5pt; }
    .note-block .panel-title { margin-bottom: 4px; }

    .declaration {
        margin-top: 10px;
        padding: 6px 10px;
        border-left: 3px solid {{ $accent }};
        background: #f9fafb;
        font-size: 8.5pt;
    }

    .signature { margin-top: 18px; }
    .signature .sign-box {
        width: 210px;
        float: right;
        text-align: center;
        font-size: 8.5pt;
    }
    .signature .sign-space { height: 46px; }
    .signature .sign-line { border-top: 1px solid #9ca3af; padding-top: 4px; }

    /* Page furniture ------------------------------------------------------ */
    .page-footer {
        margin-top: 24px;
        border-top: 1px solid #e5e7eb;
        padding-top: 5px;
        font-size: 7.5pt;
        color: #9ca3af;
    }
    .page-footer td { padding: 0; }

    @if($mode === 'screen')
    body {
        background: #f3f4f6;
        padding: 0 0 40px;
    }
    .sheet {
        width: 210mm;
        min-height: 297mm;
        margin: 20px auto;
        padding: 14mm 12mm;
        background: #fff;
        border: 1px solid #d1d5db;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    }
    .toolbar {
        background: #ffffff;
        border-bottom: 1px solid #d1d5db;
        padding: 10px 16px;
        text-align: center;
    }
    .toolbar a, .toolbar button {
        display: inline-block;
        font-family: inherit;
        font-size: 9.5pt;
        padding: 8px 16px;
        margin: 0 3px;
        border: 1px solid #d1d5db;
        background: #fff;
        color: #374151;
        text-decoration: none;
        border-radius: 4px;
        cursor: pointer;
    }
    .toolbar .primary { background: {{ $accent }}; border-color: {{ $accent }}; color: #fff; }
    @media print {
        body { background: #fff; padding: 0; }
        .toolbar { display: none; }
        .sheet { width: auto; margin: 0; padding: 0; border: none; box-shadow: none; min-height: 0; }
    }
    @endif
</style>
