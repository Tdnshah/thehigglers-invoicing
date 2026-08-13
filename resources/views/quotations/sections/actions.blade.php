@php
    use App\Support\Money;

    $isLocked = $revisions->contains(fn ($r) => $r->status === 'approved');
    $statusStyles = [
        'draft' => 'bg-muted text-foreground',
        'sent' => 'bg-info-muted text-info-muted-foreground',
        'approved' => 'bg-success-muted text-success-muted-foreground',
        'rejected' => 'bg-destructive-muted text-destructive-muted-foreground',
    ];
@endphp

<div class="space-y-6 no-print">

    <!-- Actions -->
    <div class="overflow-hidden rounded-xl border border-border bg-card shadow-panel">
        <div class="border-b border-border px-6 py-5">
            <h2 class="text-base font-semibold text-foreground">Actions</h2>
            <p class="mt-1 text-sm text-muted-foreground">What can be done with this quotation at its current state.</p>
        </div>
        <div class="grid grid-cols-1 gap-4 p-6 sm:grid-cols-2">
            @if($quotation->isApproved() && ! $quotation->isConverted())
                <form action="{{ route('quotations.convert', $quotation) }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-md bg-success px-4 text-sm font-semibold text-success-foreground shadow-xs transition-colors hover:bg-success/90 focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                        Clone to invoice
                    </button>
                </form>
            @endif

            @if($quotation->isConverted())
                <a href="{{ route('invoices.show', $quotation->invoice_id) }}"
                   class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-md border border-primary/30 bg-primary/5 px-4 text-sm font-semibold text-primary transition-colors hover:bg-primary/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    {{ $quotation->invoice->invoice_number ?? 'Related invoice' }}
                </a>
            @endif

            @if(! $isLocked && ! $quotation->isConverted())
                <a href="{{ route('quotations.create', ['source_id' => $quotation->id]) }}"
                   class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-md border border-input bg-background px-4 text-sm font-medium text-foreground shadow-xs transition-colors hover:bg-accent focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path></svg>
                    Create sub-revision
                </a>
            @elseif($isLocked)
                <div class="flex h-10 w-full items-center justify-center rounded-md border border-dashed border-border bg-muted/40 px-4">
                    <span class="text-xs font-medium text-muted-foreground">History locked, a version is approved</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Version timeline -->
    @if($revisions->count() > 1)
        <div class="overflow-hidden rounded-xl border border-border bg-card shadow-panel">
            <div class="border-b border-border px-6 py-5">
                <h2 class="text-base font-semibold text-foreground">Versions</h2>
                <p class="mt-1 text-sm text-muted-foreground">Every revision in this quotation series.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-muted/40">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">Version</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">Date</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">Total</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-muted-foreground">Status</th>
                            <th scope="col" class="px-6 py-3 text-right"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach($revisions as $rev)
                            <tr class="{{ $rev->id === $quotation->id ? 'bg-primary/5' : 'transition-colors hover:bg-muted/40' }}">
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-sm font-bold {{ $rev->id === $quotation->id ? 'text-primary' : 'text-foreground' }}">V{{ $rev->revision_number }}</span>
                                        @if($rev->id === $quotation->id)
                                            <span class="inline-flex items-center rounded-full bg-info-muted px-2 py-0.5 text-xs font-semibold uppercase tracking-wider text-info-muted-foreground">Viewing</span>
                                        @endif
                                        @if($rev->is_active)
                                            <span class="inline-flex items-center rounded-full bg-success-muted px-2 py-0.5 text-xs font-semibold uppercase tracking-wider text-success-muted-foreground">Active</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-muted-foreground">{{ $rev->quotation_date->format('d M Y') }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium tabular-nums text-foreground">{{ $rev->currency }} {{ Money::format($rev->total, $rev->currency) }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-center">
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold uppercase tracking-wider {{ $statusStyles[$rev->status] ?? 'bg-muted text-foreground' }}">{{ $rev->status }}</span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        @if(! $isLocked && ! $rev->is_active)
                                            <form action="{{ route('quotations.mark-as-active', $rev) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-xs font-medium text-muted-foreground transition-colors hover:text-success-muted-foreground focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">Set active</button>
                                            </form>
                                        @endif
                                        @if($rev->id !== $quotation->id)
                                            <a href="{{ route('quotations.show', $rev) }}" class="text-xs font-semibold text-primary transition-colors hover:text-primary/80">View</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Danger zone -->
    @php($treeConverted = $quotation->isConverted() || $revisions->contains(fn ($r) => $r->isConverted()))
    <div class="overflow-hidden rounded-xl border border-destructive-muted bg-card shadow-panel">
        <div class="border-b border-destructive-muted px-6 py-5">
            <h2 class="text-base font-semibold text-foreground">Delete quotation</h2>
            <p class="mt-1 text-sm text-muted-foreground">
                @if($treeConverted)
                    An invoice was cloned from this series, so it has to stay on record.
                @elseif($quotation->parent_id === null && $revisions->count() > 1)
                    Deletes this quotation and all {{ $revisions->count() - 1 }} of its revisions. This cannot be undone.
                @else
                    This cannot be undone.
                @endif
            </p>
        </div>
        <div class="flex justify-end px-6 py-4">
            @if($treeConverted)
                <span class="inline-flex h-9 items-center rounded-md border border-dashed border-border px-4 text-sm text-muted-foreground">Delete unavailable</span>
            @else
                <form action="{{ route('quotations.destroy', $quotation) }}" method="POST"
                      onsubmit="return confirm('Delete {{ $quotation->quotation_number }}{{ $quotation->parent_id === null && $revisions->count() > 1 ? ' and its revisions' : '' }}? This cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="inline-flex h-9 items-center gap-1.5 rounded-md bg-destructive px-4 text-sm font-semibold text-destructive-foreground shadow-xs transition-colors hover:bg-destructive/90 focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        Delete quotation
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
