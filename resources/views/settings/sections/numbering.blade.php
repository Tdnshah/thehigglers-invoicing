@php
    $generator = app(\App\Services\DocumentNumberGenerator::class);
    $sampleClient = \App\Models\Client::where('user_id', auth()->id())->first();
    $invoiceFormat = old('invoice_number_format', $company->invoice_number_format);
    $quotationFormat = old('quotation_number_format', $company->quotation_number_format);
@endphp

<div class="space-y-6">
    <div class="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2">
        <div>
            <label for="invoice_number_format" class="block text-sm font-medium text-foreground">Invoice format</label>
            <input id="invoice_number_format" name="invoice_number_format" type="text" spellcheck="false"
                   value="{{ $invoiceFormat }}" placeholder="{{ \App\Services\DocumentNumberGenerator::DEFAULT_INVOICE_FORMAT }}"
                   class="mt-1.5 block w-full rounded-md border-input bg-background font-mono text-sm shadow-xs placeholder:text-muted-foreground focus:border-ring focus:ring-1 focus:ring-ring">
            <p class="mt-1.5 flex items-center gap-1.5 text-xs text-muted-foreground">
                Next:
                <span class="rounded bg-muted px-1.5 py-0.5 font-mono font-semibold text-foreground">{{ $generator->preview($invoiceFormat ?: \App\Services\DocumentNumberGenerator::DEFAULT_INVOICE_FORMAT, $sampleClient) }}</span>
            </p>
            @error('invoice_number_format')<p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="quotation_number_format" class="block text-sm font-medium text-foreground">Quotation format</label>
            <input id="quotation_number_format" name="quotation_number_format" type="text" spellcheck="false"
                   value="{{ $quotationFormat }}" placeholder="{{ \App\Services\DocumentNumberGenerator::DEFAULT_QUOTATION_FORMAT }}"
                   class="mt-1.5 block w-full rounded-md border-input bg-background font-mono text-sm shadow-xs placeholder:text-muted-foreground focus:border-ring focus:ring-1 focus:ring-ring">
            <p class="mt-1.5 flex items-center gap-1.5 text-xs text-muted-foreground">
                Next:
                <span class="rounded bg-muted px-1.5 py-0.5 font-mono font-semibold text-foreground">{{ $generator->preview($quotationFormat ?: \App\Services\DocumentNumberGenerator::DEFAULT_QUOTATION_FORMAT, $sampleClient) }}</span>
            </p>
            @error('quotation_number_format')<p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="overflow-hidden rounded-lg border border-border">
        <p class="border-b border-border bg-muted/40 px-4 py-2.5 text-xs font-semibold uppercase tracking-wider text-muted-foreground">Tokens</p>
        <dl class="grid grid-cols-1 divide-y divide-border sm:grid-cols-2 sm:divide-y-0">
            @foreach(\App\Services\DocumentNumberGenerator::TOKENS as $token => $help)
                <div class="flex items-baseline gap-3 px-4 py-2.5">
                    <dt class="shrink-0 rounded bg-muted px-1.5 py-0.5 font-mono text-xs font-semibold text-primary">&#123;{{ $token }}&#125;</dt>
                    <dd class="text-xs text-muted-foreground">{{ $help }}</dd>
                </div>
            @endforeach
        </dl>
    </div>

    <div class="rounded-lg border border-border bg-muted/40 px-4 py-3 text-xs text-muted-foreground">
        <p>
            The running number restarts whenever the rest of the number changes, so a format with
            <span class="font-mono text-foreground">&#123;FY&#125;</span> starts again each April, and one with
            <span class="font-mono text-foreground">&#123;CLIENT&#125;</span> counts per client.
        </p>
        <p class="mt-2">
            Example: <span class="font-mono text-foreground">INV-&#123;CLIENT&#125;-&#123;YYYY&#125;-&#123;MM&#125;-&#123;SEQ:2&#125;</span>
            gives <span class="rounded bg-background px-1.5 py-0.5 font-mono font-semibold text-foreground">{{ $generator->preview('INV-{CLIENT}-{YYYY}-{MM}-{SEQ:2}', $sampleClient) }}</span>
        </p>
        <p class="mt-2">
            A GST invoice number may be at most {{ \App\Services\DocumentNumberGenerator::MAX_LENGTH }} characters and may
            contain only letters, digits, hyphen and slash. A format that breaks either rule is rejected on save.
        </p>
    </div>
</div>
