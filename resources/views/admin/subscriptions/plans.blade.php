<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <h2 class="font-semibold text-xl text-off-black leading-tight tracking-heading">
                {{ __('Subscription Plans') }}
            </h2>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">Admin</span>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Breadcrumb --}}
            <nav class="text-sm text-muted">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-off-black">Admin</a>
                <span class="mx-2">/</span>
                <span class="text-off-black font-medium">Subscription Plans</span>
            </nav>

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <p class="text-green-800 text-sm font-medium">{{ session('success') }}</p>
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <p class="text-red-800 text-sm font-medium">{{ session('error') }}</p>
                </div>
            @endif

            {{-- Add New Plan --}}
            <div class="bg-surface border border-oat rounded-card" x-data="{ showAdd: false }">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-off-black tracking-sub">Tambah Plan Baru</h3>
                        <button type="button" @click="showAdd = !showAdd"
                            class="px-4 py-2 text-sm font-medium rounded-btn border border-oat hover:bg-canvas transition"
                            x-text="showAdd ? 'Batal' : 'Tambah Plan'">
                        </button>
                    </div>

                    <div x-show="showAdd" x-transition class="mt-4">
                        <form action="{{ route('admin.subscription-plans.store') }}" method="POST" class="space-y-4">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-off-black mb-1">Nama Plan</label>
                                    <input type="text" name="name" required placeholder="e.g. PRO"
                                        class="w-full rounded-lg border-oat focus:border-fin-orange focus:ring-fin-orange">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-off-black mb-1">Slug</label>
                                    <input type="text" name="slug" required placeholder="e.g. pro (lowercase, unique)"
                                        class="w-full rounded-lg border-oat focus:border-fin-orange focus:ring-fin-orange">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-off-black mb-1">Tipe</label>
                                    <select name="type" class="w-full rounded-lg border-oat focus:border-fin-orange focus:ring-fin-orange">
                                        <option value="monthly">Bulanan</option>
                                        <option value="daily">Harian</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-off-black mb-1">Harga (IDR)</label>
                                    <input type="number" name="price_idr" required min="0" placeholder="29000"
                                        class="w-full rounded-lg border-oat focus:border-fin-orange focus:ring-fin-orange">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-off-black mb-1">Daily Request Limit</label>
                                    <input type="number" name="daily_request_limit" min="1" placeholder="Kosongkan = unlimited"
                                        class="w-full rounded-lg border-oat focus:border-fin-orange focus:ring-fin-orange">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-off-black mb-1">Per-Minute Limit</label>
                                    <input type="number" name="per_minute_limit" required min="1" placeholder="30"
                                        class="w-full rounded-lg border-oat focus:border-fin-orange focus:ring-fin-orange">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-off-black mb-1">Concurrent Limit</label>
                                    <input type="number" name="concurrent_limit" required min="1" placeholder="2"
                                        class="w-full rounded-lg border-oat focus:border-fin-orange focus:ring-fin-orange">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-off-black mb-1">Max Token Usage</label>
                                    <input type="number" name="max_token_usage" min="1" placeholder="Kosongkan = unlimited"
                                        class="w-full rounded-lg border-oat focus:border-fin-orange focus:ring-fin-orange">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-off-black mb-1">Sort Order</label>
                                    <input type="number" name="sort_order" min="0" value="0"
                                        class="w-full rounded-lg border-oat focus:border-fin-orange focus:ring-fin-orange">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-off-black mb-1">Tier Level</label>
                                    <input type="number" name="tier_level" min="0" value="0" placeholder="0=free, 1=pro, 2=premium"
                                        class="w-full rounded-lg border-oat focus:border-fin-orange focus:ring-fin-orange">
                                    <p class="text-xs text-muted mt-1">Hierarki plan untuk logika upgrade/downgrade</p>
                                </div>
                            </div>

                            {{-- Features --}}
                            <div x-data="{ features: [''] }">
                                <label class="block text-sm font-medium text-off-black mb-2">Fitur</label>
                                <template x-for="(feature, index) in features" :key="index">
                                    <div class="flex items-center gap-2 mb-2">
                                        <input type="text" :name="'features[' + index + ']'" x-model="features[index]"
                                            placeholder="e.g. Akses semua model"
                                            class="flex-1 rounded-lg border-oat focus:border-fin-orange focus:ring-fin-orange text-sm">
                                        <button type="button" @click="features.splice(index, 1)"
                                            class="text-red-500 hover:text-red-700 text-sm" x-show="features.length > 1">&times;</button>
                                    </div>
                                </template>
                                <button type="button" @click="features.push('')"
                                    class="text-sm text-fin-orange hover:text-fin-orange/80 font-medium">+ Tambah Fitur</button>
                            </div>

                            {{-- Allowed Models --}}
                            <div x-data="{ allModels: true, selectedModels: [] }">
                                <label class="block text-sm font-medium text-off-black mb-2">Model yang Tersedia</label>
                                <label class="flex items-center space-x-2 mb-3">
                                    <input type="checkbox" name="all_models" value="1" x-model="allModels"
                                        class="rounded border-oat text-fin-orange focus:ring-fin-orange">
                                    <span class="text-sm text-off-black font-medium">Semua Model (tanpa batasan)</span>
                                </label>
                                <div x-show="!allModels" x-transition class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2 p-3 bg-canvas rounded-lg border border-oat">
                                    @foreach($availableModels as $model)
                                    <label class="flex items-center space-x-2">
                                        <input type="checkbox" name="allowed_models[]" value="{{ $model }}"
                                            class="rounded border-oat text-fin-orange focus:ring-fin-orange">
                                        <span class="text-xs text-off-black font-mono">{{ $model }}</span>
                                    </label>
                                    @endforeach
                                </div>
                                <p class="text-xs text-muted mt-1">Pilih model yang bisa diakses oleh plan ini. Centang "Semua Model" untuk tanpa batasan.</p>
                            </div>

                            <div class="flex items-center gap-4">
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" name="is_popular" value="1"
                                        class="rounded border-oat text-fin-orange focus:ring-fin-orange">
                                    <span class="text-sm font-medium text-off-black">Tandai Popular</span>
                                </label>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" class="px-5 py-2 bg-off-black text-white font-medium rounded-btn hover:bg-off-black/90 transition">
                                    Simpan Plan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Plans Table --}}
            <div class="bg-surface border border-oat rounded-card">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-off-black tracking-sub mb-4">Daftar Plan</h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-oat">
                            <thead class="bg-canvas">
                                <tr>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-muted uppercase">Plan</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-muted uppercase">Slug</th>
                                    <th class="px-3 py-3 text-center text-xs font-medium text-muted uppercase">Tipe</th>
                                    <th class="px-3 py-3 text-right text-xs font-medium text-muted uppercase">Harga</th>
                                    <th class="px-3 py-3 text-right text-xs font-medium text-muted uppercase">Daily Limit</th>
                                    <th class="px-3 py-3 text-right text-xs font-medium text-muted uppercase">Per-Min</th>
                                    <th class="px-3 py-3 text-right text-xs font-medium text-muted uppercase">Concurrent</th>
                                    <th class="px-3 py-3 text-right text-xs font-medium text-muted uppercase">Subscriber</th>
                                    <th class="px-3 py-3 text-center text-xs font-medium text-muted uppercase">Popular</th>
                                    <th class="px-3 py-3 text-center text-xs font-medium text-muted uppercase">Tier</th>
                                    <th class="px-3 py-3 text-center text-xs font-medium text-muted uppercase">Models</th>
                                    <th class="px-3 py-3 text-center text-xs font-medium text-muted uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-oat">
                                @forelse($plans as $plan)
                                <tbody x-data="{ editing: false }" class="border-b border-oat">
                                    {{-- Display Row --}}
                                    <tr x-show="!editing" class="bg-surface hover:bg-canvas">
                                        <td class="px-3 py-3 text-sm font-medium text-off-black">{{ $plan->name }}</td>
                                        <td class="px-3 py-3 text-sm font-mono text-muted">{{ $plan->slug }}</td>
                                        <td class="px-3 py-3 text-sm text-center">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $plan->type === 'monthly' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                                {{ $plan->type === 'monthly' ? 'Bulanan' : 'Harian' }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-3 text-sm text-off-black text-right font-medium">{{ $plan->formatted_price }}</td>
                                        <td class="px-3 py-3 text-sm text-off-black text-right">{{ $plan->daily_request_limit ? number_format($plan->daily_request_limit) : '∞' }}</td>
                                        <td class="px-3 py-3 text-sm text-off-black text-right">{{ $plan->per_minute_limit }}/min</td>
                                        <td class="px-3 py-3 text-sm text-off-black text-right">{{ $plan->concurrent_limit }}</td>
                                        <td class="px-3 py-3 text-sm text-off-black text-right">
                                            <span class="font-medium">{{ $plan->active_subs_count }}</span>
                                            <span class="text-muted text-xs">aktif</span>
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            @if($plan->is_popular)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">★</span>
                                            @else
                                                <span class="text-muted text-xs">—</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">{{ $plan->tier_level ?? 0 }}</span>
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            @if($plan->allowed_models === null)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Semua</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">{{ count($plan->allowed_models) }} model</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <div class="flex items-center justify-center space-x-2">
                                                <button type="button" @click="editing = true" class="text-fin-orange hover:text-fin-orange/80 text-sm font-medium">Edit</button>
                                                @if($plan->slug !== 'free')
                                                <form action="{{ route('admin.subscription-plans.destroy', $plan) }}" method="POST" class="inline"
                                                    onsubmit="return confirm('Hapus plan {{ $plan->name }}?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">Hapus</button>
                                                </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>

                                    {{-- Edit Row --}}
                                    <tr x-show="editing" x-cloak class="bg-canvas">
                                        <td colspan="12" class="px-3 py-4">
                                            <form action="{{ route('admin.subscription-plans.update', $plan) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <div class="flex items-center gap-2 mb-3">
                                                    <span class="text-sm font-semibold text-off-black">Editing: {{ $plan->name }}</span>
                                                    <span class="text-xs text-muted font-mono">({{ $plan->slug }})</span>
                                                </div>
                                                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 items-end">
                                                    <div>
                                                        <label class="block text-xs font-medium text-muted mb-1">Nama</label>
                                                        <input type="text" name="name" value="{{ $plan->name }}" required
                                                            class="w-full text-sm rounded-lg border-oat focus:border-fin-orange focus:ring-fin-orange">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-muted mb-1">Tipe</label>
                                                        <select name="type" class="w-full text-sm rounded-lg border-oat focus:border-fin-orange focus:ring-fin-orange">
                                                            <option value="monthly" {{ $plan->type === 'monthly' ? 'selected' : '' }}>Bulanan</option>
                                                            <option value="daily" {{ $plan->type === 'daily' ? 'selected' : '' }}>Harian</option>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-muted mb-1">Harga IDR</label>
                                                        <input type="number" name="price_idr" value="{{ $plan->price_idr }}" min="0" required
                                                            class="w-full text-sm rounded-lg border-oat focus:border-fin-orange focus:ring-fin-orange">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-muted mb-1">Daily Limit</label>
                                                        <input type="number" name="daily_request_limit" value="{{ $plan->daily_request_limit }}" min="1"
                                                            placeholder="∞"
                                                            class="w-full text-sm rounded-lg border-oat focus:border-fin-orange focus:ring-fin-orange">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-muted mb-1">Per-Min</label>
                                                        <input type="number" name="per_minute_limit" value="{{ $plan->per_minute_limit }}" min="1" required
                                                            class="w-full text-sm rounded-lg border-oat focus:border-fin-orange focus:ring-fin-orange">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-muted mb-1">Concurrent</label>
                                                        <input type="number" name="concurrent_limit" value="{{ $plan->concurrent_limit }}" min="1" required
                                                            class="w-full text-sm rounded-lg border-oat focus:border-fin-orange focus:ring-fin-orange">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-muted mb-1">Max Token</label>
                                                        <input type="number" name="max_token_usage" value="{{ $plan->max_token_usage }}" min="1"
                                                            placeholder="∞"
                                                            class="w-full text-sm rounded-lg border-oat focus:border-fin-orange focus:ring-fin-orange">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-muted mb-1">Tier Level</label>
                                                        <input type="number" name="tier_level" value="{{ $plan->tier_level ?? 0 }}" min="0"
                                                            class="w-full text-sm rounded-lg border-oat focus:border-fin-orange focus:ring-fin-orange">
                                                    </div>
                                                    <div class="flex items-center space-x-4 pb-1">
                                                        <label class="flex items-center space-x-1.5">
                                                            <input type="checkbox" name="is_popular" value="1" {{ $plan->is_popular ? 'checked' : '' }}
                                                                class="rounded border-oat text-fin-orange focus:ring-fin-orange">
                                                            <span class="text-xs text-off-black">Popular</span>
                                                        </label>
                                                    </div>
                                                </div>

                                                {{-- Edit Features --}}
                                                <div class="mt-3" x-data="{ features: {{ json_encode($plan->features ?? ['']) }} }">
                                                    <label class="block text-xs font-medium text-muted mb-1">Fitur</label>
                                                    <template x-for="(feature, index) in features" :key="index">
                                                        <div class="flex items-center gap-2 mb-1">
                                                            <input type="text" :name="'features[' + index + ']'" x-model="features[index]"
                                                                class="flex-1 text-sm rounded-lg border-oat focus:border-fin-orange focus:ring-fin-orange">
                                                            <button type="button" @click="features.splice(index, 1)"
                                                                class="text-red-500 hover:text-red-700 text-xs" x-show="features.length > 1">&times;</button>
                                                        </div>
                                                    </template>
                                                    <button type="button" @click="features.push('')"
                                                        class="text-xs text-fin-orange hover:text-fin-orange/80 font-medium mt-1">+ Fitur</button>
                                                </div>

                                                {{-- Edit Allowed Models --}}
                                                <div class="mt-3" x-data="{ allModels: {{ ($plan->allowed_models === null) ? 'true' : 'false' }} }">
                                                    <label class="block text-xs font-medium text-muted mb-1">Model yang Tersedia</label>
                                                    <label class="flex items-center space-x-2 mb-2">
                                                        <input type="checkbox" name="all_models" value="1" x-model="allModels"
                                                            class="rounded border-oat text-fin-orange focus:ring-fin-orange">
                                                        <span class="text-xs text-off-black font-medium">Semua Model</span>
                                                    </label>
                                                    <div x-show="!allModels" x-transition class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-1.5 p-2 bg-canvas rounded-lg border border-oat max-h-48 overflow-y-auto">
                                                        @foreach($availableModels as $model)
                                                        <label class="flex items-center space-x-1.5">
                                                            <input type="checkbox" name="allowed_models[]" value="{{ $model }}"
                                                                {{ in_array($model, $plan->allowed_models ?? []) ? 'checked' : '' }}
                                                                class="rounded border-oat text-fin-orange focus:ring-fin-orange">
                                                            <span class="text-xs text-off-black font-mono truncate">{{ $model }}</span>
                                                        </label>
                                                        @endforeach
                                                    </div>
                                                </div>

                                                <div class="flex items-center space-x-2 mt-3">
                                                    <input type="hidden" name="sort_order" value="{{ $plan->sort_order }}">
                                                    <button type="submit" class="px-4 py-2 bg-off-black text-white text-sm font-medium rounded-btn hover:bg-off-black/90 transition">
                                                        Simpan
                                                    </button>
                                                    <button type="button" @click="editing = false" class="px-4 py-2 bg-oat text-off-black text-sm font-medium rounded-btn hover:bg-oat/80 transition">
                                                        Batal
                                                    </button>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                </tbody>
                                @empty
                                    <tr>
                                        <td colspan="12" class="px-3 py-6 text-sm text-muted text-center">Belum ada plan. Tambahkan plan pertama di atas.</td>
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
