@php
    /**
     * Terms and bank details for an invoice or quotation form.
     *
     * Expects:
     *   $document (Invoice|Quotation|null) - null on a create form
     *   $company  (Company|null)           - supplies the global terms and the accounts
     *   $kind     (string)                 - 'invoice' or 'quotation', used in copy
     */
    $kind = $kind ?? 'invoice';
    $termsMode = old('terms_mode', $document->terms_mode ?? 'global');
    $bankMode = old('bank_mode', $document->bank_mode ?? 'account');
    $bankMode = $bankMode === 'global' ? 'account' : $bankMode;
    $termsBody = old('terms_conditions', $document->terms_conditions ?? '');
    $bankRows = collect(old('bank_details', $document->bank_details ?? []))
        ->filter(fn ($r) => is_array($r))->values()->all();

    $accounts = $company?->bankAccounts ?? collect();
    $defaultAccount = $company?->defaultBankAccount();
    $selectedAccountId = (int) old('bank_account_id', $document->bank_account_id ?? $defaultAccount?->id);
@endphp

<div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-2">

    <!-- Terms & Conditions -->
    <div x-data="{ mode: '{{ $termsMode }}', showGlobal: false }" class="rounded-xl border border-border bg-card p-5">
        <h3 class="text-sm font-semibold text-foreground">Terms &amp; conditions</h3>
        <p class="mt-0.5 text-xs text-muted-foreground">Printed at the foot of the {{ $kind }}.</p>

        <select name="terms_mode" x-model="mode"
                class="mt-4 block h-10 w-full rounded-md border-input bg-background px-3 text-sm shadow-xs focus:border-ring focus:ring-1 focus:ring-ring">
            <option value="global">Use company terms</option>
            <option value="custom">Use terms written here</option>
            <option value="both">Both, company terms first</option>
            <option value="none">No terms on this {{ $kind }}</option>
        </select>

        <template x-if="mode === 'global' || mode === 'both'">
            <div class="mt-3">
                @if(filled($company?->terms_conditions))
                    <button type="button" @click="showGlobal = !showGlobal"
                            class="text-xs font-medium text-primary transition-colors hover:text-primary/80">
                        <span x-text="showGlobal ? 'Hide company terms' : 'Show company terms'"></span>
                    </button>
                    <div x-show="showGlobal" x-cloak class="mt-2 max-h-40 overflow-y-auto whitespace-pre-line rounded-md border border-border bg-muted/40 p-3 text-xs text-muted-foreground">{{ $company->terms_conditions }}</div>
                @else
                    <p class="rounded-md border border-warning-muted bg-warning-muted px-3 py-2 text-xs text-warning-muted-foreground">
                        No company terms set. Add them in <a href="{{ route('settings.show', 'terms') }}" class="font-semibold underline">Settings</a>.
                    </p>
                @endif
            </div>
        </template>

        <div x-show="mode === 'custom' || mode === 'both'" x-cloak class="mt-3">
            <label for="terms_conditions" class="block text-xs font-medium text-foreground">Terms for this {{ $kind }}</label>
            <textarea id="terms_conditions" name="terms_conditions" rows="5"
                      placeholder="Payment due within 15 days. Work begins on receipt of advance."
                      class="mt-1.5 block w-full rounded-md border-input bg-background text-sm shadow-xs placeholder:text-muted-foreground focus:border-ring focus:ring-1 focus:ring-ring">{{ $termsBody }}</textarea>
            @error('terms_conditions')<p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>@enderror
        </div>
    </div>

    <!-- Bank details -->
    <div x-data="{
            mode: '{{ $bankMode }}',
            accountId: '{{ $selectedAccountId ?: '' }}',
            accounts: {{ Js::from($accounts->mapWithKeys(fn ($a) => [$a->id => $a->rows()])) }},
            rows: {{ Js::from($bankRows ?: [['label' => '', 'value' => '']]) }},
            add() { this.rows.push({ label: '', value: '' }) },
            remove(i) { this.rows.splice(i, 1); if (!this.rows.length) this.add() },
            get preview() { return this.accounts[this.accountId] || {} }
         }" class="rounded-xl border border-border bg-card p-5">
        <h3 class="text-sm font-semibold text-foreground">Bank details</h3>
        <p class="mt-0.5 text-xs text-muted-foreground">Which account this client should pay into.</p>

        <select name="bank_mode" x-model="mode"
                class="mt-4 block h-10 w-full rounded-md border-input bg-background px-3 text-sm shadow-xs focus:border-ring focus:ring-1 focus:ring-ring">
            <option value="account">Use a company bank account</option>
            <option value="custom">Use the rows below only</option>
            <option value="both">An account plus the rows below</option>
            <option value="none">No bank details on this {{ $kind }}</option>
        </select>

        <div x-show="mode === 'account' || mode === 'both'" x-cloak class="mt-3">
            @if($accounts->isNotEmpty())
                <label for="bank_account_id" class="block text-xs font-medium text-foreground">Account</label>
                <select id="bank_account_id" name="bank_account_id" x-model="accountId"
                        class="mt-1.5 block h-10 w-full rounded-md border-input bg-background px-3 text-sm shadow-xs focus:border-ring focus:ring-1 focus:ring-ring">
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}" @selected($selectedAccountId === $account->id)>
                            {{ $account->label }}@if($account->summary()) &mdash; {{ $account->summary() }}@endif @if($account->is_default) (default) @endif
                        </option>
                    @endforeach
                </select>

                <dl class="mt-3 space-y-1 rounded-md border border-border bg-muted/40 px-3 py-2.5">
                    <template x-for="(value, label) in preview" :key="label">
                        <div class="flex gap-3 text-xs">
                            <dt class="w-24 shrink-0 font-semibold uppercase tracking-wider text-muted-foreground" x-text="label"></dt>
                            <dd class="min-w-0 break-all font-mono text-foreground" x-text="value"></dd>
                        </div>
                    </template>
                </dl>
            @else
                <p class="rounded-md border border-warning-muted bg-warning-muted px-3 py-2 text-xs text-warning-muted-foreground">
                    No bank accounts yet. Add one in <a href="{{ route('settings.show', 'bank') }}" class="font-semibold underline">Settings</a>.
                </p>
            @endif
        </div>

        <div x-show="mode === 'custom' || mode === 'both'" x-cloak class="mt-3">
            <p class="mb-1.5 text-xs font-medium text-foreground">Extra rows</p>
            <template x-for="(row, index) in rows" :key="index">
                <div class="mb-2 flex gap-2">
                    <input type="text" :name="'bank_details[' + index + '][label]'" x-model="row.label" maxlength="60" placeholder="Reference"
                           class="block h-9 w-2/5 rounded-md border-input bg-background px-3 text-sm shadow-xs placeholder:text-muted-foreground focus:border-ring focus:ring-1 focus:ring-ring">
                    <input type="text" :name="'bank_details[' + index + '][value]'" x-model="row.value" maxlength="120" placeholder="Quote the invoice number"
                           class="block h-9 flex-1 rounded-md border-input bg-background px-3 text-sm shadow-xs placeholder:text-muted-foreground focus:border-ring focus:ring-1 focus:ring-ring">
                    <button type="button" @click="remove(index)" aria-label="Remove row"
                            class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-destructive-muted hover:text-destructive">&times;</button>
                </div>
            </template>
            <button type="button" @click="add()" class="text-xs font-medium text-primary transition-colors hover:text-primary/80">+ Add row</button>
            <p class="mt-2 text-xs text-muted-foreground">A row with the same label as one on the account replaces it.</p>
        </div>
    </div>
</div>
