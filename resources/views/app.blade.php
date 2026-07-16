<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>CuraSys ERP</title>
    
    <link rel="manifest" href="/build/manifest.webmanifest" />
    <meta name="theme-color" content="#4f46e5" />
    <link rel="apple-touch-icon" href="/pwa-192x192.png" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-slate-900 bg-slate-50 h-screen overflow-hidden">
    <!-- Vue Router Mount Point -->
    <div id="app" class="h-full">
        <router-view></router-view>
    </div>
</body>
</html>
