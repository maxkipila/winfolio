@pictura
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <meta name="description" content="Stav si sbírku. Sleduj její růst. Zlepšuj se a vyhrávej. Proměň svou sbírku v herní pole plné strategií, výzev a odměn. Sleduj tržní ceny, plň mise a staň se LEGO investičním šampionem.">
        <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
        <!-- Scripts -->
        @routes
        @viteReactRefresh
        @vite(['resources/js/app.tsx', "resources/js/Pages/{$page['component']}.tsx"])
        @vite(['resources/sass/app.scss'])
        @inertiaHead
        @picturaHead
    </head>
    <body class="font-sans antialiased text-base">
        @inertia
    </body>
</html>
