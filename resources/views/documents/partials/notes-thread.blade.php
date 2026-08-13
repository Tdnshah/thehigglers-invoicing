@php
    /**
     * Internal notes thread, shared by invoices and quotations.
     *
     * Expects:
     *   $notes         (Collection) newest first
     *   $storeUrl      (string)
     *   $destroyRoute  (string) route name taking the note
     *   $kind          (string) 'invoice' | 'quotation'
     *   $canWrite      (bool)
     */
    $kind = $kind ?? 'document';
    $canWrite = $canWrite ?? false;
@endphp

<div class="space-y-6">

    <div class="flex items-start gap-3 rounded-lg border border-warning-muted bg-warning-muted px-4 py-3">
        <svg class="mt-0.5 h-4 w-4 shrink-0 text-warning-muted-foreground" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
        </svg>
        <p class="text-sm text-warning-muted-foreground">
            Visible to your team only. These notes never appear to the client, on the {{ $kind }}, in the print view, or in the PDF.
        </p>
    </div>

    @if($canWrite)
        <form method="POST" action="{{ $storeUrl }}">
            @csrf
            <label for="note" class="block text-sm font-medium text-foreground">Add a note</label>
            <textarea id="note" name="note" rows="3" maxlength="1000" required
                      placeholder="Payment chased, client feedback, internal memos..."
                      class="mt-1.5 block w-full rounded-md border-input bg-background text-sm shadow-xs placeholder:text-muted-foreground focus:border-ring focus:ring-1 focus:ring-ring">{{ old('note') }}</textarea>
            @error('note')<p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>@enderror
            <div class="mt-3 flex justify-end">
                <button type="submit"
                        class="inline-flex h-9 items-center rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground shadow-xs transition-colors hover:bg-primary/90 focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    Add note
                </button>
            </div>
        </form>
    @endif

    @forelse($notes as $note)
        @php($author = $note->user->name ?? 'Unknown')
        <article class="flex gap-3 border-t border-border pt-5 first:border-t-0 first:pt-0">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-secondary text-xs font-semibold text-secondary-foreground" aria-hidden="true">
                {{ mb_strtoupper(mb_substr($author, 0, 1)) }}
            </span>
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
                    <p class="text-sm font-semibold text-foreground">{{ $author }}</p>
                    <div class="flex items-center gap-3">
                        <time datetime="{{ $note->created_at->toIso8601String() }}" class="text-xs text-muted-foreground">
                            {{ $note->created_at->format('d M Y, H:i') }}
                        </time>
                        @if($canWrite)
                            <form method="POST" action="{{ route($destroyRoute, $note) }}" onsubmit="return confirm('Delete this note?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="rounded text-xs font-medium text-muted-foreground transition-colors hover:text-destructive focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                                    Delete
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
                <p class="mt-1.5 whitespace-pre-line text-sm leading-relaxed text-foreground/90">{{ $note->note }}</p>
            </div>
        </article>
    @empty
        <div class="rounded-lg border border-dashed border-border bg-muted/40 px-6 py-10 text-center">
            <p class="text-sm text-muted-foreground">No internal notes on this {{ $kind }} yet.</p>
            @if($canWrite)
                <p class="mt-1 text-xs text-muted-foreground">Anything you add here stays with your team.</p>
            @endif
        </div>
    @endforelse
</div>
