<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <h2 class="font-semibold text-xl text-off-black leading-tight tracking-heading">
                {{ __('Model Pricing') }}
            </h2>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">Admin</span>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Breadcrumb --}}
            <nav class="text-sm text-muted">
                <span>Admin</span>
                <span class="mx-2">/</span>
                <span class="text-off-black font-medium">Model Pricing</span>
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

            {{-- Add New Model --}}
            <div class="bg-surface border border-oat rounded-card" x-data="{ showAdd: false }">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-off-black tracking-sub">Add New Model</h3>
                        <button type="button" @click="showAdd = !showAdd"
                            class="px-4 py-2 text-sm font-medium rounded-btn border border-oat hover:bg-canvas transition"
                            x-text="showAdd ? 'Cancel' : 'Add Model'">
                        </button>
                    </div>

                    <div x-show="showAdd" x-transition class="mt-4">
                        <form action="{{ route('admin.model-pricing.store') }}" method="POST" class="space-y-4">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div>
                                    <label for="model_id" class="block text-sm font-medium text-off-black mb-1">Model ID</label>
                                    <input type="text" name="model_id" id="model_id" required
                                        placeholder="e.g. gpt-4o"
                                        class="w-full rounded-lg border-oat focus:border-fin-orange focus:ring-fin-orange">
                                    @error('model_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="model_name" class="block text-sm font-medium text-off-black mb-1">Model Name</label>
                                    <input type="text" name="model_name" id="model_name" required
                                        placeholder="e.g. GPT-4o"
                                        class="w-full rounded-lg border-oat focus:border-fin-orange focus:ring-fin-orange">
                                    @error('model_name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="input_price_usd" class="block text-sm font-medium text-off-black mb-1">Input Price USD / 1M tokens</label>
                                    <input type="number" name="input_price_usd" id="input_price_usd" step="0.01" min="0" required
                                        placeholder="0.00"
                                        class="w-full rounded-lg border-oat focus:border-fin-orange focus:ring-fin-orange">
                                    @error('input_price_usd')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="output_price_usd" class="block text-sm font-medium text-off-black mb-1">Output Price USD / 1M tokens</label>
                                    <input type="number" name="output_price_usd" id="output_price_usd" step="0.01" min="0" required
                                        placeholder="0.00"
                                        class="w-full rounded-lg border-oat focus:border-fin-orange focus:ring-fin-orange">
                                    @error('output_price_usd')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="discount_percent" class="block text-sm font-medium text-off-black mb-1">Discount %</label>
                                    <input type="number" name="discount_percent" id="discount_percent" min="0" max="100" value="0"
                                        class="w-full rounded-lg border-oat focus:border-fin-orange focus:ring-fin-orange">
                                    @error('discount_percent')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="max_context_tokens" class="block text-sm font-medium text-off-black mb-1">Max Context Tokens</label>
                                    <input type="number" name="max_context_tokens" id="max_context_tokens" min="1000" step="1"
                                        placeholder="e.g. 200000"
                                        class="w-full rounded-lg border-oat focus:border-fin-orange focus:ring-fin-orange">
                                    @error('max_context_tokens')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="flex items-end">
                                    <label class="flex items-center space-x-2">
                                        <input type="checkbox" name="is_free_tier" value="1"
                                            class="rounded border-oat text-fin-orange focus:ring-fin-orange">
                                        <span class="text-sm font-medium text-off-black">Free Tier</span>
                                    </label>
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" class="px-5 py-2 bg-off-black text-white font-medium rounded-btn hover:bg-off-black/90 transition">
                                    Add Model
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Models Table --}}
            <div class="bg-surface border border-oat rounded-card" x-data="modelStatusChecker()">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-off-black tracking-sub">Model Pricing List</h3>
                            <p class="text-sm text-muted">Exchange Rate: <strong>1 USD = Rp {{ number_format($exchangeRate, 0, ',', '.') }}</strong></p>
                        </div>
                        <div class="flex items-center space-x-3">
                            <button type="button" @click="checkStatus()"
                                :disabled="loading"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-btn border border-oat hover:bg-canvas transition disabled:opacity-50 disabled:cursor-wait">
                                <svg x-show="!loading" class="w-4 h-4 mr-2 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <svg x-show="loading" class="w-4 h-4 mr-2 animate-spin text-muted" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <span x-text="loading ? 'Checking...' : 'Cek Status Model'"></span>
                            </button>
                            <span x-show="checkedAt" x-text="'✓ ' + checkedAt" class="text-xs text-muted"></span>
                        </div>
                    </div>

                    {{-- Status Results Panel --}}
                    <template x-if="error">
                        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                            <p class="text-sm text-red-700" x-text="error"></p>
                        </div>
                    </template>
                    <template x-if="results.length > 0">
                        <div class="mb-4 p-4 bg-canvas border border-oat rounded-lg">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-sm font-semibold text-off-black">Status Upstream Model</h4>
                                <span class="text-xs text-muted" x-text="'Total upstream: ' + upstreamTotal + ' model'"></span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                                <template x-for="m in results" :key="m.model_id">
                                    <div class="flex items-center justify-between px-3 py-2 rounded-lg border"
                                        :class="m.upstream_available ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'">
                                        <span class="text-xs font-mono truncate mr-2" x-text="m.model_id"></span>
                                        <span class="flex-shrink-0 text-xs font-medium"
                                            :class="m.upstream_available ? 'text-green-700' : 'text-red-700'"
                                            x-text="m.upstream_available ? '✅ Online' : '❌ Offline'"></span>
                                    </div>
                                </template>
                            </div>
                            <template x-if="newModels.length > 0">
                                <div class="mt-3 pt-3 border-t border-oat">
                                    <h5 class="text-xs font-semibold text-off-black mb-2">🆕 Model baru di upstream (belum ada di DB):</h5>
                                    <div class="flex flex-wrap gap-1.5">
                                        <template x-for="nm in newModels" :key="nm">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-mono bg-blue-100 text-blue-800" x-text="nm"></span>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-oat">
                            <thead class="bg-canvas">
                                <tr>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-muted uppercase">Model ID</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-muted uppercase">Name</th>
                                    <th class="px-3 py-3 text-right text-xs font-medium text-muted uppercase">Input USD</th>
                                    <th class="px-3 py-3 text-right text-xs font-medium text-muted uppercase">Output USD</th>
                                    <th class="px-3 py-3 text-right text-xs font-medium text-muted uppercase">Input IDR</th>
                                    <th class="px-3 py-3 text-right text-xs font-medium text-muted uppercase">Output IDR</th>
                                    <th class="px-3 py-3 text-center text-xs font-medium text-muted uppercase">Discount</th>
                                    <th class="px-3 py-3 text-right text-xs font-medium text-muted uppercase">Input IDR (Net)</th>
                                    <th class="px-3 py-3 text-right text-xs font-medium text-muted uppercase">Output IDR (Net)</th>
                                    <th class="px-3 py-3 text-center text-xs font-medium text-muted uppercase">Max Context</th>
                                    <th class="px-3 py-3 text-center text-xs font-medium text-muted uppercase">Free Tier</th>
                                    <th class="px-3 py-3 text-center text-xs font-medium text-muted uppercase">Active</th>
                                    <th class="px-3 py-3 text-center text-xs font-medium text-muted uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-oat">
                                @forelse($models as $model)
                                <tbody x-data="{ editing: false }" class="border-b border-oat">
                                    {{-- Display Row --}}
                                    <tr x-show="!editing" class="bg-surface hover:bg-canvas">
                                        <td class="px-3 py-3 text-sm font-mono text-off-black">{{ $model->model_id }}</td>
                                        <td class="px-3 py-3 text-sm text-off-black">{{ $model->model_name }}</td>
                                        <td class="px-3 py-3 text-sm text-off-black text-right">${{ number_format($model->input_price_usd, 4) }}</td>
                                        <td class="px-3 py-3 text-sm text-off-black text-right">${{ number_format($model->output_price_usd, 4) }}</td>
                                        <td class="px-3 py-3 text-sm text-off-black text-right">Rp {{ number_format($model->input_price_usd * $exchangeRate, 0, ',', '.') }}</td>
                                        <td class="px-3 py-3 text-sm text-off-black text-right">Rp {{ number_format($model->output_price_usd * $exchangeRate, 0, ',', '.') }}</td>
                                        <td class="px-3 py-3 text-sm text-off-black text-center">{{ $model->discount_percent }}%</td>
                                        @php
                                            $discountMultiplier = 1 - ($model->discount_percent / 100);
                                            $inputIdrNet = $model->input_price_usd * $exchangeRate * $discountMultiplier;
                                            $outputIdrNet = $model->output_price_usd * $exchangeRate * $discountMultiplier;
                                        @endphp
                                        <td class="px-3 py-3 text-sm font-medium text-right {{ $model->discount_percent > 0 ? 'text-green-700' : 'text-off-black' }}">
                                            Rp {{ number_format($inputIdrNet, 0, ',', '.') }}
                                        </td>
                                        <td class="px-3 py-3 text-sm font-medium text-right {{ $model->discount_percent > 0 ? 'text-green-700' : 'text-off-black' }}">
                                            Rp {{ number_format($outputIdrNet, 0, ',', '.') }}
                                        </td>
                                        <td class="px-3 py-3 text-sm text-off-black text-center">
                                            @if($model->max_context_tokens)
                                                @if($model->max_context_tokens >= 1000000)
                                                    {{ round($model->max_context_tokens / 1000000, 1) }}M
                                                @else
                                                    {{ round($model->max_context_tokens / 1000) }}K
                                                @endif
                                            @else
                                                &infin;
                                            @endif
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            @if($model->is_free_tier)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Yes</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-canvas text-muted">No</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            @if($model->is_active)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <div class="flex items-center justify-center space-x-2">
                                                <button type="button" @click="editing = true" class="text-fin-orange hover:text-fin-orange/80 text-sm font-medium">Edit</button>
                                                <form action="{{ route('admin.model-pricing.destroy', $model) }}" method="POST" class="inline"
                                                    onsubmit="return confirm('Delete {{ $model->model_name }}?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    {{-- Edit Row --}}
                                    <tr x-show="editing" x-cloak class="bg-fin-orange-light">
                                        <td colspan="13" class="px-3 py-4">
                                            <form action="{{ route('admin.model-pricing.update', $model) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <div class="flex items-center gap-2 mb-3">
                                                    <span class="text-sm font-semibold text-off-black">Editing: {{ $model->model_name }}</span>
                                                    <span class="text-xs text-muted font-mono">({{ $model->model_id }})</span>
                                                </div>
                                                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 items-end">
                                                    <div>
                                                        <label class="block text-xs font-medium text-muted mb-1">Input USD / 1M</label>
                                                        <input type="number" name="input_price_usd" step="0.0001" min="0"
                                                            value="{{ $model->input_price_usd }}"
                                                            class="w-full text-sm rounded-lg border-oat focus:border-fin-orange focus:ring-fin-orange">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-muted mb-1">Output USD / 1M</label>
                                                        <input type="number" name="output_price_usd" step="0.0001" min="0"
                                                            value="{{ $model->output_price_usd }}"
                                                            class="w-full text-sm rounded-lg border-oat focus:border-fin-orange focus:ring-fin-orange">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-muted mb-1">Discount %</label>
                                                        <input type="number" name="discount_percent" min="0" max="100"
                                                            value="{{ $model->discount_percent }}"
                                                            class="w-full text-sm rounded-lg border-oat focus:border-fin-orange focus:ring-fin-orange">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-muted mb-1">Max Context</label>
                                                        <input type="number" name="max_context_tokens" min="1000" step="1"
                                                            value="{{ $model->max_context_tokens }}"
                                                            placeholder="No limit"
                                                            class="w-full text-sm rounded-lg border-oat focus:border-fin-orange focus:ring-fin-orange">
                                                    </div>
                                                    <div class="flex items-center space-x-4 pb-1">
                                                        <label class="flex items-center space-x-1.5">
                                                            <input type="checkbox" name="is_free_tier" value="1" {{ $model->is_free_tier ? 'checked' : '' }}
                                                                class="rounded border-oat text-fin-orange focus:ring-fin-orange">
                                                            <span class="text-xs text-off-black">Free Tier</span>
                                                        </label>
                                                        <label class="flex items-center space-x-1.5">
                                                            <input type="checkbox" name="is_active" value="1" {{ $model->is_active ? 'checked' : '' }}
                                                                class="rounded border-oat text-fin-orange focus:ring-fin-orange">
                                                            <span class="text-xs text-off-black">Active</span>
                                                        </label>
                                                    </div>
                                                    <div class="flex items-center space-x-2">
                                                        <button type="submit" class="px-4 py-2 bg-off-black text-white text-sm font-medium rounded-btn hover:bg-off-black/90 transition">
                                                            Save
                                                        </button>
                                                        <button type="button" @click="editing = false" class="px-4 py-2 bg-oat text-off-black text-sm font-medium rounded-btn hover:bg-oat/80 transition">
                                                            Cancel
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                </tbody>
                                @empty
                                    <tr>
                                        <td colspan="13" class="px-3 py-6 text-sm text-muted text-center">No models configured yet. Add your first model above.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
        function modelStatusChecker() {
            return {
                loading: false,
                results: [],
                newModels: [],
                upstreamTotal: 0,
                checkedAt: '',
                error: '',

                async checkStatus() {
                    this.loading = true;
                    this.error = '';

                    try {
                        const response = await fetch('{{ route("admin.model-pricing.check-status") }}');
                        const data = await response.json();

                        if (!response.ok) {
                            this.error = data.error || 'Gagal mengecek status model';
                            return;
                        }

                        this.results = data.models;
                        this.newModels = data.new_models || [];
                        this.upstreamTotal = data.upstream_total;
                        this.checkedAt = data.checked_at;
                    } catch (e) {
                        this.error = 'Network error: ' + e.message;
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
