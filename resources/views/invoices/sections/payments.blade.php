@php
    use App\Support\Money;
    $isAdmin = Auth::user()->isCompanyAdmin();
@endphp

<div class="space-y-6 no-print">

    <!-- Ledger -->
    <div class="overflow-hidden rounded-xl border border-border bg-card shadow-panel">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-border px-6 py-5">
            <div>
                <h2 class="text-base font-semibold text-foreground">Payments ledger</h2>
                <p class="mt-1 text-sm text-muted-foreground">Every amount received against this invoice.</p>
            </div>
            <p class="text-sm text-muted-foreground">
                Balance due
                <span class="ml-1 font-semibold tabular-nums text-foreground">{{ $invoice->currency }} {{ Money::format($invoice->balanceDue(), $invoice->currency) }}</span>
            </p>
        </div>

        @if($invoice->payments->count())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-muted/40">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">Method</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">Reference</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach($invoice->payments->sortBy('payment_date') as $payment)
                            <tr class="transition-colors hover:bg-muted/40">
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-muted-foreground">{{ $payment->payment_date->format('d M Y') }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-foreground">{{ $payment->payment_method ?: '-' }}</td>
                                <td class="whitespace-nowrap px-6 py-4 font-mono text-sm text-muted-foreground">{{ $payment->transaction_reference }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-semibold tabular-nums text-success-muted-foreground">{{ $invoice->currency }} {{ Money::format($payment->amount, $invoice->currency) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-t-2 border-border bg-muted/40">
                        <tr>
                            <td colspan="3" class="px-6 py-3 text-right text-sm font-semibold text-foreground">Received</td>
                            <td class="px-6 py-3 text-right text-sm font-bold tabular-nums text-foreground">{{ $invoice->currency }} {{ Money::format($invoice->amountPaid(), $invoice->currency) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <div class="px-6 py-12 text-center">
                <p class="text-sm text-muted-foreground">No payments recorded yet.</p>
            </div>
        @endif
    </div>

    <!-- Record a payment -->
    @if($isAdmin && $invoice->isPaid())
        <div class="flex items-start gap-3 rounded-xl border border-success-muted bg-success-muted px-6 py-5">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-success-muted-foreground" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div>
                <h3 class="text-sm font-semibold text-foreground">Settled and closed</h3>
                <p class="mt-1 text-sm text-success-muted-foreground">This invoice is fully paid. Nothing further can be recorded against it.</p>
            </div>
        </div>
    @elseif($isAdmin && ! $invoice->isApproved())
        <div class="flex items-start gap-3 rounded-xl border border-warning-muted bg-warning-muted px-6 py-5">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-warning-muted-foreground" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            <div>
                <h3 class="text-sm font-semibold text-foreground">Payments are locked</h3>
                <p class="mt-1 text-sm text-warning-muted-foreground">Approve this invoice first. Payments are only accepted between approval and full settlement.</p>
            </div>
        </div>
    @elseif($isAdmin && $invoice->acceptsPayments())
        <div class="overflow-hidden rounded-xl border border-border bg-card shadow-panel">
            <div class="border-b border-border px-6 py-5">
                <h2 class="text-base font-semibold text-foreground">Record a payment</h2>
                <p class="mt-1 text-sm text-muted-foreground">Part payments are allowed. Each one writes a revision with the new balance.</p>
            </div>
            <form method="POST" action="{{ route('payments.store', $invoice) }}">
                @csrf
                <div class="grid grid-cols-1 gap-x-6 gap-y-5 px-6 py-6 sm:grid-cols-2">
                    <div>
                        <label for="amount" class="block text-sm font-medium text-foreground">Amount received</label>
                        <div class="relative mt-1.5">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-muted-foreground">{{ $invoice->currency }}</span>
                            <input id="amount" name="amount" type="number" step="0.01" min="0.01" max="{{ $invoice->balanceDue() }}" required
                                   value="{{ old('amount', $invoice->balanceDue()) }}"
                                   class="block h-10 w-full rounded-md border-input bg-background pl-14 pr-3 text-sm tabular-nums shadow-xs focus:border-ring focus:ring-1 focus:ring-ring">
                        </div>
                        @error('amount')<p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="payment_date" class="block text-sm font-medium text-foreground">Payment date</label>
                        <input id="payment_date" name="payment_date" type="date" required value="{{ old('payment_date', date('Y-m-d')) }}"
                               class="mt-1.5 block h-10 w-full rounded-md border-input bg-background px-3 text-sm shadow-xs focus:border-ring focus:ring-1 focus:ring-ring">
                        @error('payment_date')<p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="payment_method" class="block text-sm font-medium text-foreground">Method</label>
                        <select id="payment_method" name="payment_method"
                                class="mt-1.5 block h-10 w-full rounded-md border-input bg-background px-3 text-sm shadow-xs focus:border-ring focus:ring-1 focus:ring-ring">
                            @foreach(['Bank Transfer', 'UPI', 'Cheque', 'Cash', 'Other'] as $method)
                                <option value="{{ $method }}" @selected(old('payment_method') === $method)>{{ $method }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="transaction_reference" class="block text-sm font-medium text-foreground">Transaction reference</label>
                        <input id="transaction_reference" name="transaction_reference" type="text" required value="{{ old('transaction_reference') }}"
                               class="mt-1.5 block h-10 w-full rounded-md border-input bg-background px-3 font-mono text-sm shadow-xs focus:border-ring focus:ring-1 focus:ring-ring">
                        @error('transaction_reference')<p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="flex justify-end border-t border-border bg-muted/40 px-6 py-4">
                    <button type="submit"
                            class="inline-flex h-9 items-center rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground shadow-xs transition-colors hover:bg-primary/90 focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                        Save payment
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
