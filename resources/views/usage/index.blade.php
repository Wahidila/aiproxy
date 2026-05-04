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

            {{-- Quick Date Range Filter --}}
            <div class="flex items-center gap-2">
                <span class="text-xs font-medium text-muted uppercase tracking-wide mr-1">Range:</span>
                @foreach(['7d' => '7d', '14d' => '14d', '30d' => '30d', '90d' => '90d'] as $rangeKey => $rangeLabel)
                    <a href="{{ route('usage.index', array_merge(request()->except('range', 'page'), ['range' => $rangeKey])) }}"
                       class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-btn border transition-colors btn-intercom
                              {{ $activeRange === $rangeKey
                                  ? 'bg-off-black text-white border-off-black'
                                  : 'bg-surface text-muted border-oat hover:text-off-black hover:border-off-black' }}">
                        {{ $rangeLabel }}
                    </a>
                @endforeach
            </div>

            {{-- Summary Stats --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-surface border border-oat rounded-card p-5">
                    <p class="text-xs font-medium text-muted uppercase">Total Requests ({{ $activeRange }})</p>
                    <p class="mt-1 text-2xl font-bold text-off-black">{{ number_format($summary['total_requests']) }}</p>
                </div>
                <div class="bg-surface border border-oat rounded-card p-5">
                    <p class="text-xs font-medium text-muted uppercase">Total Tokens ({{ $activeRange }})</p>
                    <p class="mt-1 text-2xl font-bold text-off-black">{{ usageFormatTokens($summary['total_tokens']) }}</p>
                </div>
                <div class="bg-surface border border-oat rounded-card p-5">
                    <p class="text-xs font-medium text-muted uppercase">Total Biaya ({{ $activeRange }})</p>
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
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-sm font-semibold text-off-black mb-1">Penggunaan Harian ({{ $rangeDays }} Hari)</h3>
                                <p class="text-xs text-muted">Metrik per hari</p>
                            </div>
                            {{-- Chart Metric Toggle --}}
                            <div class="flex items-center gap-1 bg-canvas border border-oat rounded-btn p-0.5" id="chart-toggle">
                                <button type="button" onclick="toggleChart('requests')" id="toggle-requests"
                                    class="px-3 py-1 text-xs font-medium rounded-btn transition-colors bg-off-black text-white">
                                    Requests
                                </button>
                                <button type="button" onclick="toggleChart('tokens')" id="toggle-tokens"
                                    class="px-3 py-1 text-xs font-medium rounded-btn transition-colors text-muted hover:text-off-black">
                                    Tokens
                                </button>
                                <button type="button" onclick="toggleChart('cost')" id="toggle-cost"
                                    class="px-3 py-1 text-xs font-medium rounded-btn transition-colors text-muted hover:text-off-black">
                                    Cost
                                </button>
                            </div>
                        </div>

                        @php
                            $maxReq = max(1, max(array_column($dailyChart, 'requests')));
                            $maxTokens = max(1, max(array_column($dailyChart, 'tokens')));
                            $maxCost = max(1, max(array_column($dailyChart, 'cost')));
                            $chartJson = json_encode(collect($dailyChart)->map(function($day, $date) use ($maxReq, $maxTokens, $maxCost) {
                                return [
                                    'date' => \Carbon\Carbon::parse($date)->format('d M Y'),
                                    'label' => \Carbon\Carbon::parse($date)->format('d/m'),
                                    'requests' => $day['requests'],
                                    'tokens' => $day['tokens'],
                                    'cost' => $day['cost'],
                                    'reqPct' => $day['requests'] > 0 ? max(round(($day['requests'] / $maxReq) * 100, 1), 4) : 0,
                                    'tokensPct' => $day['tokens'] > 0 ? max(round(($day['tokens'] / $maxTokens) * 100, 1), 4) : 0,
                                    'costPct' => $day['cost'] > 0 ? max(round(($day['cost'] / $maxCost) * 100, 1), 4) : 0,
                                    'reqFmt' => number_format($day['requests']),
                                    'tokensFmt' => usageFormatTokens($day['tokens']),
                                    'costFmt' => usageFormatRp($day['cost']),
                                ];
                            })->values());
                        @endphp

                        {{-- Chart rendered via JS for proper responsiveness --}}
                        <div id="usage-chart" class="relative w-full" style="height: 200px;"></div>

                        {{-- Tooltip (fixed position, managed by JS) --}}
                        <div id="chart-tooltip" class="fixed z-[99999] pointer-events-none opacity-0 transition-opacity duration-100 hidden">
                            <div class="bg-gray-900 text-white text-xs rounded-lg px-3 py-2 shadow-xl whitespace-nowrap">
                                <p class="font-semibold" id="tt-date"></p>
                                <p id="tt-line1"></p>
                                <p id="tt-line2"></p>
                                <p id="tt-line3"></p>
                            </div>
                            <div class="w-2 h-2 bg-gray-900 rotate-45 absolute -bottom-1 left-1/2 -translate-x-1/2"></div>
                        </div>

                        <div class="flex items-center gap-4 mt-3 text-xs text-muted" id="chart-legend">
                            <span id="legend-requests" class="flex items-center gap-1"><span class="w-3 h-3 rounded-sm inline-block bg-[#ff5600]"></span> Requests</span>
                            <span id="legend-tokens" class="flex items-center gap-1 hidden"><span class="w-3 h-3 rounded-sm inline-block bg-[#65b5ff]"></span> Tokens</span>
                            <span id="legend-cost" class="flex items-center gap-1 hidden"><span class="w-3 h-3 rounded-sm inline-block bg-[#10b981]"></span> Cost (IDR)</span>
                        </div>
                    </div>
                </div>

                <script>
                (function() {
                    var DATA = {!! $chartJson !!};
                    var COLORS = { requests: '#ff5600', tokens: '#65b5ff', cost: '#10b981' };
                    var LABELS = { requests: 'requests', tokens: 'tokens', cost: '' };
                    var activeMetric = 'requests';
                    var pctKey = { requests: 'reqPct', tokens: 'tokensPct', cost: 'costPct' };
                    var fmtKey = { requests: 'reqFmt', tokens: 'tokensFmt', cost: 'costFmt' };

                    var chart = document.getElementById('usage-chart');
                    var tooltip = document.getElementById('chart-tooltip');
                    var ttDate = document.getElementById('tt-date');
                    var ttLine1 = document.getElementById('tt-line1');
                    var ttLine2 = document.getElementById('tt-line2');
                    var ttLine3 = document.getElementById('tt-line3');

                    function render() {
                        chart.innerHTML = '';
                        // Grid container: Y-axis labels + bars area
                        var wrap = document.createElement('div');
                        wrap.className = 'flex h-full gap-2';
                        wrap.style.minWidth = '0';

                        // Y-axis (5 ticks)
                        var yAxis = document.createElement('div');
                        yAxis.className = 'flex flex-col justify-between text-[10px] text-muted text-right shrink-0 pb-5';
                        yAxis.style.width = '36px';
                        var maxVal = Math.max.apply(null, DATA.map(function(d) { return d[activeMetric]; })) || 1;
                        for (var t = 4; t >= 0; t--) {
                            var tick = document.createElement('span');
                            var v = Math.round(maxVal * t / 4);
                            if (activeMetric === 'cost') {
                                tick.textContent = v >= 1000000 ? (v/1000000).toFixed(1)+'M' : v >= 1000 ? Math.round(v/1000)+'K' : v;
                            } else if (v >= 1000000) {
                                tick.textContent = (v/1000000).toFixed(1)+'M';
                            } else if (v >= 1000) {
                                tick.textContent = Math.round(v/1000)+'K';
                            } else {
                                tick.textContent = v;
                            }
                            yAxis.appendChild(tick);
                        }
                        wrap.appendChild(yAxis);

                        // Bars area
                        var barsWrap = document.createElement('div');
                        barsWrap.className = 'flex-1 flex flex-col overflow-hidden';
                        barsWrap.style.minWidth = '0';

                        // Grid lines + bars
                        var barsArea = document.createElement('div');
                        barsArea.className = 'relative flex-1 border-b border-oat';

                        // Horizontal grid lines
                        for (var g = 1; g <= 3; g++) {
                            var line = document.createElement('div');
                            line.className = 'absolute w-full border-t border-dashed border-oat/50';
                            line.style.bottom = (g * 25) + '%';
                            barsArea.appendChild(line);
                        }

                        // Bars container — use CSS grid for equal distribution
                        var barsRow = document.createElement('div');
                        barsRow.style.cssText = 'position:absolute;inset:0;display:grid;align-items:end;grid-template-columns:repeat(' + DATA.length + ',1fr);gap:' + (DATA.length > 60 ? '0px' : DATA.length > 30 ? '1px' : '2px') + ';padding:0 1px;';

                        var pk = pctKey[activeMetric];
                        var color = COLORS[activeMetric];

                        DATA.forEach(function(d, i) {
                            var bar = document.createElement('div');
                            bar.style.cssText = 'width:100%;border-radius:2px 2px 0 0;transition:all 0.3s;cursor:pointer;background-color:' + color + ';height:' + (d[pk] > 0 ? d[pk] + '%' : '0%') + ';opacity:0.8;min-height:' + (d[pk] > 0 ? '2px' : '0') + ';';

                            bar.addEventListener('mouseenter', function(e) {
                                bar.style.opacity = '1';
                                ttDate.textContent = d.date;
                                ttLine1.textContent = d.reqFmt + ' requests';
                                ttLine2.textContent = d.tokensFmt + ' tokens';
                                ttLine3.textContent = d.costFmt;
                                tooltip.classList.remove('hidden');
                                requestAnimationFrame(function() { tooltip.style.opacity = '1'; });
                            });
                            bar.addEventListener('mousemove', function(e) {
                                var tw = tooltip.offsetWidth;
                                var th = tooltip.offsetHeight;
                                var x = e.clientX - tw / 2;
                                var y = e.clientY - th - 14;
                                if (x < 4) x = 4;
                                if (x + tw > window.innerWidth - 4) x = window.innerWidth - tw - 4;
                                if (y < 4) y = e.clientY + 16;
                                tooltip.style.left = x + 'px';
                                tooltip.style.top = y + 'px';
                            });
                            bar.addEventListener('mouseleave', function() {
                                bar.style.opacity = '0.8';
                                tooltip.style.opacity = '0';
                                setTimeout(function() { tooltip.classList.add('hidden'); }, 100);
                            });

                            barsRow.appendChild(bar);
                        });

                        barsArea.appendChild(barsRow);
                        barsWrap.appendChild(barsArea);

                        // X-axis labels
                        var xAxis = document.createElement('div');
                        xAxis.style.cssText = 'display:grid;grid-template-columns:repeat(' + DATA.length + ',1fr);gap:' + (DATA.length > 60 ? '0px' : DATA.length > 30 ? '1px' : '2px') + ';padding:4px 1px 0;';

                        // Show every Nth label if too many bars
                        var step = DATA.length > 60 ? 5 : (DATA.length > 30 ? 3 : (DATA.length > 14 ? 2 : 1));
                        DATA.forEach(function(d, i) {
                            var lbl = document.createElement('span');
                            lbl.className = 'text-center text-[10px] text-muted leading-none overflow-hidden';
                            lbl.style.minWidth = '0';
                            lbl.textContent = (i % step === 0) ? d.label : '';
                            xAxis.appendChild(lbl);
                        });

                        barsWrap.appendChild(xAxis);
                        wrap.appendChild(barsWrap);
                        chart.appendChild(wrap);
                    }

                    // Toggle function (global)
                    window.toggleChart = function(metric) {
                        activeMetric = metric;
                        ['requests', 'tokens', 'cost'].forEach(function(m) {
                            var btn = document.getElementById('toggle-' + m);
                            var leg = document.getElementById('legend-' + m);
                            if (m === metric) {
                                btn.className = 'px-3 py-1 text-xs font-medium rounded-btn transition-colors bg-off-black text-white';
                                leg.classList.remove('hidden');
                            } else {
                                btn.className = 'px-3 py-1 text-xs font-medium rounded-btn transition-colors text-muted hover:text-off-black';
                                leg.classList.add('hidden');
                            }
                        });
                        render();
                    };

                    // Initial render
                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', render);
                    } else {
                        render();
                    }

                    // Re-render on resize for responsiveness
                    var resizeTimer;
                    window.addEventListener('resize', function() {
                        clearTimeout(resizeTimer);
                        resizeTimer = setTimeout(render, 150);
                    });
                })();
                </script>

                {{-- Usage by API Key --}}
                <div class="bg-surface border border-oat rounded-card">
                    <div class="px-6 py-5">
                        <h3 class="text-sm font-semibold text-off-black mb-1">Per API Key</h3>
                        <p class="text-xs text-muted mb-4">Distribusi penggunaan {{ $rangeDays }} hari</p>

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
                    <h3 class="text-sm font-semibold text-off-black mb-1">Penggunaan per Model ({{ $rangeDays }} Hari)</h3>
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
                                class="block rounded-btn border-oat text-sm focus:border-fin-orange focus:ring-fin-orange">
                        </div>
                        <div>
                            <label for="date_to" class="block text-xs font-medium text-off-black mb-1">Sampai Tanggal</label>
                            <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}"
                                class="block rounded-btn border-oat text-sm focus:border-fin-orange focus:ring-fin-orange">
                        </div>
                        <div>
                            <label for="model" class="block text-xs font-medium text-off-black mb-1">Model</label>
                            <select name="model" id="model"
                                class="block rounded-btn border-oat text-sm focus:border-fin-orange focus:ring-fin-orange">
                                <option value="">Semua Model</option>
                                @foreach($models as $m)
                                    <option value="{{ $m }}" {{ request('model') === $m ? 'selected' : '' }}>{{ $m }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="api_key_id" class="block text-xs font-medium text-off-black mb-1">API Key</label>
                            <select name="api_key_id" id="api_key_id"
                                class="block rounded-btn border-oat text-sm focus:border-fin-orange focus:ring-fin-orange">
                                <option value="">Semua Key</option>
                                @foreach($apiKeys as $key)
                                    <option value="{{ $key->id }}" {{ request('api_key_id') == $key->id ? 'selected' : '' }}>{{ $key->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="submit"
                                class="inline-flex items-center rounded-btn bg-off-black px-4 py-2 text-sm font-medium text-white hover:bg-off-black/90 btn-intercom transition-colors">
                                <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                                </svg>
                                Filter
                            </button>
                            <a href="{{ route('usage.index') }}"
                                class="inline-flex items-center rounded-btn border border-oat bg-surface px-4 py-2 text-sm font-medium text-off-black hover:bg-canvas btn-intercom transition-colors">
                                Reset
                            </a>
                            <a href="{{ route('usage.export', request()->query()) }}"
                                class="inline-flex items-center rounded-btn border border-oat bg-surface px-4 py-2 text-sm font-medium text-off-black hover:bg-canvas btn-intercom transition-colors">
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
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-sm {{ optional($usage->apiKey)->tier === 'subscription' ? 'text-purple-600 font-medium' : ($usage->cost_idr > 0 ? 'text-fin-orange font-medium' : 'text-warm-sand') }}">
                                        @if(optional($usage->apiKey)->tier === 'subscription')
                                            Subscription
                                        @else
                                            {{ $usage->cost_idr > 0 ? usageFormatRp($usage->cost_idr) : '-' }}
                                        @endif
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

    {{--
    ============================================================
    IMPROVEMENT SUGGESTIONS FOR USAGE PAGE
    ============================================================

    1. DATE RANGE QUICK FILTERS
       - Add preset buttons: "7 hari", "14 hari", "30 hari", "Bulan ini"
       - Reduces friction vs manual date picker input
       - Controller already supports date_from/date_to, just needs UI buttons

    2. CHART TOGGLE (Requests vs Tokens vs Cost)
       - Allow user to switch the bar chart Y-axis metric
       - Currently shows requests + cost simultaneously which can be confusing
       - A simple tab/toggle above the chart would improve clarity

    3. REAL-TIME COST ALERT / BUDGET THRESHOLD
       - Add a configurable daily/monthly budget threshold
       - Show a warning banner when usage approaches the limit
       - Helps users avoid unexpected costs

    4. SPARKLINE IN SUMMARY CARDS
       - Add tiny inline sparklines (CSS-only) in the 4 summary stat cards
       - Shows trend direction at a glance without needing to read the chart

    5. MODEL COMPARISON VIEW
       - Side-by-side cost/performance comparison between models
       - Help users decide which model gives best value
       - Could show cost-per-1K-tokens and avg response time per model

    6. PAGINATION INFO ON TABLE
       - Show "Menampilkan 1-25 dari 1,234 hasil" above the table
       - Gives user context about total data volume

    7. EMPTY STATE IMPROVEMENT
       - When no data exists, show an onboarding CTA
       - e.g., "Belum ada data penggunaan. Buat API key pertama Anda!"
       - Link to API key creation page

    8. EXPORT FORMAT OPTIONS
       - Add JSON export alongside CSV
       - Add date range to export filename for clarity

    9. CACHE CHART DATA
       - The controller queries all 14-day usages on every page load
       - Consider caching dailyChart/byModel/byApiKey for 5 minutes
       - Use Cache::remember() with user-specific key

    10. ACCESSIBILITY
        - Add aria-label to bar chart bars for screen readers
        - Add role="img" and aria-label to the chart container
        - Ensure color is not the only differentiator (add patterns or labels)
    ============================================================
    --}}
</x-app-layout>
