@php
    /** Company bank details, shared by both record pages. Expects $company. */
    $rows = collect([
        'Bank' => $company?->bank_name,
        'Account number' => $company?->bank_account_number,
        'IFSC' => $company?->bank_ifsc,
    ])->filter(fn ($v) => filled($v));
@endphp

<div class="overflow-hidden rounded-xl border border-border bg-card shadow-panel no-print">
    <div class="border-b border-border px-6 py-5">
        <h2 class="text-base font-semibold text-foreground">Bank details</h2>
        <p class="mt-1 text-sm text-muted-foreground">The company defaults. A document can override or extend these on its own form.</p>
    </div>
    <div class="p-6">
        @if($rows->isNotEmpty())
            <dl class="grid max-w-xl grid-cols-1 gap-px overflow-hidden rounded-lg border border-border bg-border sm:grid-cols-3">
                @foreach($rows as $label => $value)
                    <div class="bg-card px-4 py-3">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">{{ $label }}</dt>
                        <dd class="mt-1 break-all font-mono text-sm font-medium text-foreground">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        @else
            <div class="rounded-lg border border-dashed border-border bg-muted/40 px-6 py-10 text-center">
                <p class="text-sm text-muted-foreground">No bank details set.</p>
                @if(Auth::user()->isCompanyAdmin())
                    <a href="{{ route('settings.show', 'bank') }}" class="mt-2 inline-block text-sm font-medium text-primary hover:text-primary/80">Add them in Settings</a>
                @endif
            </div>
        @endif
    </div>
</div>
