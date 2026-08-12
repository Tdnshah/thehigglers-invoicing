@php
    /**
     * Settings shell: a navigation panel on the left, the active section's panel
     * on the right. Each section is its own URL, so deep links, browser back and
     * validation errors all land where the user expects.
     */
    // preserveKeys matters: the section slug is the key and it builds every link.
    $grouped = collect($sections)->groupBy('group', preserveKeys: true);
@endphp

<x-app-layout>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

        <!-- Page header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold tracking-tight text-foreground">Settings</h1>
            <p class="mt-1 text-sm text-muted-foreground">
                Company identity, document defaults, and the values every quotation and invoice inherits.
            </p>
        </div>

        <div class="flex flex-col gap-6 lg:flex-row lg:items-start">

            <!-- Section navigation -->
            <aside class="w-full lg:w-72 lg:shrink-0 lg:sticky lg:top-24">
                <nav class="overflow-hidden rounded-xl border border-border bg-card shadow-panel" aria-label="Settings sections">
                    @foreach($grouped as $groupLabel => $items)
                        <div @class(['border-t border-border' => ! $loop->first])>
                            <p class="px-4 pb-1 pt-4 text-[0.6875rem] font-bold uppercase tracking-[0.08em] text-muted-foreground">
                                {{ $groupLabel }}
                            </p>
                            <ul class="p-2">
                                @foreach($items as $key => $item)
                                    @php($isActive = $key === $section)
                                    <li>
                                        <a href="{{ route('settings.show', $key) }}"
                                           @if($isActive) aria-current="page" @endif
                                           @class([
                                               'group flex items-start gap-3 rounded-lg px-3 py-2.5 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2',
                                               'bg-sidebar-accent' => $isActive,
                                               'hover:bg-muted' => ! $isActive,
                                           ])>
                                            <svg class="mt-0.5 h-5 w-5 shrink-0 {{ $isActive ? 'text-sidebar-accent-foreground' : 'text-muted-foreground group-hover:text-foreground' }}"
                                                 fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"></path>
                                            </svg>
                                            <span class="min-w-0">
                                                <span @class([
                                                    'block text-sm',
                                                    'font-semibold text-sidebar-accent-foreground' => $isActive,
                                                    'font-medium text-foreground' => ! $isActive,
                                                ])>{{ $item['label'] }}</span>
                                                <span class="mt-0.5 block text-xs leading-snug text-muted-foreground">{{ $item['description'] }}</span>
                                            </span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </nav>
            </aside>

            <!-- Active section -->
            <div class="min-w-0 flex-1">
                <div class="overflow-hidden rounded-xl border border-border bg-card shadow-panel">
                    <div class="border-b border-border px-6 py-5">
                        <h2 class="text-base font-semibold text-foreground">{{ $meta['label'] }}</h2>
                        <p class="mt-1 text-sm text-muted-foreground">{{ $meta['description'] }}</p>
                    </div>

                    <form method="POST" action="{{ route('settings.update', $section) }}"
                          @if($section === 'company') enctype="multipart/form-data" @endif>
                        @csrf
                        @method('PATCH')

                        <div class="px-6 py-6">
                            @include('settings.sections.' . $section)
                        </div>

                        <div class="flex items-center justify-end gap-3 border-t border-border bg-muted/40 px-6 py-4">
                            <a href="{{ route('settings.show', $section) }}"
                               class="inline-flex h-9 items-center rounded-md px-3 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                                Reset
                            </a>
                            <button type="submit"
                                    class="inline-flex h-9 items-center rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground shadow-xs transition-colors hover:bg-primary/90 focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                                Save changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
