<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-off-black">
            Riwayat Penggunaan
        </h2>
    </x-slot>

    @php
        if (!function_exists('usageFormatRp')) {
            function usageFormatRp($v) { return 'Rp ' . number_format($v, 0, ',', '.'); }
        }
        if (!function_exists('usageFormatTokens')) {
            function usageFormatTokens($c) {
                if ($c >= 1000000) return number_format($c / 1000000, 1) . 'M';
                if ($c >= 1000) return number_format($c / 1000, 1) . 'K';
                return number_format($c);
            }
        }
        $chartColors = ['#6366f1','#8b5cf6','#ec4899','#f59e0b','#10b981','#06b6d4','#f43f5e','#84cc16'];
    @endphp

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

            {{-- Summary Stats --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-surface border border-oat rounded-card p-5">
                    <p class="text-xs font-medium text-muted uppercase">Total Requests (14d)</p>
                    <p class="mt-1 text-2xl font-bold text-off-black">{{ number_format($summary['total_requests']) }}</p>
                </div>
                <div class="bg-surface border border-oat rounded-card p-5">
                    <p class="text-xs font-medium text-muted uppercase">Total Tokens (14d)</p>
                    <p class="mt-1 text-2xl font-bold text-off-black">{{ usageFormatTokens($summary['total_tokens']) }}</p>
                </div>
                <div class="bg-surface border border-oat rounded-card p-5">
                    <p class="text-xs font-medium text-muted uppercase">Total Biaya (14d)</p>
                    <p class="mt-1 text-2xl font-bold text-fin-orange">{{ usageFormatRp($summary['total_cost']) }}</p>
                </div>
                <div class="bg-surface border border-oat rounded-card p-5">
                    <p class="text-xs font-medium text-muted uppercase">Avg Response Time</p>
                    <p class="mt-1 text-2xl font-bold text-off-black">{{ number_format($summary['avg_response']) }}<span class="text-sm font-normal text-muted">ms</span></p>
                </div>
            </div>

            {{-- Charts Row --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Daily Usage Chart (spans 2 cols) --}}
                <div class="lg:col-span-2 bg-surface border border-oat rounded-card">
                    <div class="px-6 py-5">
                        <h3 class="text-sm font-semibold text-off-black mb-1">Penggunaan Harian (14 Hari)</h3>
                        <p class="text-xs text-muted mb-4">Requests & biaya per hari</p>

                        @php
                            $maxReq = max(1, max(array_column($dailyChart, 'requests')));
                            $maxCost = max(1, max(array_column($dailyChart, 'cost')));
                        @endphp

                        <div class="flex items-end gap-1" style="height: 180px;">
                            @foreach($dailyChart as $date => $day)
                                @php
                                    $reqPct = ($day['requests'] / $maxReq) * 100;
                                    $costPct = $maxCost > 0 ? ($day['cost'] / $maxCost) * 100 : 0;
                                @endphp
                                <div class="flex-1 flex flex-col items-center justify-end h-full group relative">
                                    {{-- Tooltip --}}
                                    <div class="absolute bottom-full mb-2 hidden group-hover:block z-10">
                                        <div class="bg-gray-900 text-white text-xs rounded-lg px-3 py-2 whitespace-nowrap shadow-lg">
                                            <p class="font-semibold">{{ \Carbon\Carbon::parse($date)->format('d M') }}</p>
                                            <p>{{ $day['requests'] }} requests</p>
                                            <p>{{ usageFormatTokens($day['tokens']) }} tokens</p>
                                            <p>{{ usageFormatRp($day['cost']) }}</p>
                                        </div>
                                    </div>
                                    {{-- Bars --}}
                                    <div class="w-full flex gap-px justify-center" style="height: {{ max($reqPct, 3) }}%;">
                                        <div class="flex-1 bg-fin-orange rounded-t opacity-80 hover:opacity-100 transition" style="height: 100%;"></div>
                                        <div class="flex-1 bg-emerald-400 rounded-t opacity-80 hover:opacity-100 transition" style="height: {{ $maxReq > 0 ? max(($costPct / max($reqPct, 1)) * 100, 5) : 5 }}%;"></div>
                                    </div>
                                    <span class="mt-1.5 text-[10px] text-warm-sand leading-none">{{ \Carbon\Carbon::parse($date)->format('d/m') }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="flex items-center gap-4 mt-3 text-xs text-muted">
                            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-fin-orange inline-block"></span> Requests</span>
                            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-emerald-400 inline-block"></span> Biaya</span>
                        </div>
                    </div>
                </div>

                {{-- Usage by API Key --}}
                <div class="bg-surface border border-oat rounded-card">
                    <div class="px-6 py-5">
                        <h3 class="text-sm font-semibold text-off-black mb-1">Per API Key</h3>
                        <p class="text-xs text-muted mb-4">Distribusi penggunaan 14 hari</p>

                        @if($byApiKey->count() > 0)
                            @php $totalKeyReq = max(1, $byApiKey->sum('requests')); @endphp

                            {{-- Stacked bar --}}
                            <div class="flex rounded-full overflow-hidden h-5 mb-4">
                                @foreach($byApiKey->values() as $i => $keyData)
                                    <div style="width: {{ ($keyData['requests'] / $totalKeyReq) * 100 }}%; background-color: {{ $chartColors[$i % count($chartColors)] }};"
                                         title="{{ $keyData['name'] }}: {{ $keyData['requests'] }} requests"></div>
                                @endforeach
                            </div>

                            {{-- Legend --}}
                            <div class="space-y-2.5">
                                @foreach($byApiKey->values() as $i => $keyData)
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="w-3 h-3 rounded-full flex-shrink-0" style="background-color: {{ $chartColors[$i % count($chartColors)] }};"></span>
                                            <span class="text-sm text-off-black truncate">{{ $keyData['name'] }}</span>
                                        </div>
                                        <div class="text-right flex-shrink-0 ml-2">
                                            <span class="text-sm font-medium text-off-black">{{ $keyData['requests'] }}</span>
                                            <span class="text-xs text-warm-sand ml-1">req</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="flex items-center justify-center h-32 text-sm text-warm-sand">Belum ada data</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Usage by Model (horizontal bars) --}}
            <div class="bg-surface border border-oat rounded-card">
                <div class="px-6 py-5">
                    <h3 class="text-sm font-semibold text-off-black mb-1">Penggunaan per Model (14 Hari)</h3>
                    <p class="text-xs text-muted mb-4">Breakdown requests, tokens, dan biaya per model AI</p>

                    @if($byModel->count() > 0)
                        @php $maxModelCost = max(1, $byModel->max('cost')); @endphp
                        <div class="space-y-3">
                            @foreach($byModel as $modelName => $data)
                                @php $barPct = ($data['cost'] / $maxModelCost) * 100; @endphp
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-sm font-medium text-off-black">{{ $modelName }}</span>
                                        <div class="flex items-center gap-4 text-xs text-muted">
                                            <span>{{ $data['requests'] }} req</span>
                                            <span>{{ usageFormatTokens($data['tokens']) }} tokens</span>
                                            <span class="font-semibold text-off-black">{{ usageFormatRp($data['cost']) }}</span>
                                        </div>
                                    </div>
                                    <div class="w-full bg-canvas rounded-full h-2.5">
                                        <div class="bg-fin-orange h-2.5 rounded-full transition-all duration-500" style="width: {{ max($barPct, 1) }}%;"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex items-center justify-center h-24 text-sm text-warm-sand">Belum ada data</div>
                    @endif
                </div>
            </div>

            {{-- Filters --}}
            <div class="bg-surface border border-oat rounded-card">
                <div class="px-6 py-4">
                    <h3 class="text-sm font-semibold text-off-black mb-3">Filter Log</h3>
                    <form method="GET" action="{{ route('usage.index') }}" class="flex flex-wrap items-end gap-3">
                        <div>
                            <label for="date_from" class="block text-xs font-medium text-off-black mb-1">Dari Tanggal</label>
                            <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}"
                                class="block rounded-btn border-oat text-sm shadow-sm focus:border-fin-orange focus:ring-fin-orange">
                        </div>
                        <div>
                            <label for="date_to" class="block text-xs font-medium text-off-black mb-1">Sampai Tanggal</label>
                            <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}"
                                class="block rounded-btn border-oat text-sm shadow-sm focus:border-fin-orange focus:ring-fin-orange">
                        </div>
                        <div>
                            <label for="model" class="block text-xs font-medium text-off-black mb-1">Model</label>
                            <select name="model" id="model"
                                class="block rounded-btn border-oat text-sm shadow-sm focus:border-fin-orange focus:ring-fin-orange">
                                <option value="">Semua Model</option>
                                @foreach($models as $m)
                                    <option value="{{ $m }}" {{ request('model') === $m ? 'selected' : '' }}>{{ $m }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="api_key_id" class="block text-xs font-medium text-off-black mb-1">API Key</label>
                            <select name="api_key_id" id="api_key_id"
                                class="block rounded-btn border-oat text-sm shadow-sm focus:border-fin-orange focus:ring-fin-orange">
                                <option value="">Semua Key</option>
                                @foreach($apiKeys as $key)
                                    <option value="{{ $key->id }}" {{ request('api_key_id') == $key->id ? 'selected' : '' }}>{{ $key->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="submit"
                                class="inline-flex items-center rounded-btn bg-off-black px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-off-black/90 transition-colors">
                                <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                                </svg>
                                Filter
                            </button>
                            <a href="{{ route('usage.index') }}"
                                class="inline-flex items-center rounded-btn border border-oat bg-surface px-4 py-2 text-sm font-medium text-off-black shadow-sm hover:bg-canvas transition-colors">
                                Reset
                            </a>
                            <a href="{{ route('usage.export', request()->query()) }}"
                                class="inline-flex items-center rounded-btn border border-oat bg-surface px-4 py-2 text-sm font-medium text-off-black shadow-sm hover:bg-canvas transition-colors">
                                <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Export CSV
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Usage Table --}}
            <div class="bg-surface border border-oat rounded-card">
                <div class="px-6 py-4 border-b border-oat">
                    <h3 class="text-sm font-semibold text-off-black">Detail Log Penggunaan</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-oat">
                        <thead class="bg-canvas">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted">Waktu</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted">Model</th>
                                <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-muted">Input</th>
                                <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-muted">Output</th>
                                <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-muted">Total</th>
                                <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-muted">Biaya</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted">API Key</th>
                                <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-muted">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-muted">Response</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-oat bg-surface">
                            @forelse($usages as $usage)
                                <tr class="hover:bg-canvas">
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-muted">
                                        {{ $usage->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-off-black">
                                        {{ $usage->model }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-muted">
                                        {{ number_format($usage->input_tokens) }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-muted">
                                        {{ number_format($usage->output_tokens) }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-medium text-off-black">
                                        {{ number_format($usage->total_tokens) }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-sm {{ $usage->cost_idr > 0 ? 'text-fin-orange font-medium' : 'text-warm-sand' }}">
                                        {{ $usage->cost_idr > 0 ? usageFormatRp($usage->cost_idr) : '-' }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-muted">
                                        <code class="text-xs font-mono">{{ $usage->apiKey->masked_key ?? '-' }}</code>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-center">
                                        @if($usage->status_code >= 200 && $usage->status_code < 300)
                                            <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">{{ $usage->status_code }}</span>
                                        @elseif($usage->status_code >= 400)
                                            <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">{{ $usage->status_code }}</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-canvas px-2.5 py-0.5 text-xs font-medium text-off-black">{{ $usage->status_code }}</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-muted">
                                        {{ number_format($usage->response_time_ms) }}ms
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-8 text-center text-sm text-warm-sand">
                                        Tidak ada data penggunaan yang ditemukan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($usages->hasPages())
                    <div class="border-t border-oat px-6 py-4">
                        {{ $usages->withQueryString()->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
