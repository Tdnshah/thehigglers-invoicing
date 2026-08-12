@php
    use App\Support\Brand;

    /**
     * Application shell: fixed sidebar, sticky topbar, page header band, content.
     *
     * The $header slot is kept for backwards compatibility: pages that still
     * pass one get it rendered in the page header band, so no page had to change
     * when the shell moved from a top nav bar to a sidebar.
     */
    $segment = request()->segment(1);
    $sections = [
        'dashboard' => ['label' => 'Dashboard', 'route' => 'dashboard'],
        'invoices' => ['label' => 'Invoices', 'route' => 'invoices.index'],
        'quotations' => ['label' => 'Quotations', 'route' => 'quotations.index'],
        'clients' => ['label' => 'Clients', 'route' => 'clients.index'],
        'settings' => ['label' => 'Settings', 'route' => null],
        'profile' => ['label' => 'Profile', 'route' => null],
    ];
    $section = $sections[$segment] ?? null;
    $leaf = match (true) {
        request()->routeIs('*.create') => 'New',
        request()->routeIs('*.edit') => 'Edit',
        request()->routeIs('*.show') => 'Details',
        default => null,
    };
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ Brand::title($title ?? Brand::titleForRoute()) }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
    </head>
    <body class="font-sans antialiased">
        <div x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false" class="min-h-screen bg-background">

            <!-- Sidebar: fixed from lg up -->
            <aside class="fixed inset-y-0 left-0 z-40 hidden w-64 lg:block no-print">
                @include('layouts.sidebar')
            </aside>

            <!-- Sidebar: mobile drawer -->
            <div x-show="sidebarOpen" x-cloak class="relative z-50 lg:hidden no-print" role="dialog" aria-modal="true">
                <div x-show="sidebarOpen" x-transition.opacity.duration.200ms
                     class="fixed inset-0 bg-foreground/40" @click="sidebarOpen = false"></div>
                <div x-show="sidebarOpen"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="-translate-x-full"
                     x-transition:enter-end="translate-x-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="translate-x-0"
                     x-transition:leave-end="-translate-x-full"
                     class="fixed inset-y-0 left-0 w-64 max-w-[85vw]">
                    @include('layouts.sidebar')
                </div>
            </div>

            <div class="lg:pl-64">

                <!-- Topbar -->
                <header class="sticky top-0 z-30 flex h-16 items-center gap-3 border-b border-border bg-background/95 px-4 backdrop-blur supports-[backdrop-filter]:bg-background/80 sm:px-6 lg:px-8 no-print">
                    <button type="button" @click="sidebarOpen = true"
                            class="-ml-1 inline-flex h-9 w-9 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-accent hover:text-foreground focus:outline-none focus-visible:ring-2 focus-visible:ring-ring lg:hidden"
                            aria-label="Open navigation">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>

                    <nav class="min-w-0 flex-1" aria-label="Breadcrumb">
                        <ol class="flex items-center gap-2 text-sm">
                            @if($section)
                                <li class="min-w-0">
                                    @if($section['route'] && $leaf)
                                        <a href="{{ route($section['route']) }}" class="truncate font-medium text-muted-foreground transition-colors hover:text-foreground">{{ $section['label'] }}</a>
                                    @else
                                        <span class="truncate font-semibold text-foreground">{{ $section['label'] }}</span>
                                    @endif
                                </li>
                                @if($leaf)
                                    <li aria-hidden="true" class="text-muted-foreground/60">/</li>
                                    <li><span class="font-semibold text-foreground">{{ $leaf }}</span></li>
                                @endif
                            @endif
                        </ol>
                    </nav>

                    <div class="flex items-center gap-2">
                        @if(Auth::user()->isCompanyAdmin())
                            <a href="{{ route('quotations.create') }}"
                               class="hidden h-9 items-center gap-1.5 rounded-md border border-input bg-background px-3 text-sm font-medium text-foreground shadow-xs transition-colors hover:bg-accent focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 sm:inline-flex">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                Quotation
                            </a>
                            <a href="{{ route('invoices.create') }}"
                               class="inline-flex h-9 items-center gap-1.5 rounded-md bg-primary px-3 text-sm font-medium text-primary-foreground shadow-xs transition-colors hover:bg-primary/90 focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                Invoice
                            </a>
                        @endif
                    </div>
                </header>

                <!-- Page header band (legacy $header slot) -->
                @isset($header)
                    <div class="border-b border-border bg-card no-print">
                        <div class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </div>
                @endisset

                <!-- Flash messages -->
                @if(session('success') || session('error'))
                    <div class="mx-auto max-w-7xl px-4 pt-6 sm:px-6 lg:px-8 no-print"
                         x-data="{ show: true }" x-show="show" x-transition.opacity.duration.200ms>
                        @if(session('success'))
                            <div role="status" class="flex items-start gap-3 rounded-lg border border-success-muted bg-success-muted px-4 py-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-success-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"></path></svg>
                                <p class="flex-1 text-sm font-medium text-success-muted-foreground">{{ session('success') }}</p>
                                <button type="button" @click="show = false" class="text-success-muted-foreground/70 transition-colors hover:text-success-muted-foreground" aria-label="Dismiss">&times;</button>
                            </div>
                        @endif
                        @if(session('error'))
                            <div role="alert" class="flex items-start gap-3 rounded-lg border border-destructive-muted bg-destructive-muted px-4 py-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-destructive-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"></path></svg>
                                <p class="flex-1 text-sm font-medium text-destructive-muted-foreground">{{ session('error') }}</p>
                                <button type="button" @click="show = false" class="text-destructive-muted-foreground/70 transition-colors hover:text-destructive-muted-foreground" aria-label="Dismiss">&times;</button>
                            </div>
                        @endif
                    </div>
                @endif

                <main>
                    {{ $slot }}
                </main>
            </div>
        </div>
        @stack('scripts')
    </body>
</html>
