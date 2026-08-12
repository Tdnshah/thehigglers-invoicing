@php
    /**
     * Primary application navigation.
     *
     * Rendered twice by the shell: once fixed on large screens, once inside the
     * mobile drawer. Keep it presentational; the drawer owns its own state.
     */
    $company = Auth::user()->company ?? \App\Models\Company::first();
    $isAdmin = Auth::user()->isCompanyAdmin();

    $groups = array_values(array_filter([
        [
            'label' => null,
            'items' => [
                [
                    'label' => 'Dashboard',
                    'href' => route('dashboard'),
                    'active' => request()->routeIs('dashboard'),
                    'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
                ],
            ],
        ],
        [
            'label' => 'Documents',
            'items' => [
                [
                    'label' => 'Quotations',
                    'href' => route('quotations.index'),
                    'active' => request()->routeIs('quotations.*'),
                    'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                ],
                [
                    'label' => 'Invoices',
                    'href' => route('invoices.index'),
                    'active' => request()->routeIs('invoices.*'),
                    'icon' => 'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z',
                ],
            ],
        ],
        $isAdmin ? [
            'label' => 'Directory',
            'items' => [
                [
                    'label' => 'Clients',
                    'href' => route('clients.index'),
                    'active' => request()->routeIs('clients.*'),
                    'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
                ],
            ],
        ] : null,
        $isAdmin ? [
            'label' => 'Configuration',
            'items' => [
                [
                    'label' => 'Settings',
                    'href' => route('settings.show', 'company'),
                    'active' => request()->routeIs('settings.*'),
                    'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
                ],
            ],
        ] : null,
    ]));
@endphp

<div class="flex h-full flex-col overflow-hidden bg-sidebar border-r border-sidebar-border">

    <!-- Brand -->
    <div class="flex h-16 shrink-0 items-center gap-3 border-b border-sidebar-border px-5 mx-auto">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 min-w-0 rounded-md focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
            @if($company?->logo_path)
                <img src="{{ asset('storage/' . $company->logo_path) }}" alt="{{ $company->name }}" class="h-10 w-auto max-w-[14rem] object-contain">
            @else
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-primary text-primary-foreground text-sm font-bold">
                    {{ strtoupper(substr($company->name ?? 'H', 0, 1)) }}
                </span>
                <span class="truncate text-sm font-semibold text-foreground">{{ $company->name ?? config('app.name') }}</span>
            @endif
        </a>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto scrollbar-thin px-4 py-5" aria-label="Main">
        @foreach($groups as $group)
            <div @class(['mt-6' => ! $loop->first])>
                @if($group['label'])
                    <p class="px-3 pb-2 text-[0.6875rem] font-bold uppercase tracking-[0.08em] text-sidebar-muted">{{ $group['label'] }}</p>
                @endif
                <ul class="space-y-1">
                    @foreach($group['items'] as $item)
                        <li>
                            <a href="{{ $item['href'] }}"
                               @if($item['active']) aria-current="page" @endif
                               @class([
                                   'group relative flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2',
                                   'bg-sidebar-accent font-semibold text-sidebar-accent-foreground' => $item['active'],
                                   'font-medium text-sidebar-foreground hover:bg-muted hover:text-foreground' => ! $item['active'],
                               ])>
                                <svg class="h-5 w-5 shrink-0 {{ $item['active'] ? 'text-sidebar-accent-foreground' : 'text-sidebar-muted group-hover:text-foreground' }}"
                                     fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"></path>
                                </svg>
                                <span class="truncate">{{ $item['label'] }}</span>
                                @if($item['active'])
                                    <span aria-hidden="true" class="absolute inset-y-1.5 -left-3 w-1 rounded-r-full bg-primary"></span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </nav>

    <!-- Account -->
    <div class="shrink-0 border-t border-sidebar-border p-3">
        <div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
            <button type="button" @click="open = !open" :aria-expanded="open" aria-haspopup="menu"
                    class="flex w-full items-center gap-3 rounded-md px-3 py-2 text-left transition-colors hover:bg-sidebar-accent focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-secondary text-xs font-semibold text-secondary-foreground">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block truncate text-sm font-medium text-foreground">{{ Auth::user()->name }}</span>
                    <span class="block truncate text-xs text-muted-foreground">{{ Auth::user()->email }}</span>
                </span>
                <svg class="h-4 w-4 shrink-0 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                </svg>
            </button>

            <div x-show="open" x-cloak x-transition.opacity.duration.150ms role="menu"
                 class="absolute bottom-full left-0 z-50 mb-2 w-full min-w-[12rem] overflow-hidden rounded-md border border-border bg-popover p-1 shadow-lg">
                <a href="{{ route('profile.edit') }}" role="menuitem"
                   class="block rounded-sm px-3 py-2 text-sm text-popover-foreground hover:bg-accent focus:bg-accent focus:outline-none">Profile</a>
                @if($isAdmin)
                    <a href="{{ route('settings.show', 'company') }}" role="menuitem"
                       class="block rounded-sm px-3 py-2 text-sm text-popover-foreground hover:bg-accent focus:bg-accent focus:outline-none">Settings</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" role="menuitem"
                            class="block w-full rounded-sm px-3 py-2 text-left text-sm text-popover-foreground hover:bg-accent focus:bg-accent focus:outline-none">Log out</button>
                </form>
            </div>
        </div>
    </div>
</div>
