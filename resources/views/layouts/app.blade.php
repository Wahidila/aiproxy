<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>[x-cloak] { display: none !important; }</style>

        <!-- Lucide Icons -->
        <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    </head>
    <body class="font-sans antialiased text-off-black" onload="lucide.createIcons()">
        <div class="min-h-screen bg-canvas">
            @include('layouts.navigation')

            <!-- Broadcast Notifications -->
            @include('partials.broadcast-notifications')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-surface border-b border-oat">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>

            <!-- Footer -->
            <footer class="border-t border-oat bg-surface mt-12">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-gray-500">
                    <p>&copy; {{ date('Y') }} AIMurah. All rights reserved.</p>
                    <div class="flex flex-wrap gap-4">
                        <a href="{{ route('legal.terms') }}" class="hover:text-gray-700 transition-colors">Ketentuan Layanan</a>
                        <a href="{{ route('legal.privacy') }}" class="hover:text-gray-700 transition-colors">Kebijakan Privasi</a>
                        <a href="{{ route('legal.donation') }}" class="hover:text-gray-700 transition-colors">Ketentuan Donasi</a>
                    </div>
                </div>
            </footer>
        </div>
        <!-- Telegram Floating Bubble -->
        <a href="https://t.me/+P1jSmpeMhmcyMTQ9" target="_blank" rel="noopener noreferrer" aria-label="Join Telegram Group" class="fixed bottom-6 right-6 z-50 w-14 h-14 bg-[#2AABEE] rounded-full flex items-center justify-center shadow-lg hover:scale-110 hover:shadow-xl active:scale-95 transition-all duration-300 group">
            <svg class="w-7 h-7 fill-white" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12.056 0h-.112Zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.479.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635Z"/></svg>
            <span class="absolute right-16 bottom-1/2 translate-y-1/2 bg-white text-gray-800 text-xs font-medium px-3 py-2 rounded-md shadow-md whitespace-nowrap opacity-0 pointer-events-none group-hover:opacity-100 transition-opacity duration-200 border border-gray-200 max-sm:hidden">Join Telegram Group</span>
        </a>

        @stack('scripts')
    </body>
</html>
