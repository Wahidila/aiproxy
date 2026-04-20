<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-off-black">
            API Keys
        </h2>
    </x-slot>

    @php
        if (!function_exists('apiKeysFormatRp')) {
            function apiKeysFormatRp($v) { return 'Rp ' . number_format($v, 0, ',', '.'); }
        }
        $exchangeRate = (float) \App\Models\Setting::get('usd_to_idr_rate', 16500);
    @endphp

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

            {{-- ============================================================ --}}
            {{-- PANDUAN SETUP CUSTOM PROVIDER --}}
            {{-- ============================================================ --}}
            <div class="bg-surface border border-oat rounded-card" x-data="{ openGuide: 'kilo' }">
                <div class="px-6 py-5">
                    <h3 class="text-lg font-semibold text-off-black tracking-sub mb-1">Panduan Setup Custom Provider</h3>
                    <p class="text-sm text-muted mb-4">Gunakan Base URL dan API Key di bawah untuk menghubungkan tool Anda.</p>

                    {{-- Base URL + API Key info --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-5">
                        <div x-data="{ copied: false }">
                            <label class="block text-xs font-medium text-muted uppercase mb-1">Base URL</label>
                            <div class="flex items-center gap-2">
                                <code class="flex-1 rounded-btn border border-oat bg-canvas px-3 py-2 text-sm font-mono text-off-black truncate">{{ $baseUrl }}</code>
                                <button @click="navigator.clipboard.writeText('{{ $baseUrl }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                        class="flex-shrink-0 inline-flex items-center rounded-btn border border-oat bg-surface px-2.5 py-2 text-xs font-medium text-muted hover:bg-canvas transition">
                                    <span x-show="!copied"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg></span>
                                    <span x-show="copied" x-cloak class="text-green-600">Copied!</span>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-muted uppercase mb-1">API Key</label>
                            <div class="rounded-btn border border-oat bg-canvas px-3 py-2 text-sm text-muted">
                                Buat API key di bawah, lalu copy dan paste ke tool Anda
                            </div>
                        </div>
                    </div>

                    {{-- Tab buttons --}}
                    <div class="flex flex-wrap gap-1 border-b border-oat mb-4">
                        @foreach(['kilo' => 'Kilo Code', 'cursor' => 'Cursor', 'vscode' => 'VS Code (Continue)', 'curl' => 'cURL / API'] as $tab => $label)
                            <button @click="openGuide = '{{ $tab }}'"
                                    :class="openGuide === '{{ $tab }}' ? 'border-fin-orange text-fin-orange' : 'border-transparent text-muted hover:text-off-black hover:border-oat'"
                                    class="px-4 py-2 text-sm font-medium border-b-2 transition whitespace-nowrap">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>

                    {{-- Kilo Code Guide --}}
                    <div x-show="openGuide === 'kilo'" x-cloak>
                        <div class="rounded-lg bg-fin-orange-light p-4 space-y-2">
                            <h4 class="text-sm font-semibold text-off-black">Setup di Kilo Code</h4>
                            <ol class="list-decimal list-inside space-y-1.5 text-sm text-off-black">
                                <li>Buka Kilo Code, tekan <kbd class="px-1.5 py-0.5 bg-white rounded text-xs font-mono border">Ctrl+P</kbd> lalu pilih <strong>Providers: Manage Providers</strong></li>
                                <li>Klik <strong>Add Custom Provider</strong></li>
                                <li>Isi <strong>Name</strong>: bebas (misal "AI Token")</li>
                                <li>Isi <strong>Base URL</strong>: <code class="bg-white px-1.5 py-0.5 rounded text-xs font-mono border">{{ $baseUrl }}</code></li>
                                <li>Isi <strong>API Key</strong>: paste API key yang Anda buat di bawah</li>
                                <li>Klik <strong>Save</strong></li>
                                <li>Pilih provider baru, lalu ketik <strong>Model ID</strong> dari daftar model di bawah (misal: <code class="bg-white px-1.5 py-0.5 rounded text-xs font-mono border">claude-sonnet-4.5</code>)</li>
                            </ol>
                        </div>
                    </div>

                    {{-- Cursor Guide --}}
                    <div x-show="openGuide === 'cursor'" x-cloak>
                        <div class="rounded-lg bg-purple-50 p-4 space-y-2">
                            <h4 class="text-sm font-semibold text-purple-900">Setup di Cursor</h4>
                            <ol class="list-decimal list-inside space-y-1.5 text-sm text-purple-800">
                                <li>Buka <strong>Settings</strong> > <strong>Models</strong></li>
                                <li>Klik <strong>+ Add Model</strong></li>
                                <li>Pilih <strong>OpenAI Compatible</strong></li>
                                <li>Isi <strong>Base URL</strong>: <code class="bg-white px-1.5 py-0.5 rounded text-xs font-mono border">{{ $baseUrl }}</code></li>
                                <li>Isi <strong>API Key</strong>: paste API key Anda</li>
                                <li>Isi <strong>Model Name</strong>: ketik Model ID dari daftar di bawah</li>
                                <li>Klik <strong>Save</strong> dan pilih model tersebut untuk digunakan</li>
                            </ol>
                        </div>
                    </div>

                    {{-- VS Code (Continue) Guide --}}
                    <div x-show="openGuide === 'vscode'" x-cloak>
                        <div class="rounded-lg bg-blue-50 p-4 space-y-2">
                            <h4 class="text-sm font-semibold text-blue-900">Setup di VS Code (Continue Extension)</h4>
                            <ol class="list-decimal list-inside space-y-1.5 text-sm text-blue-800">
                                <li>Install extension <strong>Continue</strong> di VS Code</li>
                                <li>Buka <code class="bg-white px-1.5 py-0.5 rounded text-xs font-mono border">~/.continue/config.json</code></li>
                                <li>Tambahkan provider baru di array <code>models</code>:</li>
                            </ol>
                            <div class="mt-2 relative" x-data="{ copied: false }">
                                <pre class="bg-gray-900 text-gray-100 rounded-lg p-4 text-xs font-mono overflow-x-auto">{
  "models": [{
    "title": "AI Token",
    "provider": "openai",
    "model": "<span class="text-yellow-300">MODEL_ID_DISINI</span>",
    "apiBase": "<span class="text-green-300">{{ $baseUrl }}</span>",
    "apiKey": "<span class="text-green-300">API_KEY_ANDA</span>"
  }]
}</pre>
                                <button @click="navigator.clipboard.writeText(JSON.stringify({models:[{title:'AI Token',provider:'openai',model:'claude-sonnet-4.5',apiBase:'{{ $baseUrl }}',apiKey:'YOUR_API_KEY'}]},null,2)); copied=true; setTimeout(()=>copied=false,2000)"
                                        class="absolute top-2 right-2 px-2 py-1 bg-gray-700 text-gray-300 text-xs rounded hover:bg-gray-600 transition">
                                    <span x-show="!copied">Copy</span>
                                    <span x-show="copied" x-cloak class="text-green-400">Copied!</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- cURL Guide --}}
                    <div x-show="openGuide === 'curl'" x-cloak>
                        <div class="rounded-lg bg-canvas p-4 space-y-2">
                            <h4 class="text-sm font-semibold text-off-black">Menggunakan API langsung (cURL / HTTP)</h4>
                            <p class="text-sm text-muted">API ini kompatibel dengan format OpenAI. Contoh request:</p>
                            <div class="mt-2 relative" x-data="{ copied: false }">
                                <pre class="bg-gray-900 text-gray-100 rounded-lg p-4 text-xs font-mono overflow-x-auto">curl -X POST {{ $baseUrl }}/chat/completions \
  -H "Authorization: Bearer <span class="text-green-300">API_KEY_ANDA</span>" \
  -H "Content-Type: application/json" \
  -d '{
    "model": "<span class="text-yellow-300">claude-sonnet-4.5</span>",
    "messages": [{"role": "user", "content": "Hello!"}],
    "max_tokens": 1000
  }'</pre>
                                <button @click="navigator.clipboard.writeText(`curl -X POST {{ $baseUrl }}/chat/completions \\\n  -H \"Authorization: Bearer YOUR_API_KEY\" \\\n  -H \"Content-Type: application/json\" \\\n  -d '{\"model\":\"claude-sonnet-4.5\",\"messages\":[{\"role\":\"user\",\"content\":\"Hello!\"}],\"max_tokens\":1000}'`); copied=true; setTimeout(()=>copied=false,2000)"
                                        class="absolute top-2 right-2 px-2 py-1 bg-gray-700 text-gray-300 text-xs rounded hover:bg-gray-600 transition">
                                    <span x-show="!copied">Copy</span>
                                    <span x-show="copied" x-cloak class="text-green-400">Copied!</span>
                                </button>
                            </div>
                            <div class="mt-3 text-xs text-muted">
                                <p><strong>Endpoints:</strong></p>
                                <ul class="list-disc list-inside mt-1 space-y-0.5">
                                    <li><code>POST /chat/completions</code> - Chat (OpenAI format)</li>
                                    <li><code>POST /messages</code> - Chat (Anthropic format)</li>
                                    <li><code>GET /models</code> - List available models</li>
                                    <li><code>GET /health</code> - Health check</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ============================================================ --}}
            {{-- MODEL YANG TERSEDIA --}}
            {{-- ============================================================ --}}
            <div class="bg-surface border border-oat rounded-card" x-data="{ showAll: false }">
                <div class="px-6 py-5">
                    <h3 class="text-lg font-semibold text-off-black tracking-sub mb-1">Model yang Tersedia</h3>
                    <p class="text-sm text-muted mb-4">Copy <strong>Model ID</strong> dan paste di konfigurasi tool Anda. Harga per 1M token (setelah diskon).</p>

                    {{-- Free Tier Models --}}
                    <div class="mb-5">
                        <h4 class="text-sm font-semibold text-green-700 mb-2 flex items-center gap-1.5">
                            <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">Free Tier</span>
                            Tersedia untuk API key Free & Paid
                        </h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-oat text-sm">
                                <thead class="bg-green-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-muted uppercase">Model ID</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-muted uppercase">Nama</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-muted uppercase">Input /1M</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-muted uppercase">Output /1M</th>
                                        <th class="px-3 py-2 text-center text-xs font-medium text-muted uppercase">Diskon</th>
                                        <th class="px-3 py-2 text-center text-xs font-medium text-muted uppercase w-16"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-oat">
                                    @foreach($freeModels as $model)
                                        @php
                                            $disc = 1 - ($model->discount_percent / 100);
                                            $inIdr = $model->input_price_usd * $exchangeRate * $disc;
                                            $outIdr = $model->output_price_usd * $exchangeRate * $disc;
                                        @endphp
                                        <tr class="hover:bg-green-50/50" x-data="{ copied: false }">
                                            <td class="px-3 py-2 font-mono text-off-black font-medium">{{ $model->model_id }}</td>
                                            <td class="px-3 py-2 text-muted">{{ $model->model_name }}</td>
                                            <td class="px-3 py-2 text-right text-off-black">{{ apiKeysFormatRp($inIdr) }}</td>
                                            <td class="px-3 py-2 text-right text-off-black">{{ apiKeysFormatRp($outIdr) }}</td>
                                            <td class="px-3 py-2 text-center">
                                                @if($model->discount_percent > 0)
                                                    <span class="text-green-600 font-medium">-{{ $model->discount_percent }}%</span>
                                                @else
                                                    <span class="text-warm-sand">-</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2 text-center">
                                                <button @click="navigator.clipboard.writeText('{{ $model->model_id }}'); copied = true; setTimeout(() => copied = false, 1500)"
                                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded border transition"
                                                        :class="copied ? 'bg-green-100 border-green-300 text-green-700' : 'bg-surface border-oat text-muted hover:bg-canvas'">
                                                    <span x-show="!copied">Copy ID</span>
                                                    <span x-show="copied" x-cloak>Copied!</span>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Paid Models (collapsible) --}}
                    <div>
                        <button @click="showAll = !showAll" class="flex items-center gap-2 text-sm font-semibold text-fin-orange mb-2 hover:text-off-black transition">
                            <span class="inline-flex items-center rounded-full bg-fin-orange-light px-2 py-0.5 text-xs font-medium text-fin-orange">Premium</span>
                            Model Premium (hanya API key Paid)
                            <svg :class="showAll ? 'rotate-180' : ''" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="showAll" x-cloak x-collapse>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-oat text-sm">
                                    <thead class="bg-fin-orange-light">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-muted uppercase">Model ID</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-muted uppercase">Nama</th>
                                            <th class="px-3 py-2 text-right text-xs font-medium text-muted uppercase">Input /1M</th>
                                            <th class="px-3 py-2 text-right text-xs font-medium text-muted uppercase">Output /1M</th>
                                            <th class="px-3 py-2 text-center text-xs font-medium text-muted uppercase">Diskon</th>
                                            <th class="px-3 py-2 text-center text-xs font-medium text-muted uppercase w-16"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-oat">
                                        @foreach($paidModels as $model)
                                            @php
                                                $disc = 1 - ($model->discount_percent / 100);
                                                $inIdr = $model->input_price_usd * $exchangeRate * $disc;
                                                $outIdr = $model->output_price_usd * $exchangeRate * $disc;
                                            @endphp
                                            <tr class="hover:bg-fin-orange-light/50" x-data="{ copied: false }">
                                                <td class="px-3 py-2 font-mono text-off-black font-medium">{{ $model->model_id }}</td>
                                                <td class="px-3 py-2 text-muted">{{ $model->model_name }}</td>
                                                <td class="px-3 py-2 text-right text-off-black">{{ apiKeysFormatRp($inIdr) }}</td>
                                                <td class="px-3 py-2 text-right text-off-black">{{ apiKeysFormatRp($outIdr) }}</td>
                                                <td class="px-3 py-2 text-center">
                                                    @if($model->discount_percent > 0)
                                                        <span class="text-green-600 font-medium">-{{ $model->discount_percent }}%</span>
                                                    @else
                                                        <span class="text-warm-sand">-</span>
                                                    @endif
                                                </td>
                                                <td class="px-3 py-2 text-center">
                                                    <button @click="navigator.clipboard.writeText('{{ $model->model_id }}'); copied = true; setTimeout(() => copied = false, 1500)"
                                                            class="inline-flex items-center px-2 py-1 text-xs font-medium rounded border transition"
                                                            :class="copied ? 'bg-green-100 border-green-300 text-green-700' : 'bg-surface border-oat text-muted hover:bg-canvas'">
                                                        <span x-show="!copied">Copy ID</span>
                                                        <span x-show="copied" x-cloak>Copied!</span>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ============================================================ --}}
            {{-- NEW KEY FLASH MESSAGE --}}
            {{-- ============================================================ --}}
            @if(session('new_key'))
                <div class="rounded-lg border border-green-300 bg-green-50 p-4" x-data="{ copied: false }">
                    <div class="flex items-start">
                        <svg class="h-5 w-5 text-green-500 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-green-800 mb-2">API Key berhasil dibuat!</p>
                            <div class="flex items-center gap-2 mb-2">
                                <code class="flex-1 rounded-btn border border-green-200 bg-white px-3 py-2 text-sm font-mono text-off-black break-all">{{ session('new_key') }}</code>
                                <button @click="navigator.clipboard.writeText('{{ session('new_key') }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                        class="inline-flex items-center rounded-btn bg-green-600 px-3 py-2 text-sm font-medium text-white hover:bg-green-700 transition-colors">
                                    <span x-show="!copied">Copy</span>
                                    <span x-show="copied" x-cloak>Copied!</span>
                                </button>
                            </div>
                            <p class="text-xs font-medium text-red-600">
                                <svg class="inline h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                Simpan API key ini! Tidak akan ditampilkan lagi.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="rounded-lg border border-green-300 bg-green-50 p-4">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            @endif
            @if(session('error'))
                <div class="rounded-lg border border-red-300 bg-red-50 p-4">
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
            @endif

            {{-- ============================================================ --}}
            {{-- BALANCE INFO --}}
            {{-- ============================================================ --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-surface border border-oat rounded-card p-5">
                    <p class="text-xs font-medium text-muted uppercase">Saldo Free Trial</p>
                    <p class="mt-1 text-2xl font-bold {{ $quota->free_balance > 0 ? 'text-green-600' : 'text-red-500' }}">{{ $quota->formatted_free_balance }}</p>
                    <p class="mt-1 text-xs text-warm-sand">Hanya untuk model free tier</p>
                </div>
                <div class="bg-surface border border-oat rounded-card p-5">
                    <p class="text-xs font-medium text-muted uppercase">Saldo Top Up</p>
                    <p class="mt-1 text-2xl font-bold {{ $quota->paid_balance > 0 ? 'text-fin-orange' : 'text-red-500' }}">{{ $quota->formatted_paid_balance }}</p>
                    <p class="mt-1 text-xs text-warm-sand">Untuk semua model (termasuk premium)</p>
                </div>
            </div>

            {{-- ============================================================ --}}
            {{-- CREATE NEW API KEY --}}
            {{-- ============================================================ --}}
            <div class="bg-surface border border-oat rounded-card">
                <div class="px-6 py-5">
                    <h3 class="text-lg font-semibold text-off-black tracking-sub mb-3">Buat API Key Baru</h3>

                    <form method="POST" action="{{ route('api-keys.store') }}" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-off-black mb-1">Nama</label>
                                <input type="text" name="name" id="name" required
                                    placeholder="Contoh: Cursor IDE, Kilo Code, dll"
                                    class="block w-full rounded-btn border-oat focus:border-fin-orange focus:ring-fin-orange sm:text-sm"
                                    value="{{ old('name') }}">
                                @error('name')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-off-black mb-1">Tipe Saldo</label>
                                <div class="flex gap-3 mt-1" x-data="{ tier: '{{ old('tier', 'free') }}' }">
                                    <label class="flex items-center gap-2 cursor-pointer rounded-lg border-2 px-4 py-2.5 transition flex-1"
                                           :class="tier === 'free' ? 'border-green-500 bg-green-50' : '{{ $quota->free_balance > 0 ? "border-oat hover:border-green-300" : "border-oat opacity-50" }}'">
                                        <input type="radio" name="tier" value="free" x-model="tier"
                                            class="text-green-600 focus:ring-green-500"
                                            {{ $quota->free_balance <= 0 ? 'disabled' : '' }}>
                                        <div>
                                            <span class="text-sm font-semibold text-off-black">Free Tier</span>
                                            <p class="text-xs text-muted">Model terbatas</p>
                                        </div>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer rounded-lg border-2 px-4 py-2.5 transition flex-1"
                                           :class="tier === 'paid' ? 'border-fin-orange bg-fin-orange-light' : '{{ $quota->paid_balance > 0 ? "border-oat hover:border-fin-orange/50" : "border-oat opacity-50" }}'">
                                        <input type="radio" name="tier" value="paid" x-model="tier"
                                            class="text-fin-orange focus:ring-fin-orange"
                                            {{ $quota->paid_balance <= 0 ? 'disabled' : '' }}>
                                        <div>
                                            <span class="text-sm font-semibold text-off-black">Paid</span>
                                            <p class="text-xs text-muted">Semua model</p>
                                        </div>
                                    </label>
                                </div>
                                @error('tier')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div>
                            <button type="submit"
                                class="inline-flex items-center rounded-btn bg-off-black px-4 py-2 text-sm font-medium text-white hover:bg-off-black/90 btn-intercom transition-colors">
                                <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Buat Key
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ============================================================ --}}
            {{-- API KEYS TABLE --}}
            {{-- ============================================================ --}}
            <div class="bg-surface border border-oat rounded-card">
                <div class="px-6 py-5">
                    <h3 class="text-lg font-semibold text-off-black tracking-sub mb-4">API Keys Anda</h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-oat">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted">Nama</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted">Key</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-muted">Tier</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-muted">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted">Terakhir Digunakan</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-muted">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-oat">
                                @forelse($apiKeys as $key)
                                    <tr class="hover:bg-canvas">
                                        <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-off-black">
                                            {{ $key->name }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3">
                                            <code class="text-sm font-mono text-muted">{{ $key->masked_key }}</code>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-center">
                                            @if($key->isFree())
                                                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700">Free</span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-fin-orange-light px-2.5 py-0.5 text-xs font-medium text-fin-orange">Paid</span>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-center">
                                            @if($key->is_active)
                                                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Active</span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-canvas px-2.5 py-0.5 text-xs font-medium text-muted">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-sm text-muted">
                                            {{ $key->last_used_at ? $key->last_used_at->diffForHumans() : 'Belum pernah' }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <form method="POST" action="{{ route('api-keys.toggle', $key) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit"
                                                            class="inline-flex items-center rounded-btn px-2.5 py-1.5 text-xs font-medium transition-colors
                                                                   {{ $key->is_active
                                                                       ? 'border border-yellow-300 bg-yellow-50 text-yellow-700 hover:bg-yellow-100'
                                                                       : 'border border-green-300 bg-green-50 text-green-700 hover:bg-green-100' }}">
                                                        {{ $key->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('api-keys.destroy', $key) }}"
                                                      x-data @submit.prevent="if(confirm('Yakin ingin menghapus API key ini?')) $el.submit()">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="inline-flex items-center rounded-btn border border-red-300 bg-red-50 px-2.5 py-1.5 text-xs font-medium text-red-700 hover:bg-red-100 transition-colors">
                                                        Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-sm text-warm-sand">
                                            Belum ada API key. Buat satu di atas untuk mulai menggunakan layanan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
