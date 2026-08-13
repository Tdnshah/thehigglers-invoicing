@php
    use App\Support\Money;

    $isAdmin = Auth::user()->isCompanyAdmin();
    $paid = $invoice->amountPaid();
    $balance = $invoice->balanceDue();

    $statusStyles = [
        'draft' => 'bg-muted text-foreground',
        'approved' => 'bg-success-muted text-success-muted-foreground',
        'sent' => 'bg-info-muted text-info-muted-foreground',
        'paid' => 'bg-success-muted text-success-muted-foreground',
        'overdue' => 'bg-destructive-muted text-destructive-muted-foreground',
    ];

    $tabs = array_filter([
        'document' => ['label' => 'Invoice'],
        'payments' => ($isAdmin || $invoice->payments->count()) ? ['label' => 'Payments', 'badge' => $invoice->payments->count() ?: null] : null,
        'notes' => $isAdmin ? ['label' => 'Private Notes', 'badge' => $invoice->privateNotes->count() ?: null] : null,
        'revisions' => $isAdmin ? ['label' => 'Revisions', 'badge' => $invoice->revisions->count() ?: null] : null,
        'bank' => ['label' => 'Bank Details'],
    ]);
@endphp

<x-app-layout>
    <x-slot name="title">{{ $invoice->invoice_number }}</x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

        <!-- Record header -->
        <div class="overflow-hidden rounded-xl border border-border bg-card shadow-panel no-print">
            <div class="flex flex-wrap items-start justify-between gap-x-6 gap-y-4 px-6 py-5">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-3">
                        <h1 class="text-xl font-bold tracking-tight text-foreground">{{ $invoice->invoice_number }}</h1>
                        @include('documents.partials.status-menu', [
                            'current' => $invoice->status,
                            'options' => $isAdmin ? \App\Http\Controllers\InvoiceController::selectableStatuses($invoice) : [],
                            'action' => route('invoices.status', $invoice),
                            'styles' => $statusStyles,
                            'lockNote' => $invoice->isPaid() ? 'Settled and closed.' : null,
                        ])
                        @if($invoice->quotation)
                            <a href="{{ route('quotations.show', $invoice->quotation) }}"
                               class="inline-flex items-center gap-1 rounded-full bg-muted px-2 py-0.5 text-xs font-medium text-muted-foreground transition-colors hover:text-foreground">
                                from {{ $invoice->quotation->quotation_number }}
                            </a>
                        @endif
                    </div>
                    <p class="mt-1.5 text-sm text-muted-foreground">
                        {{ $invoice->client->name }}
                        <span aria-hidden="true" class="px-1.5 text-muted-foreground/50">&middot;</span>
                        Issued {{ $invoice->invoice_date->format('d M Y') }}
                        @if($invoice->due_date)
                            <span aria-hidden="true" class="px-1.5 text-muted-foreground/50">&middot;</span>
                            Due {{ $invoice->due_date->format('d M Y') }}
                        @endif
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    @if($isAdmin && ! $invoice->isPaid())
                        <a href="{{ route('invoices.edit', $invoice) }}"
                           class="inline-flex h-9 items-center rounded-md border border-input bg-background px-3 text-sm font-medium text-foreground shadow-xs transition-colors hover:bg-accent focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">Edit</a>
                    @endif
                    <a href="{{ route('invoices.print', $invoice) }}" target="_blank"
                       class="inline-flex h-9 items-center gap-1.5 rounded-md border border-input bg-background px-3 text-sm font-medium text-foreground shadow-xs transition-colors hover:bg-accent focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        Print
                    </a>
                    <a href="{{ route('invoices.download', $invoice) }}"
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
                    <dd class="mt-1 text-lg font-bold tabular-nums text-foreground">{{ $invoice->currency }} {{ Money::format($invoice->total, $invoice->currency) }}</dd>
                </div>
                <div class="border-border px-6 py-4 sm:border-r">
                    <dt class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Received</dt>
                    <dd class="mt-1 text-lg font-bold tabular-nums {{ $paid > 0 ? 'text-success-muted-foreground' : 'text-muted-foreground' }}">{{ $invoice->currency }} {{ Money::format($paid, $invoice->currency) }}</dd>
                </div>
                <div class="border-r border-t border-border px-6 py-4 sm:border-t-0">
                    <dt class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Balance due</dt>
                    <dd class="mt-1 text-lg font-bold tabular-nums {{ $balance > 0 ? 'text-foreground' : 'text-success-muted-foreground' }}">{{ $invoice->currency }} {{ Money::format($balance, $invoice->currency) }}</dd>
                </div>
                <div class="border-t border-border px-6 py-4 sm:border-t-0">
                    <dt class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Supply</dt>
                    <dd class="mt-1 text-sm font-medium text-foreground">
                        {{ ucfirst($invoice->invoice_type) }}
                        @if($invoice->place_of_supply)
                            <span class="text-muted-foreground">&middot; {{ config('gst-states.states.' . $invoice->place_of_supply) ?? $invoice->place_of_supply }}</span>
                        @endif
                    </dd>
                </div>
            </dl>

            @include('documents.partials.record-tabs', [
                'tabs' => $tabs,
                'active' => $tab,
                'url' => fn ($key) => route('invoices.show', ['invoice' => $invoice, 'tab' => $key]),
            ])
        </div>

        <!-- Approval gate -->
        @if($isAdmin && ! $invoice->isApproved() && ! $invoice->isPaid())
            <div x-data="{ open: {{ $errors->has('note') ? 'true' : 'false' }} }"
                 class="mt-6 rounded-xl border border-warning-muted bg-warning-muted/60 p-6 no-print">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="max-w-2xl">
                        <h2 class="text-base font-semibold text-foreground">This invoice is not approved yet</h2>
                        <p class="mt-1 text-sm text-warning-muted-foreground">
                            No payment can be recorded against it until it is approved, and approving requires a note explaining the decision.
                        </p>
                    </div>
                    <button type="button" @click="open = true" x-show="!open"
                            class="inline-flex h-9 shrink-0 items-center gap-1.5 rounded-md bg-success px-4 text-sm font-semibold text-success-foreground shadow-xs transition-colors hover:bg-success/90 focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                        Approve invoice
                    </button>
                </div>

                <form x-show="open" x-cloak method="POST" action="{{ route('invoices.approve', $invoice) }}" class="mt-5">
                    @csrf
                    <label for="approval_note" class="block text-sm font-medium text-foreground">Approval note <span class="font-normal text-muted-foreground">(required)</span></label>
                    <textarea id="approval_note" name="note" rows="3" required minlength="3" maxlength="1000"
                              placeholder="Checked against the approved quotation and the signed PO."
                              class="mt-1.5 block w-full rounded-md border-input bg-background text-sm shadow-xs placeholder:text-muted-foreground focus:border-ring focus:ring-1 focus:ring-ring">{{ old('note') }}</textarea>
                    @error('note')<p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>@enderror
                    <p class="mt-2 text-xs text-warning-muted-foreground">Filed in Private Notes and on the approval revision. Never shown to the client.</p>
                    <div class="mt-4 flex items-center justify-end gap-3">
                        <button type="button" @click="open = false" class="text-sm font-medium text-muted-foreground transition-colors hover:text-foreground">Cancel</button>
                        <button type="submit"
                                class="inline-flex h-9 items-center rounded-md bg-success px-4 text-sm font-semibold text-success-foreground shadow-xs transition-colors hover:bg-success/90 focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                            Approve invoice
                        </button>
                    </div>
                </form>
            </div>
        @endif

        <!-- Active section -->
        <div class="mt-6">
            @include('invoices.sections.' . $tab)
        </div>
    </div>

    <style>
        @media print {
            body * { visibility: hidden; }
            #invoice-print-area, #invoice-print-area * { visibility: visible; }
            #invoice-print-area { position: absolute; left: 0; top: 0; width: 100%; box-shadow: none; border: 0; margin: 0; padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</x-app-layout>
