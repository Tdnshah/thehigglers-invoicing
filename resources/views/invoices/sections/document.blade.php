@include('documents.partials.document-preview', [
    'doc' => $invoice,
    'kindLabel' => 'Tax Invoice',
    'number' => $invoice->invoice_number,
    'recipientLabel' => 'Billed to',
    'printId' => 'invoice-print-area',
    'dates' => [
        'Invoice date' => $invoice->invoice_date->format('d M Y'),
        'Due date' => $invoice->due_date?->format('d M Y'),
    ],
    'extra' => [
        'LUT' => $invoice->invoice_type === 'export' ? $invoice->lut_number : null,
    ],
])
