@php
    use App\Support\Money;
@endphp

<div class="overflow-hidden rounded-xl border border-border bg-card shadow-panel no-print">
    <div class="border-b border-border px-6 py-5">
        <h2 class="text-base font-semibold text-foreground">Revisions</h2>
        <p class="mt-1 text-sm text-muted-foreground">
            A snapshot is written on approval and on every payment, each holding the total, the amount received to date, and the balance.
        </p>
    </div>

    <div class="p-6">
        <ol class="relative ml-2 border-l border-border">
            <li class="relative ml-6 pb-8">
                <span aria-hidden="true" class="absolute -left-[1.6875rem] top-1 h-3 w-3 rounded-full bg-border ring-4 ring-card"></span>
                <p class="text-sm font-semibold text-foreground">Invoice created</p>
                <p class="mt-0.5 text-xs text-muted-foreground">{{ $invoice->created_at->format('d M Y, H:i') }}</p>
            </li>

            @if($invoice->quotation)
                <li class="relative ml-6 pb-8">
                    <span aria-hidden="true" class="absolute -left-[1.6875rem] top-1 h-3 w-3 rounded-full bg-success ring-4 ring-card"></span>
                    <p class="text-sm font-semibold text-foreground">Cloned from an approved quotation</p>
                    <p class="mt-0.5 text-xs">
                        <a href="{{ route('quotations.show', $invoice->quotation) }}" class="font-medium text-primary hover:text-primary/80">
                            {{ $invoice->quotation->quotation_number }} &rarr;
                        </a>
                    </p>
                </li>
            @endif

            @foreach($invoice->revisions as $revision)
                <li class="relative ml-6 pb-8 last:pb-0">
                    <span aria-hidden="true" class="absolute -left-[1.6875rem] top-1 h-3 w-3 rounded-full ring-4 ring-card {{ $revision->isSettled() ? 'bg-success' : 'bg-primary' }}"></span>

                    <div class="flex flex-wrap items-center gap-2">
                        <p class="text-sm font-semibold text-foreground">R{{ $revision->revision_number }} &middot; {{ $revision->label() }}</p>
                        @if($revision->isSettled())
                            <span class="inline-flex items-center rounded-full bg-success-muted px-2 py-0.5 text-xs font-semibold uppercase tracking-wider text-success-muted-foreground">Settled</span>
                        @endif
                    </div>
                    <p class="mt-0.5 text-xs text-muted-foreground">
                        {{ $revision->created_at->format('d M Y, H:i') }}
                        @if($revision->user)<span aria-hidden="true" class="px-1">&middot;</span>{{ $revision->user->name }}@endif
                    </p>

                    <dl class="mt-3 max-w-xs space-y-1.5 rounded-lg border border-border bg-muted/40 px-4 py-3">
                        <div class="flex items-baseline justify-between gap-6">
                            <dt class="text-xs text-muted-foreground">Invoice total</dt>
                            <dd class="text-sm font-medium tabular-nums text-foreground">{{ $invoice->currency }} {{ Money::format($revision->total, $invoice->currency) }}</dd>
                        </div>
                        <div class="flex items-baseline justify-between gap-6">
                            <dt class="text-xs text-muted-foreground">Less received</dt>
                            <dd class="text-sm font-medium tabular-nums text-success-muted-foreground">&minus; {{ $invoice->currency }} {{ Money::format($revision->amount_received, $invoice->currency) }}</dd>
                        </div>
                        <div class="flex items-baseline justify-between gap-6 border-t border-border pt-1.5">
                            <dt class="text-xs font-semibold text-foreground">Balance</dt>
                            <dd class="text-sm font-bold tabular-nums {{ $revision->isSettled() ? 'text-success-muted-foreground' : 'text-foreground' }}">{{ $invoice->currency }} {{ Money::format($revision->balance, $invoice->currency) }}</dd>
                        </div>
                    </dl>

                    @if($revision->payment)
                        <p class="mt-2 text-xs text-muted-foreground">
                            This payment: {{ $invoice->currency }} {{ Money::format($revision->payment->amount, $invoice->currency) }}
                            @if($revision->payment->payment_method) via {{ $revision->payment->payment_method }} @endif
                            <span class="font-mono">{{ $revision->payment->transaction_reference }}</span>
                        </p>
                    @endif

                    @if($revision->note)
                        <p class="mt-2 whitespace-pre-line border-l-2 border-primary/30 pl-3 text-sm text-foreground/90">{{ $revision->note }}</p>
                    @endif
                </li>
            @endforeach

            @if($invoice->revisions->isEmpty())
                <li class="relative ml-6">
                    <span aria-hidden="true" class="absolute -left-[1.6875rem] top-1 h-3 w-3 rounded-full bg-border ring-4 ring-card"></span>
                    @if($invoice->isPaid())
                        <p class="text-sm font-semibold text-foreground">No revisions recorded</p>
                        <p class="mt-0.5 text-xs text-muted-foreground">This invoice was settled before revisions were tracked. Its payments are in the ledger.</p>
                    @else
                        <p class="text-sm font-semibold text-foreground">Awaiting approval</p>
                        <p class="mt-0.5 text-xs text-muted-foreground">The first revision is written when the invoice is approved.</p>
                    @endif
                </li>
            @endif
        </ol>

        <p class="mt-6 text-xs text-muted-foreground">Edits to line items are not versioned.</p>
    </div>
</div>
