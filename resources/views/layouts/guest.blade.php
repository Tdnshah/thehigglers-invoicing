@php
    use App\Support\Brand;

    $pageTitle = $title ?? Brand::titleForRoute();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ Brand::title($pageTitle) }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen lg:grid lg:grid-cols-[1fr_minmax(0,44rem)] xl:grid-cols-[1fr_minmax(0,48rem)]">

            {{--
                Brand panel. Built entirely from CSS so it ships with no asset
                dependency: a graphite-to-indigo ground with the ruled grid of a
                ledger sheet drawn over it.
            --}}
            <aside class="relative hidden overflow-hidden bg-[hsl(222_47%_11%)] lg:flex lg:flex-col lg:justify-between lg:p-12">
                <div aria-hidden="true" class="pointer-events-none absolute inset-0 opacity-[0.18]"
                     style="background-image:
                        linear-gradient(to right, rgba(255,255,255,0.6) 1px, transparent 1px),
                        linear-gradient(to bottom, rgba(255,255,255,0.35) 1px, transparent 1px);
                        background-size: 96px 100%, 100% 32px;"></div>
                <div aria-hidden="true" class="pointer-events-none absolute -left-40 top-1/3 h-[36rem] w-[36rem] rounded-full opacity-40 blur-3xl"
                     style="background: radial-gradient(circle, hsl(243 75% 59% / 0.55), transparent 65%);"></div>
                <div aria-hidden="true" class="pointer-events-none absolute inset-x-0 bottom-0 h-64"
                     style="background: linear-gradient(to top, hsl(222 47% 8%), transparent);"></div>

                <div class="relative">
                    @if(Brand::logoUrl())
                        <img src="{{ Brand::logoUrl() }}" alt="{{ Brand::name() }}" class="h-11 w-auto max-w-[14rem] object-contain brightness-0 invert">
                    @else
                        <div class="flex items-center gap-3">
                            <span class="flex h-11 w-11 items-center justify-center rounded-lg bg-white/10 text-base font-bold text-white ring-1 ring-inset ring-white/20">
                                {{ Brand::initials() }}
                            </span>
                            <span class="text-lg font-semibold text-white">{{ Brand::name() }}</span>
                        </div>
                    @endif
                </div>

                <div class="relative max-w-lg">
                    <h2 class="text-4xl font-bold leading-[1.15] tracking-tight text-white">
                        Quotation to invoice to&nbsp;settled, on one ledger.
                    </h2>
                    <p class="mt-5 text-base leading-relaxed text-white/70">
                        GST compliant tax invoices, multi-currency exports under LUT, and a payment trail your
                        accountant can follow months later.
                    </p>

                    <ul class="mt-10 space-y-4">
                        @foreach([
                            'Compliant numbering, place of supply, and CGST / SGST / IGST handled by the system',
                            'Approved quotations clone straight into invoices, carrying their terms',
                            'Every approval and part payment recorded with its balance',
                        ] as $point)
                            <li class="flex items-start gap-3">
                                <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-white/10 ring-1 ring-inset ring-white/20">
                                    <svg class="h-3 w-3 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </span>
                                <span class="text-sm leading-relaxed text-white/75">{{ $point }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <p class="relative text-xs text-white/40">
                    &copy; {{ now()->year }} {{ Brand::name() }}
                </p>
            </aside>

            <!-- Form panel -->
            <main class="flex items-center justify-center bg-card px-6 py-12 sm:px-12">
                <div class="w-full max-w-sm">
                    <!-- Brand mark, small screens only -->
                    <div class="mb-10 lg:hidden">
                        @if(Brand::logoUrl())
                            <img src="{{ Brand::logoUrl() }}" alt="{{ Brand::name() }}" class="h-10 w-auto max-w-[12rem] object-contain">
                        @else
                            <div class="flex items-center gap-3">
                                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary text-sm font-bold text-primary-foreground">{{ Brand::initials() }}</span>
                                <span class="text-base font-semibold text-foreground">{{ Brand::name() }}</span>
                            </div>
                        @endif
                    </div>

                    {{ $slot }}
                </div>
            </main>
        </div>
    </body>
</html>
