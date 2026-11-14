<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, minimal-ui">
        <meta name="theme-color" content="#292524">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="apple-mobile-web-app-title" content="Infak Assajjad">

        <link rel="apple-touch-icon" href="/icon-192x192.png">
        <link rel="manifest" href="/manifest.json">

        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Infak Madrasah Assajjad</title>

        <!-- Typography: Onest & JetBrains Mono -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;600;700&family=Onest:wght@400;500;600;700;800&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-[#1C1917] bg-[#F5F3EE] selection:bg-[#A8551E]/15 selection:text-[#7C3A10]">
        <div id="app"></div>
        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('/sw.js', { scope: '/' })
                        .catch(() => {});
                });
            }
        </script>
    </body>
</html>
