@php
    use App\Support\Money;

    $isAdmin = Auth::user()->isCompanyAdmin();
    $isLocked = $revisions->contains(fn ($r) => $r->status === 'approved');

    $statusStyles = [
        'draft' => 'bg-muted text-foreground',
        'sent' => 'bg-info-muted text-info-muted-foreground',
        'approved' => 'bg-success-muted text-success-muted-foreground',
        'rejected' => 'bg-destructive-muted text-destructive-muted-foreground',
    ];

    $tabs = array_filter([
        'document' => ['label' => 'Quotation'],
        'actions' => $isAdmin ? ['label' => 'Versions & Actions', 'badge' => $revisions->count() > 1 ? $revisions->count() : null] : null,
        'notes' => $isAdmin ? ['label' => 'Private Notes', 'badge' => $quotation->notes->count() ?: null] : null,
        'bank' => ['label' => 'Bank Details'],
    ]);
@endphp

<x-app-layout>
    <x-slot name="title">{{ $quotation->quotation_number }}</x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

        <!-- Record header -->
        <div class="overflow-hidden rounded-xl border border-border bg-card shadow-panel no-print">
            <div class="flex flex-wrap items-start justify-between gap-x-6 gap-y-4 px-6 py-5">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-3">
                        <h1 class="text-xl font-bold tracking-tight text-foreground">{{ $quotation->quotation_number }}</h1>
                        @include('documents.partials.status-menu', [
                            'current' => $quotation->status,
                            'options' => $isAdmin ? \App\Http\Controllers\QuotationController::selectableStatuses($quotation) : [],
                            'action' => route('quotations.status', $quotation),
                            'styles' => $statusStyles,
                            'lockNote' => $quotation->isConverted() ? 'Cloned to an invoice.' : null,
                        ])
                        @if($quotation->revision_number)
                            <span class="inline-flex items-center rounded-full bg-muted px-2 py-0.5 font-mono text-xs font-semibold text-muted-foreground">V{{ $quotation->revision_number }}</span>
                        @endif
                        @if($isLocked)
                            <span class="inline-flex items-center gap-1 rounded-full bg-success-muted px-2 py-0.5 text-xs font-semibold uppercase tracking-wider text-success-muted-foreground">
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                                Finalized
                            </span>
                        @endif
                    </div>
                    <p class="mt-1.5 text-sm text-muted-foreground">
                        {{ $quotation->client->name }}
                        <span aria-hidden="true" class="px-1.5 text-muted-foreground/50">&middot;</span>
                        Dated {{ $quotation->quotation_date->format('d M Y') }}
                        @if($quotation->valid_until)
                            <span aria-hidden="true" class="px-1.5 text-muted-foreground/50">&middot;</span>
                            Valid until {{ $quotation->valid_until->format('d M Y') }}
                        @endif
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    @if($isAdmin && $quotation->isApproved() && ! $quotation->isConverted())
                        <form action="{{ route('quotations.convert', $quotation) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit"
                                    class="inline-flex h-9 items-center gap-1.5 rounded-md bg-success px-3 text-sm font-semibold text-success-foreground shadow-xs transition-colors hover:bg-success/90 focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                Clone to invoice
                            </button>
                        </form>
                    @elseif($quotation->isConverted())
                        <a href="{{ route('invoices.show', $quotation->invoice_id) }}"
                           class="inline-flex h-9 items-center gap-1.5 rounded-md border border-primary/30 bg-primary/5 px-3 text-sm font-semibold text-primary transition-colors hover:bg-primary/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                            {{ $quotation->invoice->invoice_number ?? 'Related invoice' }}
                        </a>
                    @endif
                    @if($isAdmin && ! $isLocked)
                        <a href="{{ route('quotations.edit', $quotation) }}"
                           class="inline-flex h-9 items-center rounded-md border border-input bg-background px-3 text-sm font-medium text-foreground shadow-xs transition-colors hover:bg-accent focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">Edit</a>
                    @endif
                    <a href="{{ route('quotations.print', $quotation) }}" target="_blank"
                       class="inline-flex h-9 items-center gap-1.5 rounded-md border border-input bg-background px-3 text-sm font-medium text-foreground shadow-xs transition-colors hover:bg-accent focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        Print
                    </a>
                    <a href="{{ route('quotations.download', $quotation) }}"
                       class="inline-flex h-9 items-center gap-1.5 rounded-md bg-foreground px-3 text-sm font-medium text-background shadow-xs transition-colors hover:bg-foreground/90 focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        PDF
                    </a>
                </div>
            </div>

            <!-- Figures -->
            <dl class="grid grid-cols-2 border-t border-border sm:grid-cols-4">
                <div class="border-r border-border px-6 py-4">
                    <dt class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Total</dt>
                    <dd class="mt-1 text-lg font-bold tabular-nums text-foreground">{{ $quotation->currency }} {{ Money::format($quotation->total, $quotation->currency) }}</dd>
                </div>
                <div class="border-border px-6 py-4 sm:border-r">
                    <dt class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Taxable value</dt>
                    <dd class="mt-1 text-lg font-bold tabular-nums text-muted-foreground">{{ $quotation->currency }} {{ Money::format($quotation->subtotal, $quotation->currency) }}</dd>
                </div>
                <div class="border-r border-t border-border px-6 py-4 sm:border-t-0">
                    <dt class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Versions</dt>
                    <dd class="mt-1 text-lg font-bold tabular-nums text-foreground">{{ $revisions->count() }}</dd>
                </div>
                <div class="border-t border-border px-6 py-4 sm:border-t-0">
                    <dt class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Supply</dt>
                    <dd class="mt-1 text-sm font-medium text-foreground">
                        {{ ucfirst($quotation->quotation_type) }}
                        @if($quotation->place_of_supply)
                            <span class="text-muted-foreground">&middot; {{ config('gst-states.states.' . $quotation->place_of_supply) ?? $quotation->place_of_supply }}</span>
                        @endif
                    </dd>
                </div>
            </dl>

            @include('documents.partials.record-tabs', [
                'tabs' => $tabs,
                'active' => $tab,
                'url' => fn ($key) => route('quotations.show', ['quotation' => $quotation, 'tab' => $key]),
            ])
        </div>

        <!-- Active section -->
        <div class="mt-6">
            @include('quotations.sections.' . $tab)
        </div>
    </div>

    <style>
        @media print {
            body * { visibility: hidden; }
            #quotation-print-area, #quotation-print-area * { visibility: visible; }
            #quotation-print-area { position: absolute; left: 0; top: 0; width: 100%; box-shadow: none; border: 0; margin: 0; padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</x-app-layout>
