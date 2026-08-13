@php
    use App\Support\Money;

    /**
     * On-screen preview of a quotation or invoice, shared by both record pages.
     *
     * Expects a normalised bag so one template serves both documents:
     *   $doc      Invoice|Quotation
     *   $kindLabel string   'Tax Invoice' | 'Quotation'
     *   $number   string
     *   $dates    array<string,string|null>  label => formatted date
     *   $printId  string  the id the print stylesheet targets
     *   $extra    array<string,string|null>  extra meta rows (LUT, revision, ...)
     */
    $company = $doc->user->company ?? \App\Models\Company::first();
    $client = $doc->client;
    $currency = $doc->currency;
    $type = $doc->invoice_type ?? $doc->quotation_type;
@endphp

<div id="{{ $printId }}" class="overflow-hidden rounded-xl border border-border bg-card shadow-panel">
    <div class="p-6 sm:p-10">

        <!-- Masthead -->
        <div class="flex flex-col justify-between gap-8 border-b border-border pb-8 sm:flex-row">
            <div class="min-w-0">
                @if($company?->logo_path)
                    <img src="{{ asset('storage/' . $company->logo_path) }}" alt="{{ $company->name }}" class="mb-4 h-14 w-auto max-w-[13rem] object-contain">
                @else
                    <p class="mb-2 text-lg font-bold text-foreground">{{ $company->name ?? config('app.name') }}</p>
                @endif
                <p class="whitespace-pre-line text-sm leading-relaxed text-muted-foreground">{{ $company?->address }}</p>
                <div class="mt-2 space-y-0.5 text-sm text-muted-foreground">
                    @if($company?->phone)<p>{{ $company->phone }}</p>@endif
                    @if($company?->email)<p>{{ $company->email }}</p>@endif
                    @if($company?->gst_number)
                        <p class="pt-1"><span class="text-xs font-semibold uppercase tracking-wider">GSTIN</span> <span class="font-mono font-medium text-foreground">{{ $company->gst_number }}</span></p>
                    @endif
                </div>
            </div>

            <div class="shrink-0 sm:text-right">
                <p class="text-2xl font-bold uppercase tracking-tight text-foreground">{{ $kindLabel }}</p>
                <p class="mt-1 font-mono text-sm font-semibold text-muted-foreground">{{ $number }}</p>
                <dl class="mt-5 space-y-2">
                    @foreach($dates as $label => $value)
                        @if($value)
                            <div class="flex gap-6 sm:justify-end">
                                <dt class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">{{ $label }}</dt>
                                <dd class="text-sm font-medium tabular-nums text-foreground">{{ $value }}</dd>
                            </div>
                        @endif
                    @endforeach
                </dl>
            </div>
        </div>

        <!-- Parties -->
        <div class="grid grid-cols-1 gap-8 border-b border-border py-8 sm:grid-cols-2">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">{{ $recipientLabel }}</p>
                <p class="mt-2 text-base font-semibold text-foreground">{{ $client->name }}</p>
                @if($client->address)
                    <p class="mt-1 whitespace-pre-line text-sm leading-relaxed text-muted-foreground">{{ $client->address }}</p>
                @endif
                <div class="mt-2 space-y-0.5 text-sm text-muted-foreground">
                    @if($client->email)<p>{{ $client->email }}</p>@endif
                    @if($client->phone)<p>{{ $client->phone }}</p>@endif
                    @if($client->gst_number)
                        <p class="pt-1"><span class="text-xs font-semibold uppercase tracking-wider">GSTIN</span> <span class="font-mono font-medium text-foreground">{{ $client->gst_number }}</span></p>
                    @endif
                </div>
            </div>

            <dl class="space-y-2 sm:justify-self-end">
                <div class="flex gap-6">
                    <dt class="w-36 shrink-0 text-xs font-semibold uppercase tracking-wider text-muted-foreground">Supply type</dt>
                    <dd class="text-sm font-medium text-foreground">{{ ucfirst($type) }}</dd>
                </div>
                @if($doc->place_of_supply)
                    <div class="flex gap-6">
                        <dt class="w-36 shrink-0 text-xs font-semibold uppercase tracking-wider text-muted-foreground">Place of supply</dt>
                        <dd class="text-sm font-medium text-foreground">{{ $doc->place_of_supply }} - {{ config('gst-states.states.' . $doc->place_of_supply) ?? 'Unknown' }}</dd>
                    </div>
                @endif
                <div class="flex gap-6">
                    <dt class="w-36 shrink-0 text-xs font-semibold uppercase tracking-wider text-muted-foreground">Currency</dt>
                    <dd class="text-sm font-medium text-foreground">{{ $currency }}</dd>
                </div>
                @foreach($extra as $label => $value)
                    @if(filled($value))
                        <div class="flex gap-6">
                            <dt class="w-36 shrink-0 text-xs font-semibold uppercase tracking-wider text-muted-foreground">{{ $label }}</dt>
                            <dd class="font-mono text-sm font-medium text-foreground">{{ $value }}</dd>
                        </div>
                    @endif
                @endforeach
            </dl>
        </div>

        <!-- Items -->
        <div class="-mx-6 overflow-x-auto py-8 sm:mx-0">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b border-border">
                        <th scope="col" class="px-6 py-2 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground sm:pl-0">Description</th>
                        <th scope="col" class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">HSN/SAC</th>
                        <th scope="col" class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">Qty</th>
                        <th scope="col" class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">Rate</th>
                        <th scope="col" class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">GST</th>
                        <th scope="col" class="px-6 py-2 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground sm:pr-0">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($doc->items as $item)
                        @php
                            $taxable = $item->quantity * $item->unit_price;
                            $lineTotal = $taxable + ($taxable * $item->tax_rate / 100);
                        @endphp
                        <tr>
                            <td class="px-6 py-4 align-top text-sm text-foreground sm:pl-0">
                                <div class="rich-text-content max-w-prose">{!! $item->description !!}</div>
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-right align-top font-mono text-sm text-muted-foreground">{{ $item->hsn_code ?: '-' }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-right align-top text-sm tabular-nums text-foreground">{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-right align-top text-sm tabular-nums text-foreground">{{ Money::format($item->unit_price, $currency) }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-right align-top text-sm tabular-nums text-muted-foreground">{{ rtrim(rtrim(number_format($item->tax_rate, 2), '0'), '.') }}%</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right align-top text-sm font-semibold tabular-nums text-foreground sm:pr-0">{{ Money::format($lineTotal, $currency) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <div class="flex justify-end border-t border-border pt-6">
            <dl class="w-full max-w-xs space-y-2">
                <div class="flex items-baseline justify-between gap-6">
                    <dt class="text-sm text-muted-foreground">Taxable value</dt>
                    <dd class="text-sm font-medium tabular-nums text-foreground">{{ $currency }} {{ Money::format($doc->subtotal, $currency) }}</dd>
                </div>
                @if($doc->igst > 0)
                    <div class="flex items-baseline justify-between gap-6">
                        <dt class="text-sm text-muted-foreground">IGST</dt>
                        <dd class="text-sm font-medium tabular-nums text-foreground">{{ $currency }} {{ Money::format($doc->igst, $currency) }}</dd>
                    </div>
                @elseif($doc->cgst > 0 || $doc->sgst > 0)
                    <div class="flex items-baseline justify-between gap-6">
                        <dt class="text-sm text-muted-foreground">CGST</dt>
                        <dd class="text-sm font-medium tabular-nums text-foreground">{{ $currency }} {{ Money::format($doc->cgst, $currency) }}</dd>
                    </div>
                    <div class="flex items-baseline justify-between gap-6">
                        <dt class="text-sm text-muted-foreground">SGST</dt>
                        <dd class="text-sm font-medium tabular-nums text-foreground">{{ $currency }} {{ Money::format($doc->sgst, $currency) }}</dd>
                    </div>
                @endif
                <div class="flex items-baseline justify-between gap-6 border-t-2 border-foreground pt-3">
                    <dt class="text-base font-bold text-foreground">Total</dt>
                    <dd class="text-base font-bold tabular-nums text-foreground">{{ $currency }} {{ Money::format($doc->total, $currency) }}</dd>
                </div>
                {{ $totalsExtra ?? '' }}
            </dl>
        </div>
    </div>
</div>
