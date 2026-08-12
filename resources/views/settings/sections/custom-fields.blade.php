@php
    $existing = collect(old('custom_fields', $company->custom_fields ?? []))
        ->filter(fn ($f) => is_array($f))
        ->map(fn ($f) => [
            'key' => $f['key'] ?? '',
            'value' => $f['value'] ?? '',
            'show_in_invoice' => (bool) ($f['show_in_invoice'] ?? false),
            'show_in_quotation' => (bool) ($f['show_in_quotation'] ?? false),
        ])
        ->values()
        ->all();
@endphp

<div x-data="{
        rows: {{ Js::from($existing) }},
        add() { this.rows.push({ key: '', value: '', show_in_invoice: false, show_in_quotation: false }) },
        remove(i) { this.rows.splice(i, 1) }
     }"
     class="space-y-4">

    <div class="overflow-hidden rounded-lg border border-border">
        <div class="hidden grid-cols-12 gap-3 border-b border-border bg-muted/40 px-4 py-2.5 text-[0.6875rem] font-bold uppercase tracking-[0.08em] text-muted-foreground sm:grid">
            <div class="col-span-4">Field name</div>
            <div class="col-span-4">Value</div>
            <div class="col-span-3 text-center">Available on</div>
            <div class="col-span-1"></div>
        </div>

        <template x-if="rows.length === 0">
            <div class="px-4 py-10 text-center">
                <p class="text-sm text-muted-foreground">No custom fields yet.</p>
                <p class="mt-1 text-xs text-muted-foreground">Add one for each LUT registration, or for any value you want to print on documents.</p>
            </div>
        </template>

        <template x-for="(row, index) in rows" :key="index">
            <div class="grid grid-cols-1 gap-3 border-b border-border px-4 py-3 last:border-b-0 sm:grid-cols-12 sm:items-center">
                <div class="sm:col-span-4">
                    <input type="text" :name="'custom_fields[' + index + '][key]'" x-model="row.key" maxlength="255"
                           placeholder="LUT Number - 2026-2027"
                           class="block w-full rounded-md border-input bg-background text-sm shadow-xs placeholder:text-muted-foreground focus:border-ring focus:ring-1 focus:ring-ring">
                </div>
                <div class="sm:col-span-4">
                    <input type="text" :name="'custom_fields[' + index + '][value]'" x-model="row.value" maxlength="255"
                           placeholder="AD270426032855V"
                           class="block w-full rounded-md border-input bg-background font-mono text-sm shadow-xs placeholder:text-muted-foreground focus:border-ring focus:ring-1 focus:ring-ring">
                </div>
                <div class="flex items-center justify-start gap-4 sm:col-span-3 sm:justify-center">
                    <label class="inline-flex items-center gap-1.5 text-xs text-muted-foreground">
                        <input type="hidden" :name="'custom_fields[' + index + '][show_in_invoice]'" value="0">
                        <input type="checkbox" :name="'custom_fields[' + index + '][show_in_invoice]'" x-model="row.show_in_invoice" value="1"
                               class="h-4 w-4 rounded border-input text-primary focus:ring-ring">
                        Invoice
                    </label>
                    <label class="inline-flex items-center gap-1.5 text-xs text-muted-foreground">
                        <input type="hidden" :name="'custom_fields[' + index + '][show_in_quotation]'" value="0">
                        <input type="checkbox" :name="'custom_fields[' + index + '][show_in_quotation]'" x-model="row.show_in_quotation" value="1"
                               class="h-4 w-4 rounded border-input text-primary focus:ring-ring">
                        Quotation
                    </label>
                </div>
                <div class="sm:col-span-1 sm:text-right">
                    <button type="button" @click="remove(index)"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-destructive-muted hover:text-destructive focus:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                            aria-label="Remove field">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </template>
    </div>

    <div class="flex items-center justify-between gap-4">
        <button type="button" @click="add()"
                class="inline-flex h-9 items-center gap-1.5 rounded-md border border-input bg-background px-3 text-sm font-medium text-foreground shadow-xs transition-colors hover:bg-accent focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path></svg>
            Add field
        </button>
        <p class="text-xs text-muted-foreground">Ticking a box makes the field selectable on that document type, not automatic.</p>
    </div>

    @error('custom_fields')<p class="text-xs font-medium text-destructive">{{ $message }}</p>@enderror
</div>
