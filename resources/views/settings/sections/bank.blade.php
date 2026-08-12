@php
    $accounts = collect(old('accounts'))->filter(fn ($a) => is_array($a))->values();

    if ($accounts->isEmpty()) {
        $accounts = $company->bankAccounts->map(fn ($a) => [
            'id' => $a->id,
            'label' => $a->label,
            'bank_name' => $a->bank_name,
            'account_number' => $a->account_number,
            'ifsc' => $a->ifsc,
            'swift' => $a->swift,
            'iban' => $a->iban,
            'branch' => $a->branch,
        ])->values();
    }

    $defaultId = old('default_account', $company->defaultBankAccount()?->id);
@endphp

<div x-data="{
        accounts: {{ Js::from($accounts) }},
        defaultAccount: '{{ $defaultId }}',
        add() {
            this.accounts.push({ id: '', label: '', bank_name: '', account_number: '', ifsc: '', swift: '', iban: '', branch: '' });
            if (this.accounts.length === 1) this.defaultAccount = 'new-0';
        },
        remove(i) { this.accounts.splice(i, 1) },
        keyFor(row, i) { return row.id ? String(row.id) : 'new-' + i }
     }"
     class="space-y-4">

    <template x-if="accounts.length === 0">
        <div class="rounded-lg border border-dashed border-border bg-muted/40 px-6 py-10 text-center">
            <p class="text-sm text-muted-foreground">No bank accounts yet.</p>
            <p class="mt-1 text-xs text-muted-foreground">Add one for domestic collections, and another for exports if the account differs.</p>
        </div>
    </template>

    <template x-for="(account, index) in accounts" :key="index">
        <div class="rounded-lg border border-border bg-card p-5">
            <input type="hidden" :name="'accounts[' + index + '][id]'" :value="account.id">

            <div class="mb-4 flex items-start justify-between gap-4">
                <div class="min-w-0 flex-1">
                    <label class="block text-xs font-medium text-foreground" :for="'label-' + index">Account name</label>
                    <input type="text" :id="'label-' + index" :name="'accounts[' + index + '][label]'" x-model="account.label" required maxlength="80"
                           placeholder="HDFC current account, or Export USD account"
                           class="mt-1.5 block w-full rounded-md border-input bg-background text-sm shadow-xs placeholder:text-muted-foreground focus:border-ring focus:ring-1 focus:ring-ring">
                </div>
                <button type="button" @click="remove(index)" aria-label="Remove account"
                        class="mt-6 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-destructive-muted hover:text-destructive focus:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </button>
            </div>

            <div class="grid grid-cols-1 gap-x-4 gap-y-3 sm:grid-cols-2">
                <div>
                    <label class="block text-xs font-medium text-muted-foreground" :for="'bank-' + index">Bank</label>
                    <input type="text" :id="'bank-' + index" :name="'accounts[' + index + '][bank_name]'" x-model="account.bank_name"
                           class="mt-1 block w-full rounded-md border-input bg-background text-sm shadow-xs focus:border-ring focus:ring-1 focus:ring-ring">
                </div>
                <div>
                    <label class="block text-xs font-medium text-muted-foreground" :for="'branch-' + index">Branch</label>
                    <input type="text" :id="'branch-' + index" :name="'accounts[' + index + '][branch]'" x-model="account.branch"
                           class="mt-1 block w-full rounded-md border-input bg-background text-sm shadow-xs focus:border-ring focus:ring-1 focus:ring-ring">
                </div>
                <div>
                    <label class="block text-xs font-medium text-muted-foreground" :for="'acct-' + index">Account number</label>
                    <input type="text" :id="'acct-' + index" :name="'accounts[' + index + '][account_number]'" x-model="account.account_number"
                           class="mt-1 block w-full rounded-md border-input bg-background font-mono text-sm shadow-xs focus:border-ring focus:ring-1 focus:ring-ring">
                </div>
                <div>
                    <label class="block text-xs font-medium text-muted-foreground" :for="'ifsc-' + index">IFSC</label>
                    <input type="text" :id="'ifsc-' + index" :name="'accounts[' + index + '][ifsc]'" x-model="account.ifsc" maxlength="20"
                           class="mt-1 block w-full rounded-md border-input bg-background font-mono text-sm uppercase shadow-xs focus:border-ring focus:ring-1 focus:ring-ring">
                </div>
                <div>
                    <label class="block text-xs font-medium text-muted-foreground" :for="'swift-' + index">SWIFT <span class="font-normal">(exports)</span></label>
                    <input type="text" :id="'swift-' + index" :name="'accounts[' + index + '][swift]'" x-model="account.swift" maxlength="20"
                           class="mt-1 block w-full rounded-md border-input bg-background font-mono text-sm uppercase shadow-xs focus:border-ring focus:ring-1 focus:ring-ring">
                </div>
                <div>
                    <label class="block text-xs font-medium text-muted-foreground" :for="'iban-' + index">IBAN <span class="font-normal">(exports)</span></label>
                    <input type="text" :id="'iban-' + index" :name="'accounts[' + index + '][iban]'" x-model="account.iban" maxlength="40"
                           class="mt-1 block w-full rounded-md border-input bg-background font-mono text-sm uppercase shadow-xs focus:border-ring focus:ring-1 focus:ring-ring">
                </div>
            </div>

            <label class="mt-4 inline-flex items-center gap-2">
                <input type="radio" name="default_account" :value="keyFor(account, index)" x-model="defaultAccount"
                       class="h-4 w-4 border-input text-primary focus:ring-ring">
                <span class="text-xs text-muted-foreground">Use as the default on new documents</span>
            </label>
        </div>
    </template>

    <div class="flex flex-wrap items-center justify-between gap-4">
        <button type="button" @click="add()"
                class="inline-flex h-9 items-center gap-1.5 rounded-md border border-input bg-background px-3 text-sm font-medium text-foreground shadow-xs transition-colors hover:bg-accent focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path></svg>
            Add bank account
        </button>
        <p class="text-xs text-muted-foreground">Each invoice and quotation picks the account its client pays into.</p>
    </div>

    @error('accounts')<p class="text-xs font-medium text-destructive">{{ $message }}</p>@enderror
    @error('accounts.*.label')<p class="text-xs font-medium text-destructive">{{ $message }}</p>@enderror
</div>
