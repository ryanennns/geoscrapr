<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title inertia>GeoScrapr</title>

    @vite(['resources/css/app.css', 'resources/js/app.ts'])
    @inertiaHead
</head>
<body class="antialiased page-wrapper">
@inertia
</body>
</html>

<style>
    ::-webkit-scrollbar {
        display: none;
    }
    html, body {
        -ms-overflow-style: none; /* IE/Edge */
        scrollbar-width: none; /* Firefox */
        overflow-y: scroll;
    }
</style>
