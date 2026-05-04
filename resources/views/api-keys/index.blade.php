<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-off-black">
                API Keys
            </h2>
            <button onclick="window.dispatchEvent(new CustomEvent('open-create-key'))"
                    class="inline-flex items-center gap-1.5 rounded-btn bg-off-black px-4 py-2 text-sm font-medium text-white btn-intercom transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Buat Key Baru
            </button>
        </div>
    </x-slot>

    @php
        if (!function_exists('apiKeysFormatRp')) {
            function apiKeysFormatRp($v) { return 'Rp ' . number_format($v, 0, ',', '.'); }
        }
        $exchangeRate = (float) \App\Models\Setting::get('usd_to_idr_rate', 16500);
    @endphp

    <div class="py-6" x-data="{
        showCreateForm: false,
        showGuide: false,
        showModels: false,
        openGuide: 'kilo',
        tier: '{{ old('tier', 'paid') }}'
    }" @open-create-key.window="showCreateForm = true; $nextTick(() => $refs.nameInput?.focus())">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-5">

            {{-- ============================================================ --}}
            {{-- NEW KEY FLASH MESSAGE --}}
            {{-- ============================================================ --}}
            @if(session('new_key'))
                <div class="rounded-card border border-green-300 bg-green-50 p-4" x-data="{ copied: false }">
                    <div class="flex items-start gap-3">
                        <svg class="h-5 w-5 text-green-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-green-800 mb-2">API Key berhasil dibuat! Simpan sekarang.</p>
                            <div class="flex items-center gap-2 mb-2">
                                <code class="flex-1 rounded-btn border border-green-200 bg-white px-3 py-2 text-sm font-mono text-off-black break-all select-all">{{ session('new_key') }}</code>
                                <button @click="navigator.clipboard.writeText('{{ session('new_key') }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                        class="flex-shrink-0 inline-flex items-center rounded-btn bg-green-600 px-3 py-2 text-sm font-medium text-white hover:bg-green-700 btn-intercom transition-colors">
                                    <span x-show="!copied">Copy</span>
                                    <span x-show="copied" x-cloak>Copied!</span>
                                </button>
                            </div>
                            <p class="text-xs font-medium text-red-600">
                                <svg class="inline h-3.5 w-3.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                Key ini tidak akan ditampilkan lagi setelah Anda meninggalkan halaman ini.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Flash Messages --}}
            @if(session('success') && !session('new_key'))
                <div class="rounded-card border border-green-300 bg-green-50 p-4">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            @endif
            @if(session('error'))
                <div class="rounded-card border border-red-300 bg-red-50 p-4">
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
            @endif

            {{-- ============================================================ --}}
            {{-- BASE URL (always visible, compact) --}}
            {{-- ============================================================ --}}
            <div class="flex flex-col sm:flex-row sm:items-center gap-2 bg-surface border border-oat rounded-card px-4 py-3" x-data="{ copied: false }">
                <span class="text-xs font-medium text-muted uppercase tracking-wide flex-shrink-0">Base URL</span>
                <code class="flex-1 text-sm font-mono text-off-black truncate">{{ $baseUrl }}</code>
                <button @click="navigator.clipboard.writeText('{{ $baseUrl }}'); copied = true; setTimeout(() => copied = false, 2000)"
                        class="flex-shrink-0 inline-flex items-center gap-1 rounded-btn border border-oat bg-canvas px-2.5 py-1.5 text-xs font-medium text-muted hover:text-off-black hover:border-off-black/30 btn-intercom transition-colors">
                    <svg x-show="!copied" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    <span x-show="!copied">Copy</span>
                    <span x-show="copied" x-cloak class="text-green-600">Copied!</span>
                </button>
            </div>

            {{-- ============================================================ --}}
            {{-- CREATE KEY FORM (inline, collapsible) --}}
            {{-- ============================================================ --}}
            <div x-show="showCreateForm" x-cloak x-collapse>
                <div class="bg-surface border border-oat rounded-card">
                    <div class="px-5 py-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-base font-semibold text-off-black tracking-sub">Buat API Key Baru</h3>
                            <button @click="showCreateForm = false" class="text-muted hover:text-off-black transition-colors p-1">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <form method="POST" action="{{ route('api-keys.store') }}">
                            @csrf
                            <div class="flex flex-col sm:flex-row gap-3">
                                <div class="flex-1">
                                    <label for="name" class="sr-only">Nama Key</label>
                                    <input type="text" name="name" id="name" required x-ref="nameInput"
                                        placeholder="Nama key (misal: Cursor IDE, Kilo Code)"
                                        class="block w-full rounded-btn border-oat focus:border-fin-orange focus:ring-fin-orange text-sm"
                                        value="{{ old('name') }}">
                                    @error('name')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="flex gap-2 flex-shrink-0">
                                    <label class="flex items-center gap-1.5 cursor-pointer rounded-btn border-2 px-3 py-2 transition text-sm"
                                           :class="tier === 'paid' ? 'border-fin-orange bg-fin-orange-light text-off-black font-medium' : 'border-oat text-muted hover:border-off-black/30'">
                                        <input type="radio" name="tier" value="paid" x-model="tier" class="sr-only">
                                        <span>Paid</span>
                                    </label>
                                    @if($subscriptionEnabled)
                                        <label class="flex items-center gap-1.5 cursor-pointer rounded-btn border-2 px-3 py-2 transition text-sm
                                                      {{ (!$activeSubscription || !$activeSubscription->isActive()) ? 'opacity-40 cursor-not-allowed' : '' }}"
                                               :class="tier === 'subscription' ? 'border-purple-500 bg-purple-50 text-off-black font-medium' : 'border-oat text-muted hover:border-off-black/30'">
                                            <input type="radio" name="tier" value="subscription" x-model="tier" class="sr-only"
                                                {{ (!$activeSubscription || !$activeSubscription->isActive()) ? 'disabled' : '' }}>
                                            <span>Subscription</span>
                                        </label>
                                    @endif
                                </div>
                                <button type="submit"
                                    class="inline-flex items-center justify-center rounded-btn bg-off-black px-5 py-2 text-sm font-medium text-white btn-intercom transition-colors flex-shrink-0">
                                    Buat Key
                                </button>
                            </div>
                            @error('tier')
                                <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </form>
                    </div>
                </div>
            </div>

            {{-- ============================================================ --}}
            {{-- API KEYS LIST --}}
            {{-- ============================================================ --}}
            @if($apiKeys->count() > 0)
                <div class="space-y-3">
                    <h3 class="text-sm font-medium text-muted uppercase tracking-wide">{{ $apiKeys->count() }} Key{{ $apiKeys->count() > 1 ? 's' : '' }}</h3>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                        @foreach($apiKeys as $key)
                            <div class="bg-surface border border-oat rounded-card p-4 hover:border-off-black/20 transition-colors" x-data="{ copied: false }">
                                <div class="flex items-start justify-between gap-3 mb-3">
                                    <div class="flex items-center gap-2 min-w-0">
                                        {{-- Status dot --}}
                                        @if($key->is_active)
                                            <span class="flex-shrink-0 h-2 w-2 rounded-full bg-green-500" title="Active"></span>
                                        @else
                                            <span class="flex-shrink-0 h-2 w-2 rounded-full bg-warm-sand" title="Inactive"></span>
                                        @endif
                                        <span class="text-sm font-semibold text-off-black truncate">{{ $key->name }}</span>
                                    </div>
                                    {{-- Tier badge --}}
                                    @if($key->isSubscription())
                                        <span class="flex-shrink-0 inline-flex items-center rounded-btn bg-purple-100 px-2 py-0.5 text-xs font-medium text-purple-700">Subscription</span>
                                    @else
                                        <span class="flex-shrink-0 inline-flex items-center rounded-btn bg-canvas px-2 py-0.5 text-xs font-medium text-off-black border border-oat">Paid</span>
                                    @endif
                                </div>

                                {{-- Masked key with copy --}}
                                <div class="flex items-center gap-2 mb-3">
                                    <code class="flex-1 text-xs font-mono text-muted bg-canvas rounded-btn px-2.5 py-1.5 border border-oat truncate">{{ $key->masked_key }}</code>
                                    <button @click="navigator.clipboard.writeText('{{ $key->masked_key }}'); copied = true; setTimeout(() => copied = false, 1500)"
                                            class="flex-shrink-0 rounded-btn border border-oat px-2 py-1.5 text-xs text-muted hover:text-off-black hover:border-off-black/30 btn-intercom transition-colors"
                                            :class="copied ? 'border-green-300 text-green-600' : ''">
                                        <span x-show="!copied">Copy ID</span>
                                        <span x-show="copied" x-cloak>Copied!</span>
                                    </button>
                                </div>

                                {{-- Footer: last used + actions --}}
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-muted">
                                        {{ $key->last_used_at ? 'Digunakan ' . $key->last_used_at->diffForHumans() : 'Belum pernah digunakan' }}
                                    </span>
                                    <div class="flex items-center gap-1.5">
                                        <form method="POST" action="{{ route('api-keys.toggle', $key) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="rounded-btn px-2 py-1 text-xs font-medium transition-colors btn-intercom
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
                                                    class="rounded-btn border border-red-300 bg-red-50 px-2 py-1 text-xs font-medium text-red-700 hover:bg-red-100 btn-intercom transition-colors">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                {{-- Empty state --}}
                <div class="bg-surface border border-oat rounded-card px-6 py-12 text-center">
                    <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-canvas border border-oat">
                        <svg class="h-6 w-6 text-muted" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-off-black tracking-sub mb-1">Belum ada API Key</h3>
                    <p class="text-sm text-muted mb-4">Buat API key pertama Anda untuk mulai menggunakan model AI.</p>
                    <button @click="showCreateForm = true; $nextTick(() => $refs.nameInput?.focus())"
                            class="inline-flex items-center gap-1.5 rounded-btn bg-off-black px-4 py-2 text-sm font-medium text-white btn-intercom transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        Buat Key Pertama
                    </button>
                </div>
            @endif

            {{-- ============================================================ --}}
            {{-- WALLET & PLAN STATUS (compact) --}}
            {{-- ============================================================ --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="bg-surface border border-oat rounded-card px-4 py-3 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-muted uppercase tracking-wide">Saldo Wallet</p>
                        <p class="text-lg font-bold {{ $quota->paid_balance > 0 ? 'text-off-black' : 'text-red-500' }}">{{ $quota->formatted_paid_balance }}</p>
                    </div>
                    <a href="{{ route('donations.index') }}" class="rounded-btn border border-oat px-3 py-1.5 text-xs font-medium text-off-black hover:border-off-black/30 btn-intercom transition-colors">
                        Top Up
                    </a>
                </div>
                <div class="bg-surface border border-oat rounded-card px-4 py-3 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-muted uppercase tracking-wide">Subscription</p>
                        <p class="text-lg font-bold text-off-black">{{ $activePlan->name ?? 'Tidak Ada' }}</p>
                    </div>
                    @if($activeSubscription && $activeSubscription->isActive())
                        <span class="text-xs text-muted">s/d {{ $activeSubscription->expires_at ? $activeSubscription->expires_at->format('d M Y') : '-' }}</span>
                    @else
                        <a href="{{ route('pricing') }}" class="rounded-btn border border-oat px-3 py-1.5 text-xs font-medium text-fin-orange hover:border-fin-orange btn-intercom transition-colors">
                            Pilih Plan
                        </a>
                    @endif
                </div>
            </div>

            {{-- ============================================================ --}}
            {{-- PANDUAN SETUP (collapsible) --}}
            {{-- ============================================================ --}}
            <div class="bg-surface border border-oat rounded-card">
                <button @click="showGuide = !showGuide" class="w-full flex items-center justify-between px-5 py-4 text-left">
                    <div class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                        <span class="text-sm font-semibold text-off-black">Panduan Setup</span>
                        <span class="text-xs text-muted">Kilo Code, Cursor, VS Code, cURL</span>
                    </div>
                    <svg :class="showGuide ? 'rotate-180' : ''" class="h-4 w-4 text-muted transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="showGuide" x-cloak x-collapse>
                    <div class="px-5 pb-5 border-t border-oat pt-4">
                        {{-- Tab buttons --}}
                        <div class="flex flex-wrap gap-1 border-b border-oat mb-4">
                            @foreach(['kilo' => 'Kilo Code', 'cursor' => 'Cursor', 'vscode' => 'VS Code (Continue)', 'curl' => 'cURL / API'] as $tab => $label)
                                <button @click="openGuide = '{{ $tab }}'"
                                        :class="openGuide === '{{ $tab }}' ? 'border-off-black text-off-black' : 'border-transparent text-muted hover:text-off-black hover:border-oat'"
                                        class="px-3 py-2 text-sm font-medium border-b-2 transition whitespace-nowrap">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>

                        {{-- Kilo Code Guide --}}
                        <div x-show="openGuide === 'kilo'" x-cloak>
                            <ol class="list-decimal list-inside space-y-1.5 text-sm text-off-black">
                                <li>Buka Kilo Code, tekan <kbd class="px-1.5 py-0.5 bg-canvas rounded-btn text-xs font-mono border border-oat">Ctrl+P</kbd> lalu pilih <strong>Providers: Manage Providers</strong></li>
                                <li>Klik <strong>Add Custom Provider</strong></li>
                                <li>Isi <strong>Name</strong>: bebas (misal "AI Token")</li>
                                <li>Isi <strong>Base URL</strong>: <code class="bg-canvas px-1.5 py-0.5 rounded-btn text-xs font-mono border border-oat">{{ $baseUrl }}</code></li>
                                <li>Isi <strong>API Key</strong>: paste API key yang Anda buat</li>
                                <li>Klik <strong>Save</strong>, pilih provider, lalu ketik Model ID dari daftar di bawah</li>
                            </ol>
                        </div>

                        {{-- Cursor Guide --}}
                        <div x-show="openGuide === 'cursor'" x-cloak>
                            <ol class="list-decimal list-inside space-y-1.5 text-sm text-off-black">
                                <li>Buka <strong>Settings</strong> > <strong>Models</strong></li>
                                <li>Klik <strong>+ Add Model</strong>, pilih <strong>OpenAI Compatible</strong></li>
                                <li>Isi <strong>Base URL</strong>: <code class="bg-canvas px-1.5 py-0.5 rounded-btn text-xs font-mono border border-oat">{{ $baseUrl }}</code></li>
                                <li>Isi <strong>API Key</strong>: paste API key Anda</li>
                                <li>Isi <strong>Model Name</strong>: ketik Model ID dari daftar di bawah</li>
                                <li>Klik <strong>Save</strong> dan pilih model tersebut</li>
                            </ol>
                        </div>

                        {{-- VS Code (Continue) Guide --}}
                        <div x-show="openGuide === 'vscode'" x-cloak>
                            <ol class="list-decimal list-inside space-y-1.5 text-sm text-off-black mb-3">
                                <li>Install extension <strong>Continue</strong> di VS Code</li>
                                <li>Buka <code class="bg-canvas px-1.5 py-0.5 rounded-btn text-xs font-mono border border-oat">~/.continue/config.json</code></li>
                                <li>Tambahkan provider baru di array <code>models</code>:</li>
                            </ol>
                            <div class="relative" x-data="{ copied: false }">
                                <pre class="bg-off-black text-white rounded-card p-4 text-xs font-mono overflow-x-auto">{
  "models": [{
    "title": "AI Token",
    "provider": "openai",
    "model": "MODEL_ID_DISINI",
    "apiBase": "{{ $baseUrl }}",
    "apiKey": "API_KEY_ANDA"
  }]
}</pre>
                                <button @click="navigator.clipboard.writeText(JSON.stringify({models:[{title:'AI Token',provider:'openai',model:'claude-sonnet-4.5',apiBase:'{{ $baseUrl }}',apiKey:'YOUR_API_KEY'}]},null,2)); copied=true; setTimeout(()=>copied=false,2000)"
                                        class="absolute top-2 right-2 px-2 py-1 bg-white/10 text-white/70 text-xs rounded-btn hover:bg-white/20 transition">
                                    <span x-show="!copied">Copy</span>
                                    <span x-show="copied" x-cloak class="text-green-400">Copied!</span>
                                </button>
                            </div>
                        </div>

                        {{-- cURL Guide --}}
                        <div x-show="openGuide === 'curl'" x-cloak>
                            <p class="text-sm text-muted mb-3">API ini kompatibel dengan format OpenAI. Contoh request:</p>
                            <div class="relative" x-data="{ copied: false }">
                                <pre x-ref="curlBlock" class="bg-off-black text-white rounded-card p-4 text-xs font-mono overflow-x-auto">curl -X POST {{ $baseUrl }}/chat/completions \
  -H "Authorization: Bearer API_KEY_ANDA" \
  -H "Content-Type: application/json" \
  -d '{
    "model": "claude-sonnet-4.5",
    "messages": [{"role": "user", "content": "Hello!"}],
    "max_tokens": 1000
  }'</pre>
                                <button @click="navigator.clipboard.writeText($refs.curlBlock.textContent.trim()); copied=true; setTimeout(()=>copied=false,2000)"
                                        class="absolute top-2 right-2 px-2 py-1 bg-white/10 text-white/70 text-xs rounded-btn hover:bg-white/20 transition">
                                    <span x-show="!copied">Copy</span>
                                    <span x-show="copied" x-cloak class="text-green-400">Copied!</span>
                                </button>
                            </div>
                            <div class="mt-3 text-xs text-muted">
                                <p class="font-medium mb-1">Endpoints:</p>
                                <ul class="list-disc list-inside space-y-0.5">
                                    <li><code>POST /chat/completions</code> — Chat (OpenAI format)</li>
                                    <li><code>POST /messages</code> — Chat (Anthropic format)</li>
                                    <li><code>GET /models</code> — List available models</li>
                                    <li><code>GET /health</code> — Health check</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ============================================================ --}}
            {{-- MODEL DAN HARGA (collapsible) --}}
            {{-- ============================================================ --}}
            <div class="bg-surface border border-oat rounded-card" x-data="{ showPremium: false }">
                <button @click="showModels = !showModels" class="w-full flex items-center justify-between px-5 py-4 text-left">
                    <div class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z"/></svg>
                        <span class="text-sm font-semibold text-off-black">Model dan Harga</span>
                        <span class="text-xs text-muted">{{ $freeModels->count() + $paidModels->count() }} model tersedia</span>
                    </div>
                    <svg :class="showModels ? 'rotate-180' : ''" class="h-4 w-4 text-muted transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="showModels" x-cloak x-collapse>
                    <div class="px-5 pb-5 border-t border-oat pt-4">
                        <p class="text-xs text-muted mb-4">Copy <strong>Model ID</strong> dan paste di konfigurasi tool Anda. Harga per 1M token (setelah diskon).</p>

                        {{-- Free Tier Models --}}
                        @if($freeModels->count() > 0)
                            <div class="mb-5">
                                <h4 class="text-xs font-semibold text-muted uppercase tracking-wide mb-2 flex items-center gap-1.5">
                                    <span class="inline-flex items-center rounded-btn bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">Free Tier</span>
                                    Tersedia untuk semua API key
                                </h4>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-oat text-sm">
                                        <thead>
                                            <tr class="bg-canvas">
                                                <th class="px-3 py-2 text-left text-xs font-medium text-muted uppercase">Model ID</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-muted uppercase">Nama</th>
                                                <th class="px-3 py-2 text-center text-xs font-medium text-muted uppercase">Context</th>
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
                                                <tr class="hover:bg-canvas" x-data="{ copied: false }">
                                                    <td class="px-3 py-2 font-mono text-off-black font-medium text-xs">{{ $model->model_id }}</td>
                                                    <td class="px-3 py-2 text-muted">{{ $model->model_name }}</td>
                                                    <td class="px-3 py-2 text-center text-muted text-xs">
                                                        @if($model->max_context_tokens)
                                                            @if($model->max_context_tokens >= 1000000)
                                                                {{ round($model->max_context_tokens / 1000000, 1) }}M
                                                            @else
                                                                {{ round($model->max_context_tokens / 1000) }}K
                                                            @endif
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
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
                                                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-btn border transition btn-intercom"
                                                                :class="copied ? 'bg-green-100 border-green-300 text-green-700' : 'bg-surface border-oat text-muted hover:text-off-black hover:border-off-black/30'">
                                                            <span x-show="!copied">Copy</span>
                                                            <span x-show="copied" x-cloak>Copied!</span>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        {{-- Premium Models (nested collapsible) --}}
                        @if($paidModels->count() > 0)
                            <div>
                                <button @click="showPremium = !showPremium" class="flex items-center gap-2 text-xs font-semibold text-muted uppercase tracking-wide mb-2 hover:text-off-black transition">
                                    <span class="inline-flex items-center rounded-btn bg-canvas px-2 py-0.5 text-xs font-medium text-off-black border border-oat">Premium</span>
                                    Model Premium (hanya API key Paid)
                                    <svg :class="showPremium ? 'rotate-180' : ''" class="w-3.5 h-3.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                                <div x-show="showPremium" x-cloak x-collapse>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-oat text-sm">
                                            <thead>
                                                <tr class="bg-canvas">
                                                    <th class="px-3 py-2 text-left text-xs font-medium text-muted uppercase">Model ID</th>
                                                    <th class="px-3 py-2 text-left text-xs font-medium text-muted uppercase">Nama</th>
                                                    <th class="px-3 py-2 text-center text-xs font-medium text-muted uppercase">Context</th>
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
                                                    <tr class="hover:bg-canvas" x-data="{ copied: false }">
                                                        <td class="px-3 py-2 font-mono text-off-black font-medium text-xs">{{ $model->model_id }}</td>
                                                        <td class="px-3 py-2 text-muted">{{ $model->model_name }}</td>
                                                        <td class="px-3 py-2 text-center text-muted text-xs">
                                                            @if($model->max_context_tokens)
                                                                @if($model->max_context_tokens >= 1000000)
                                                                    {{ round($model->max_context_tokens / 1000000, 1) }}M
                                                                @else
                                                                    {{ round($model->max_context_tokens / 1000) }}K
                                                                @endif
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
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
                                                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-btn border transition btn-intercom"
                                                                    :class="copied ? 'bg-green-100 border-green-300 text-green-700' : 'bg-surface border-oat text-muted hover:text-off-black hover:border-off-black/30'">
                                                                <span x-show="!copied">Copy</span>
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
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
