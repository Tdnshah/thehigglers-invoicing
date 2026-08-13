<?php

/**
 * Registry of the templates an invoice or quotation can be printed with.
 *
 * Adding a template is a two step job:
 *   1. copy resources/views/documents/templates/classic to a new folder and edit it,
 *   2. add an entry here.
 *
 * Every template view is handed a single `$document` (App\Support\Documents\DocumentData)
 * plus `$options` and `$mode` ('screen' or 'pdf'), so it never touches Eloquent models.
 */
return [

    /*
     * Template used when a company has not picked one.
     */
    'default' => env('DOCUMENT_TEMPLATE', 'classic'),

    /*
     * Paper defaults handed to Dompdf.
     */
    'paper' => [
        'size' => 'a4',
        'orientation' => 'portrait',
    ],

    /*
     * Presentation options a template may read. These are the defaults; a company
     * level override can be merged over them once template settings are stored.
     */
    'options' => [
        'accent' => '#1f2937',
        // Rate-wise CGST/SGST/IGST breakup. The per line GST column and the
        // totals block already carry the tax, so this is off by default.
        'show_tax_summary' => false,
        'show_bank_details' => true,
        // Documents go out unsigned, so the signatory block is replaced by a
        // footer note that says as much.
        'show_signature' => false,
        'footer_note' => 'This is a computer generated document and does not require a signature.',
    ],

    'templates' => [

        'classic' => [
            'name' => 'Classic',
            'description' => 'Neutral, dense layout. Safe default for statutory invoices.',
            'view' => 'documents.templates.classic.document',
        ],

    ],

];
