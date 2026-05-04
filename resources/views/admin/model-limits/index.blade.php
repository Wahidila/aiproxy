<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <h2 class="font-semibold text-xl text-off-black leading-tight tracking-heading">
                {{ __('Model Daily Limits') }}
            </h2>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Emergency</span>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">Admin</span>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Breadcrumb --}}
            <nav class="text-sm text-muted">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-off-black">Admin</a>
                <span class="mx-2">/</span>
                <span class="text-off-black font-medium">Model Daily Limits</span>
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

            {{-- Info Box --}}
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="text-sm text-blue-800">
                        <p class="font-semibold mb-1">Batas Harian Per User Per Model</p>
                        <p>Fitur ini membatasi jumlah request per hari <strong>per user</strong> untuk model tertentu. Contoh: jika limit GPT-5.5 = 10, maka setiap user hanya bisa pakai 10 request/hari untuk model tersebut. Berguna untuk mengontrol biaya model mahal. Perubahan berlaku dalam 60 detik.</p>
                    </div>
                </div>
            </div>

            {{-- Models Table --}}
            <div class="bg-surface border border-oat rounded-card">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-off-black tracking-sub">Per-User Daily Limits</h3>
                            <p class="text-sm text-muted">Set daily request limits per user for expensive models. Empty limit = unlimited.</p>
                        </div>
                        <span class="text-xs text-muted">{{ $models->count() }} models</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-oat">
                            <thead class="bg-canvas">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase">Model ID</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-muted uppercase">Model Name</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-muted uppercase">Today's Total</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-muted uppercase">Daily Limit</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-muted uppercase">Enforced</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-muted uppercase">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-oat">
                                @forelse($models as $model)
                                    @php
                                        $setting = $limits[$model->model_id] ?? null;
                                        $isEnabled = $setting['enabled'] ?? false;
                                        $currentLimit = $setting['limit'] ?? null;
                                        $todayCount = $todayUsage[$model->model_id] ?? 0;
                                        $isAtLimit = $isEnabled && $currentLimit && $todayCount >= $currentLimit;
                                    @endphp
                                    <tr class="bg-surface hover:bg-canvas {{ $isAtLimit ? 'bg-red-50 hover:bg-red-100' : '' }}">
                                        <td class="px-4 py-3 text-sm font-mono text-off-black">{{ $model->model_id }}</td>
                                        <td class="px-4 py-3 text-sm text-off-black">
                                            {{ $model->model_name }}
                                            @if(!$model->is_active)
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-canvas text-muted ml-1">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-center">
                                            @if($isEnabled && $currentLimit)
                                                <span class="font-medium {{ $isAtLimit ? 'text-red-700' : 'text-off-black' }}">
                                                    {{ number_format($todayCount) }} / {{ number_format($currentLimit) }}
                                                </span>
                                                @if($isAtLimit)
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700 ml-1">LIMIT HIT</span>
                                                @endif
                                            @else
                                                <span class="text-muted">{{ number_format($todayCount) }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center" colspan="3">
                                            <form action="{{ route('admin.model-limits.update') }}" method="POST" class="flex items-center justify-center gap-3">
                                                @csrf
                                                <input type="hidden" name="model_id" value="{{ $model->model_id }}">
                                                <input type="number" name="limit" min="1" step="1"
                                                    value="{{ $currentLimit ?: '' }}"
                                                    placeholder="Unlimited"
                                                    class="w-28 text-sm text-center rounded-lg border-oat focus:border-fin-orange focus:ring-fin-orange">
                                                <label class="flex items-center space-x-1.5 flex-shrink-0">
                                                    <input type="hidden" name="enabled" value="0">
                                                    <input type="checkbox" name="enabled" value="1" {{ $isEnabled ? 'checked' : '' }}
                                                        class="rounded border-oat text-fin-orange focus:ring-fin-orange">
                                                    <span class="text-xs text-off-black">On</span>
                                                </label>
                                                <button type="submit" class="px-3 py-1.5 bg-off-black text-white text-xs font-medium rounded-btn hover:bg-off-black/90 transition flex-shrink-0">
                                                    Save
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-6 text-sm text-muted text-center">No models found in model_pricings table.</td>
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
