<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Quotation;
use App\Support\Documents\DocumentData;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PdfWrapper;
use Illuminate\Contracts\View\View;
use InvalidArgumentException;

/**
 * Renders an invoice or quotation through the configured document template,
 * either to a browser view (print preview) or to a PDF.
 */
class DocumentRenderer
{
    public function forInvoice(Invoice $invoice): DocumentData
    {
        $document = DocumentData::fromInvoice($invoice);
        $document->downloadUrl = route('invoices.download', $invoice);

        return $document;
    }

    public function forQuotation(Quotation $quotation): DocumentData
    {
        $document = DocumentData::fromQuotation($quotation);
        $document->downloadUrl = route('quotations.download', $quotation);

        return $document;
    }

    /**
     * Browser view: same template, plus the on-screen action bar.
     */
    public function view(DocumentData $document, ?string $template = null): View
    {
        return view($this->templateView($template), [
            'document' => $document,
            'options' => $this->options(),
            'mode' => 'screen',
            'template' => $this->templateKey($template),
        ]);
    }

    public function pdf(DocumentData $document, ?string $template = null): PdfWrapper
    {
        $options = $this->options();

        $pdf = Pdf::loadView($this->templateView($template), [
            'document' => $document,
            'options' => $options,
            'mode' => 'pdf',
            'template' => $this->templateKey($template),
        ])->setPaper(
            config('document-templates.paper.size', 'a4'),
            config('document-templates.paper.orientation', 'portrait')
        );

        $this->stampFooter($pdf, $document, $options);

        return $pdf;
    }

    /**
     * Draw the running footer straight onto the canvas.
     *
     * Dompdf only reflows a position:fixed block once, so an HTML footer neither
     * repeats reliably nor knows the total page count. Canvas text does both.
     */
    protected function stampFooter(PdfWrapper $pdf, DocumentData $document, array $options): void
    {
        $pdf->render();

        $dompdf = $pdf->getDomPDF();
        $canvas = $dompdf->getCanvas();
        $metrics = $dompdf->getFontMetrics();

        $font = $metrics->getFont('DejaVu Sans', 'normal');
        $size = 7.0;
        $grey = [0.6, 0.6, 0.6];
        $margin = 34.0; // 12mm, matching the @page side margins
        $width = $canvas->get_width();
        $baseline = $canvas->get_height() - 34.0;

        $canvas->line($margin, $baseline - 9, $width - $margin, $baseline - 9, [0.88, 0.88, 0.88], 0.5);

        $left = $document->title . ' ' . $document->number;
        $centre = $options['footer_note'] ?? 'This is a computer generated document.';
        $right = 'Page {PAGE_NUM} of {PAGE_COUNT}';

        $canvas->page_text($margin, $baseline, $left, $font, $size, $grey);
        $canvas->page_text(
            ($width - $metrics->getTextWidth($centre, $font, $size)) / 2,
            $baseline,
            $centre,
            $font,
            $size,
            $grey
        );
        $canvas->page_text(
            $width - $margin - $metrics->getTextWidth('Page 00 of 00', $font, $size),
            $baseline,
            $right,
            $font,
            $size,
            $grey
        );
    }

    /**
     * A filesystem safe download name, e.g. "Invoice-INV-2026-0042.pdf".
     */
    public function filename(DocumentData $document): string
    {
        $prefix = $document->isInvoice() ? 'Invoice' : 'Quotation';
        $number = preg_replace('/[^A-Za-z0-9._-]+/', '-', $document->number);

        return $prefix . '-' . trim($number, '-') . '.pdf';
    }

    /**
     * Presentation options for the active template.
     */
    public function options(array $overrides = []): array
    {
        return array_merge(config('document-templates.options', []), array_filter($overrides, fn ($v) => $v !== null));
    }

    public function templateKey(?string $template = null): string
    {
        $key = $template ?: config('document-templates.default', 'classic');

        if (! config()->has('document-templates.templates.' . $key)) {
            $key = 'classic';
        }

        return $key;
    }

    protected function templateView(?string $template = null): string
    {
        $key = $this->templateKey($template);
        $view = config('document-templates.templates.' . $key . '.view');

        if (! $view) {
            throw new InvalidArgumentException("Document template [{$key}] has no view configured.");
        }

        return $view;
    }
}
