<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
<tallstackui:script />
        <!-- Styles -->
        @livewireStyles
		<style>[x-cloak]{display:none!important}</style>
	<script defer>
/* ---------- drug badge bootstrap ---------- */
(async () => {
    /* 1. Pull the JSON once per page-load */
    const res = await fetch('{{ asset('data/brand_generic.json') }}');
    const map = await res.json();

    /* 2. Flatten into two JS Sets for O(1) lookup */
    window.brandSet   = new Set(Object.keys(map).map(s => s.toUpperCase()));
    window.genericSet = new Set(
        Object.values(map).flat().map(s => s.toUpperCase())
    );

    /* 3. Helpers every script can call */
    window.normaliseDrug = str =>
        str.toUpperCase().replace(/[^A-Z0-9 ]+/g, '').trim();

    window.drugType = str => {
        const n = window.normaliseDrug(str);
        if (window.brandSet.has(n))   return 'brand';
        if (window.genericSet.has(n)) return 'generic';
        return null;         // unknown / not in dictionary
    };
})();
</script>

<style>
.badge-brand, .badge-generic {
    @apply ml-2 inline-flex items-center px-1.5 py-0.5 rounded text-xs font-semibold;
}
.badge-brand   { @apply bg-blue-100  text-blue-800;   }
.badge-generic { @apply bg-purple-100 text-purple-800; }
</style>
	
@once
    <!-- dialog() polyfill for browsers that still need it -->
    <link  rel="stylesheet"
           href="https://unpkg.com/dialog-polyfill@0.5.6/dist/dialog-polyfill.css">
    <script src="https://unpkg.com/dialog-polyfill@0.5.6/dist/dialog-polyfill.min.js"></script>
@endonce

    </head>
    <body class="font-sans antialiased">
        <x-banner />

        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            @livewire('navigation-menu')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        @stack('modals')
		 @stack('scripts')
        @livewireScripts


    </body>
</html>
