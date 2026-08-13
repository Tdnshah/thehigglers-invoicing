{{-- On-screen actions for the print preview. Never rendered into a PDF. --}}
<div class="toolbar">
    <button type="button" class="primary" onclick="window.print()">Print</button>
    @if($document->downloadUrl)
        <a href="{{ $document->downloadUrl }}">Download PDF</a>
    @endif
    <a href="{{ url()->previous() }}">Back</a>
</div>
