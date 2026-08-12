<div class="rounded-xl border border-border bg-card p-6 shadow-panel no-print">
    @include('documents.partials.notes-thread', [
        'notes' => $quotation->notes->sortByDesc('created_at'),
        'storeUrl' => route('quotations.notes.store', $quotation),
        'destroyRoute' => 'quotations.notes.destroy',
        'kind' => 'quotation',
        'canWrite' => Auth::user()->isCompanyAdmin(),
    ])
</div>
