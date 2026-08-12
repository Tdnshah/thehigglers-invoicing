@php
    /**
     * The status pill, as a menu when the viewer can change it.
     *
     * Status is deliberately not part of the edit form: changing it is a
     * one-click workflow action, and putting it in the form meant a save could
     * silently rewrite it.
     *
     * Expects:
     *   $current  string
     *   $options  array<string,string>  value => label, empty when locked
     *   $action   string                 route to POST to
     *   $styles   array<string,string>   status => pill classes
     *   $lockNote string|null            why it cannot change, shown as a tooltip
     */
    $pill = $styles[$current] ?? 'bg-muted text-foreground';
    $selectable = collect($options)->except($current);
@endphp

@if($selectable->isEmpty())
    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold uppercase tracking-wider {{ $pill }}"
          @if($lockNote ?? null) title="{{ $lockNote }}" @endif>
        {{ $current }}
    </span>
@else
    <div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
        <button type="button" @click="open = !open" :aria-expanded="open" aria-haspopup="menu"
                class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold uppercase tracking-wider transition-opacity hover:opacity-80 focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 {{ $pill }}">
            {{ $current }}
            <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>

        <div x-show="open" x-cloak x-transition.opacity.duration.150ms role="menu"
             class="absolute left-0 top-full z-40 mt-1.5 w-48 overflow-hidden rounded-md border border-border bg-popover p-1 shadow-lg">
            <p class="px-3 py-1.5 text-xs font-semibold uppercase tracking-wider text-muted-foreground">Change status to</p>
            @foreach($selectable as $value => $label)
                <form method="POST" action="{{ $action }}">
                    @csrf
                    <input type="hidden" name="status" value="{{ $value }}">
                    <button type="submit" role="menuitem"
                            class="block w-full rounded-sm px-3 py-2 text-left text-sm text-popover-foreground transition-colors hover:bg-accent focus:bg-accent focus:outline-none">
                        {{ $label }}
                    </button>
                </form>
            @endforeach
        </div>
    </div>
@endif
