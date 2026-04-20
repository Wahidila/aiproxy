<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'AI Token Dashboard') }} - Akses AI Premium</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/" class="font-bold text-xl text-indigo-600">AI Token</a>
                </div>
                <div class="flex items-center space-x-4">
                    @auth
                        <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-indigo-600 font-medium">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-indigo-600 font-medium">Login</a>
                        <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition">Daftar Gratis</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-800"></div>
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%23ffffff&quot; fill-opacity=&quot;0.4&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')"></div>
        </div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 sm:py-32">
            <div class="text-center">
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white tracking-tight">
                    Akses AI Premium
                    <span class="block text-indigo-200">Harga Terjangkau</span>
                </h1>
                <p class="mt-6 max-w-2xl mx-auto text-xl text-indigo-100">
                    Gunakan model AI terbaik dunia - Claude Opus 4.6, GPT-5, Gemini Pro - langsung dari Cursor, VS Code, atau tool favorit Anda.
                </p>
                <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-8 py-3 text-lg font-semibold rounded-lg text-indigo-700 bg-white hover:bg-indigo-50 transition shadow-lg">
                        Mulai Gratis - 1M Token
                    </a>
                    <a href="#pricing" class="inline-flex items-center justify-center px-8 py-3 text-lg font-semibold rounded-lg text-white border-2 border-white/30 hover:bg-white/10 transition">
                        Lihat Harga
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-gray-900">Cara Kerja</h2>
                <p class="mt-4 text-lg text-gray-600">3 langkah mudah untuk mulai menggunakan AI premium</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Step 1 -->
                <div class="text-center p-8 rounded-xl bg-gray-50">
                    <div class="w-16 h-16 mx-auto bg-indigo-100 rounded-full flex items-center justify-center mb-6">
                        <span class="text-2xl font-bold text-indigo-600">1</span>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Daftar & Dapatkan API Key</h3>
                    <p class="text-gray-600">Buat akun gratis, lalu generate API key dari dashboard. Langsung dapat 1M token gratis per bulan.</p>
                </div>
                <!-- Step 2 -->
                <div class="text-center p-8 rounded-xl bg-gray-50">
                    <div class="w-16 h-16 mx-auto bg-indigo-100 rounded-full flex items-center justify-center mb-6">
                        <span class="text-2xl font-bold text-indigo-600">2</span>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Pasang di Tool Anda</h3>
                    <p class="text-gray-600">Set Base URL dan API Key di Cursor, VS Code, Cline, atau tool OpenAI-compatible lainnya.</p>
                </div>
                <!-- Step 3 -->
                <div class="text-center p-8 rounded-xl bg-gray-50">
                    <div class="w-16 h-16 mx-auto bg-indigo-100 rounded-full flex items-center justify-center mb-6">
                        <span class="text-2xl font-bold text-indigo-600">3</span>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Mulai Coding dengan AI</h3>
                    <p class="text-gray-600">Gunakan Claude Opus 4.6, GPT-5, Gemini, dan 30+ model AI lainnya untuk coding, writing, dan analisis.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Supported Models -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-gray-900">30+ Model AI Premium</h2>
                <p class="mt-4 text-lg text-gray-600">Akses model terbaik dari berbagai provider dalam satu API</p>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                @php
                $models = [
                    ['name' => 'Claude Opus 4.6', 'tier' => 'MAX', 'provider' => 'Anthropic'],
                    ['name' => 'Claude Sonnet 4.5', 'tier' => 'Standard', 'provider' => 'Anthropic'],
                    ['name' => 'Claude Sonnet 4', 'tier' => 'Standard', 'provider' => 'Anthropic'],
                    ['name' => 'GPT-5.4', 'tier' => 'MAX', 'provider' => 'OpenAI'],
                    ['name' => 'GPT-5.2', 'tier' => 'MAX', 'provider' => 'OpenAI'],
                    ['name' => 'Gemini 2.5 Pro', 'tier' => 'MAX', 'provider' => 'Google'],
                    ['name' => 'Gemini 2.5 Flash', 'tier' => 'MAX', 'provider' => 'Google'],
                    ['name' => 'DeepSeek 3.2', 'tier' => 'Standard', 'provider' => 'DeepSeek'],
                    ['name' => 'Kimi K2.5', 'tier' => 'MAX', 'provider' => 'Moonshot'],
                    ['name' => 'Claude Haiku 4.5', 'tier' => 'Standard', 'provider' => 'Anthropic'],
                    ['name' => 'GPT-5.3 Codex', 'tier' => 'MAX', 'provider' => 'OpenAI'],
                    ['name' => 'Gemini 3.1 Pro', 'tier' => 'MAX', 'provider' => 'Google'],
                ];
                @endphp
                @foreach($models as $model)
                <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $model['tier'] === 'MAX' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                            {{ $model['tier'] }}
                        </span>
                    </div>
                    <h4 class="font-semibold text-gray-900 text-sm">{{ $model['name'] }}</h4>
                    <p class="text-xs text-gray-500 mt-1">{{ $model['provider'] }}</p>
                </div>
                @endforeach
            </div>
            <p class="text-center mt-8 text-gray-500">Dan masih banyak lagi...</p>
        </div>
    </section>

    <!-- Pricing -->
    <section id="pricing" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-gray-900">Harga Sederhana</h2>
                <p class="mt-4 text-lg text-gray-600">Mulai gratis, upgrade kapan saja</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
                <!-- Free Tier -->
                <div class="rounded-2xl border-2 border-gray-200 p-8">
                    <h3 class="text-2xl font-bold text-gray-900">Free</h3>
                    <p class="mt-2 text-gray-600">Untuk mencoba dan penggunaan ringan</p>
                    <div class="mt-6">
                        <span class="text-4xl font-extrabold text-gray-900">Rp 0</span>
                        <span class="text-gray-500">/bulan</span>
                    </div>
                    <ul class="mt-8 space-y-4">
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            <span class="text-gray-700"><strong>1M token</strong> per bulan</span>
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            <span class="text-gray-700">Semua model AI (termasuk Opus 4.6)</span>
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            <span class="text-gray-700">OpenAI-compatible API</span>
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            <span class="text-gray-700">Dashboard & statistik</span>
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            <span class="text-gray-700">Reset otomatis setiap bulan</span>
                        </li>
                    </ul>
                    <a href="{{ route('register') }}" class="mt-8 block w-full text-center px-6 py-3 rounded-lg border-2 border-indigo-600 text-indigo-600 font-semibold hover:bg-indigo-50 transition">
                        Daftar Gratis
                    </a>
                </div>

                <!-- Donasi Tier -->
                <div class="rounded-2xl border-2 border-indigo-600 p-8 relative shadow-lg">
                    <div class="absolute -top-4 left-1/2 -translate-x-1/2">
                        <span class="bg-indigo-600 text-white text-sm font-semibold px-4 py-1 rounded-full">Populer</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900">Donasi</h3>
                    <p class="mt-2 text-gray-600">Untuk penggunaan intensif</p>
                    <div class="mt-6">
                        <span class="text-4xl font-extrabold text-gray-900">Rp 20.000</span>
                        <span class="text-gray-500">/hari</span>
                    </div>
                    <ul class="mt-8 space-y-4">
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            <span class="text-gray-700"><strong>10M token</strong> untuk 24 jam</span>
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            <span class="text-gray-700">Semua model AI (termasuk Opus 4.6)</span>
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            <span class="text-gray-700">Bayar via QRIS (semua e-wallet & bank)</span>
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            <span class="text-gray-700">Pay-as-you-go, beli kapan butuh</span>
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            <span class="text-gray-700">Aktivasi cepat oleh admin</span>
                        </li>
                    </ul>
                    <a href="{{ route('register') }}" class="mt-8 block w-full text-center px-6 py-3 rounded-lg bg-indigo-600 text-white font-semibold hover:bg-indigo-700 transition shadow-lg">
                        Mulai Sekarang
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Compatible Tools -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900">Kompatibel dengan Tool Favorit Anda</h2>
                <p class="mt-4 text-lg text-gray-600">Drop-in replacement untuk OpenAI API. Tinggal ganti Base URL dan API Key.</p>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-6 max-w-3xl mx-auto">
                @php
                $tools = ['Cursor', 'VS Code', 'Cline', 'Continue', 'Windsurf', 'Kilo Code', 'OpenCode', 'Any OpenAI Client'];
                @endphp
                @foreach($tools as $tool)
                <div class="bg-white rounded-lg p-4 text-center shadow-sm border border-gray-100">
                    <p class="font-medium text-gray-800">{{ $tool }}</p>
                </div>
                @endforeach
            </div>
            <div class="mt-12 max-w-2xl mx-auto bg-gray-900 rounded-xl p-6 text-sm">
                <p class="text-gray-400 mb-2"># Setup di Cursor / VS Code</p>
                <p class="text-green-400">Base URL: <span class="text-white">{{ url('/api/v1') }}</span></p>
                <p class="text-green-400">API Key: <span class="text-white">sk-your-api-key-here</span></p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-400 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0">
                    <span class="font-bold text-xl text-white">AI Token</span>
                    <p class="mt-2 text-sm">Akses AI Premium, Harga Terjangkau</p>
                </div>
                <div class="flex space-x-6 text-sm">
                    <a href="{{ route('login') }}" class="hover:text-white transition">Login</a>
                    <a href="{{ route('register') }}" class="hover:text-white transition">Register</a>
                    <a href="#pricing" class="hover:text-white transition">Pricing</a>
                </div>
            </div>
            <div class="mt-8 pt-8 border-t border-gray-800 text-center text-sm">
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </div>
        </div>
    </footer>
</body>
</html>
