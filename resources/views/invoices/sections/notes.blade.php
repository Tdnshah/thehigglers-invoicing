<div class="rounded-xl border border-border bg-card p-6 shadow-panel no-print">
    @include('documents.partials.notes-thread', [
        'notes' => $invoice->privateNotes,
        'storeUrl' => route('invoices.notes.store', $invoice),
        'destroyRoute' => 'invoices.notes.destroy',
        'kind' => 'invoice',
        'canWrite' => Auth::user()->isCompanyAdmin(),
    ])
</div>
