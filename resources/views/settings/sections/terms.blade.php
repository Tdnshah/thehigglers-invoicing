<div class="space-y-5">
    <div>
        <label for="terms_conditions" class="block text-sm font-medium text-foreground">Company terms &amp; conditions</label>
        <textarea id="terms_conditions" name="terms_conditions" rows="10"
                  placeholder="1. Payment due within 15 days of the invoice date.&#10;2. Interest at 18% per annum on overdue amounts."
                  class="mt-1.5 block w-full rounded-md border-input bg-background text-sm leading-relaxed shadow-xs placeholder:text-muted-foreground focus:border-ring focus:ring-1 focus:ring-ring">{{ old('terms_conditions', $company->terms_conditions) }}</textarea>
        @error('terms_conditions')<p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>@enderror
    </div>

    <div class="rounded-lg border border-border bg-muted/40 px-4 py-3">
        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">How each document uses this</p>
        <dl class="mt-2 space-y-1 text-xs text-muted-foreground">
            <div class="flex gap-2"><dt class="w-32 shrink-0 font-medium text-foreground">Use company terms</dt><dd>prints this block as written.</dd></div>
            <div class="flex gap-2"><dt class="w-32 shrink-0 font-medium text-foreground">Use its own</dt><dd>ignores this block entirely.</dd></div>
            <div class="flex gap-2"><dt class="w-32 shrink-0 font-medium text-foreground">Both</dt><dd>prints this block first, then the document's own, one blank line apart.</dd></div>
            <div class="flex gap-2"><dt class="w-32 shrink-0 font-medium text-foreground">None</dt><dd>prints no terms at all.</dd></div>
        </dl>
    </div>
</div>
