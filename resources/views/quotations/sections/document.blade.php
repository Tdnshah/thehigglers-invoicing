@include('documents.partials.document-preview', [
    'doc' => $quotation,
    'kindLabel' => 'Quotation',
    'number' => $quotation->quotation_number,
    'recipientLabel' => 'Prepared for',
    'printId' => 'quotation-print-area',
    'dates' => [
        'Quotation date' => $quotation->quotation_date->format('d M Y'),
        'Valid until' => $quotation->valid_until?->format('d M Y'),
    ],
    'extra' => [
        'LUT' => $quotation->quotation_type === 'export' ? $quotation->lut_number : null,
        'Revision' => $quotation->revision_number ? 'V' . $quotation->revision_number : null,
    ],
])

@if($quotation->client_notes)
    <div class="mt-6 rounded-xl border border-border bg-card p-6 shadow-panel">
        <h2 class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Notes for the client</h2>
        <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-foreground/90">{{ $quotation->client_notes }}</p>
    </div>
@endif
