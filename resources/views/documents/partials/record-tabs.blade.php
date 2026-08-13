@php
    /**
     * Horizontal section tabs for a record page.
     *
     * Expects:
     *   $tabs   array<string, array{label: string, badge?: string|int|null}>
     *   $active string
     *   $url    callable(string $key): string
     */
@endphp
<nav class="border-t border-border px-3" aria-label="Sections">
    <ul class="-mb-px flex gap-1 overflow-x-auto scrollbar-thin">
        @foreach($tabs as $key => $tab)
            @php($isActive = $key === $active)
            <li class="shrink-0">
                <a href="{{ $url($key) }}"
                   @if($isActive) aria-current="page" @endif
                   @class([
                       'flex items-center gap-2 whitespace-nowrap border-b-2 px-3 py-3 text-sm transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2',
                       'border-primary font-semibold text-primary' => $isActive,
                       'border-transparent font-medium text-muted-foreground hover:border-border hover:text-foreground' => ! $isActive,
                   ])>
                    {{ $tab['label'] }}
                    @if(filled($tab['badge'] ?? null))
                        <span @class([
                            'rounded-full px-1.5 py-0.5 text-xs font-semibold tabular-nums',
                            'bg-primary/10 text-primary' => $isActive,
                            'bg-muted text-muted-foreground' => ! $isActive,
                        ])>{{ $tab['badge'] }}</span>
                    @endif
                </a>
            </li>
        @endforeach
    </ul>
</nav>
