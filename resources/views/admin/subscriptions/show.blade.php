<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <h2 class="font-semibold text-xl text-off-black leading-tight tracking-heading">
                {{ __('User Detail') }}
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
                <a href="{{ route('admin.subscriptions.index') }}" class="hover:text-off-black">User Subscriptions</a>
                <span class="mx-2">/</span>
                <span class="text-off-black font-medium">{{ $user->name }}</span>
            </nav>

            {{-- User Info & Subscription Card --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                {{-- User Info --}}
                <div class="bg-surface border border-oat rounded-card p-5">
                    <h3 class="text-xs font-medium text-muted uppercase tracking-wide mb-3">Info User</h3>
                    <div class="space-y-2">
                        <div>
                            <p class="text-sm text-muted">Nama</p>
                            <p class="text-base font-medium text-off-black">{{ $user->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-muted">Email</p>
                            <p class="text-base font-medium text-off-black">{{ $user->email }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-muted">User ID</p>
                            <p class="text-base font-mono text-off-black">#{{ $user->id }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-muted">Bergabung</p>
                            <p class="text-base text-off-black">{{ $user->created_at->format('d M Y') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Subscription Info --}}
                <div class="bg-surface border border-oat rounded-card p-5">
                    <h3 class="text-xs font-medium text-muted uppercase tracking-wide mb-3">Subscription</h3>
                    <div class="space-y-2">
                        <div>
                            <p class="text-sm text-muted">Plan Aktif</p>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                {{ $plan->slug === 'free' ? 'bg-gray-100 text-gray-800' : ($plan->slug === 'premium' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800') }}">
                                {{ $plan->name }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm text-muted">Status</p>
                            @if($subscription && $subscription->status === 'active')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                            @elseif($subscription && $subscription->status === 'expired')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Expired</span>
                            @elseif($subscription)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">{{ ucfirst($subscription->status) }}</span>
                            @else
                                <span class="text-sm text-muted">No subscription</span>
                            @endif
                        </div>
                        <div>
                            <p class="text-sm text-muted">Mulai</p>
                            <p class="text-base text-off-black">{{ $subscription && $subscription->starts_at ? $subscription->starts_at->format('d M Y H:i') : '—' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-muted">Berakhir</p>
                            <p class="text-base text-off-black">
                                @if($subscription && $subscription->expires_at)
                                    {{ $subscription->expires_at->format('d M Y H:i') }}
                                    @if($subscription->expires_at->isPast())
                                        <span class="text-xs text-red-600 font-medium">(overdue)</span>
                                    @elseif($subscription->expires_at->diffInDays(now()) <= 3)
                                        <span class="text-xs text-yellow-600 font-medium">(segera)</span>
                                    @endif
                                @else
                                    <span class="text-muted">∞ (selamanya)</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Wallet & Cost --}}
                <div class="bg-surface border border-oat rounded-card p-5">
                    <h3 class="text-xs font-medium text-muted uppercase tracking-wide mb-3">Wallet & Cost</h3>
                    <div class="space-y-2">
                        <div>
                            <p class="text-sm text-muted">Balance</p>
                            <p class="text-2xl font-semibold text-off-black">Rp {{ number_format($quota->balance ?? 0, 0, ',', '.') }}</p>
                            <p class="text-xs text-muted">Free: Rp {{ number_format($quota->free_balance ?? 0, 0, ',', '.') }} · Paid: Rp {{ number_format($quota->paid_balance ?? 0, 0, ',', '.') }}</p>
                        </div>
                        <div class="pt-2 border-t border-oat">
                            <p class="text-sm text-muted">Cost Estimation</p>
                            <div class="space-y-1 mt-1">
                                <div class="flex justify-between text-sm">
                                    <span class="text-muted">Hari ini</span>
                                    <span class="text-off-black font-medium">Rp {{ number_format($costToday, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-muted">Bulan ini</span>
                                    <span class="text-off-black font-medium">Rp {{ number_format($costThisMonth, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-muted">Total</span>
                                    <span class="text-off-black font-medium">Rp {{ number_format($totalCost, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Rate Limit Status --}}
            <div class="bg-surface border border-oat rounded-card p-5">
                <h3 class="text-xs font-medium text-muted uppercase tracking-wide mb-3">Rate Limit Status</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {{-- Per Minute --}}
                    <div class="p-3 rounded-lg border border-oat">
                        @php
                            $perMinUsed = $rateLimitStatus['requests_last_minute'];
                            $perMinLimit = $rateLimitStatus['per_minute_limit'];
                            $perMinPercent = $perMinLimit ? round(($perMinUsed / $perMinLimit) * 100) : 0;
                            $perMinColor = $perMinPercent >= 80 ? '#c41c1c' : ($perMinPercent >= 50 ? '#fe4c02' : '#0bdf50');
                        @endphp
                        <p class="text-xs text-muted mb-1">Request / Menit</p>
                        <p class="text-lg font-semibold text-off-black">{{ $perMinUsed }} / {{ $perMinLimit ?? '∞' }}</p>
                        @if($perMinLimit)
                            <div class="w-full h-2 bg-gray-100 rounded-full mt-2">
                                <div class="h-2 rounded-full" style="width: {{ min($perMinPercent, 100) }}%; background-color: {{ $perMinColor }};"></div>
                            </div>
                            @if($perMinPercent >= 80)
                                <p class="text-xs mt-1" style="color: #c41c1c;">Mendekati limit!</p>
                            @endif
                        @endif
                    </div>

                    {{-- Daily --}}
                    <div class="p-3 rounded-lg border border-oat">
                        @php
                            $dailyUsed = $rateLimitStatus['daily_requests_used'];
                            $dailyLimit = $rateLimitStatus['daily_request_limit'];
                            $dailyPercent = $dailyLimit ? round(($dailyUsed / $dailyLimit) * 100) : 0;
                            $dailyColor = $dailyPercent >= 80 ? '#c41c1c' : ($dailyPercent >= 50 ? '#fe4c02' : '#0bdf50');
                        @endphp
                        <p class="text-xs text-muted mb-1">Request / Hari</p>
                        <p class="text-lg font-semibold text-off-black">{{ $dailyUsed }} / {{ $dailyLimit ?? '∞' }}</p>
                        @if($dailyLimit)
                            <div class="w-full h-2 bg-gray-100 rounded-full mt-2">
                                <div class="h-2 rounded-full" style="width: {{ min($dailyPercent, 100) }}%; background-color: {{ $dailyColor }};"></div>
                            </div>
                            @if($dailyPercent >= 80)
                                <p class="text-xs mt-1" style="color: #c41c1c;">Mendekati limit!</p>
                            @endif
                        @endif
                    </div>

                    {{-- Token Cap --}}
                    <div class="p-3 rounded-lg border border-oat">
                        @php
                            $tokenUsed = $subscription ? $subscription->token_usage_total : 0;
                            $tokenCap = $plan->max_token_usage;
                            $tokenPercent = $tokenCap ? round(($tokenUsed / $tokenCap) * 100) : 0;
                            $tokenColor = $tokenPercent >= 80 ? '#c41c1c' : ($tokenPercent >= 50 ? '#fe4c02' : '#0bdf50');
                        @endphp
                        <p class="text-xs text-muted mb-1">Token Usage Cap</p>
                        <p class="text-lg font-semibold text-off-black">{{ number_format($tokenUsed) }} / {{ $tokenCap ? number_format($tokenCap) : '∞' }}</p>
                        @if($tokenCap)
                            <div class="w-full h-2 bg-gray-100 rounded-full mt-2">
                                <div class="h-2 rounded-full" style="width: {{ min($tokenPercent, 100) }}%; background-color: {{ $tokenColor }};"></div>
                            </div>
                            @if($tokenPercent >= 80)
                                <p class="text-xs mt-1" style="color: #c41c1c;">Mendekati limit!</p>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            {{-- Usage Trend Chart (7 days) --}}
            <div class="bg-surface border border-oat rounded-card p-5">
                <h3 class="text-xs font-medium text-muted uppercase tracking-wide mb-4">Usage Trend (7 Hari Terakhir)</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Token Chart --}}
                    <div>
                        <p class="text-sm font-medium text-off-black mb-2">Total Tokens</p>
                        <div class="flex items-end space-x-1" style="height: 120px;">
                            @foreach($usageTrend as $day)
                                @php
                                    $height = $maxTokens > 0 ? round(($day['tokens'] / $maxTokens) * 100) : 0;
                                    $barColor = $day['tokens'] > ($maxTokens * 0.8) ? '#c41c1c' : '#65b5ff';
                                @endphp
                                <div class="flex-1 flex flex-col items-center justify-end h-full">
                                    <span class="text-[10px] text-muted mb-1">{{ $day['tokens'] > 0 ? number_format($day['tokens'] / 1000, 1) . 'k' : '0' }}</span>
                                    <div class="w-full rounded-t" style="height: {{ max($height, 2) }}%; background-color: {{ $barColor }}; min-height: 2px;"></div>
                                    <span class="text-[10px] text-muted mt-1">{{ $day['label'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Request Chart --}}
                    <div>
                        <p class="text-sm font-medium text-off-black mb-2">Total Requests</p>
                        <div class="flex items-end space-x-1" style="height: 120px;">
                            @foreach($usageTrend as $day)
                                @php
                                    $height = $maxRequests > 0 ? round(($day['requests'] / $maxRequests) * 100) : 0;
                                    $barColor = $day['requests'] > ($maxRequests * 0.8) ? '#c41c1c' : '#0bdf50';
                                @endphp
                                <div class="flex-1 flex flex-col items-center justify-end h-full">
                                    <span class="text-[10px] text-muted mb-1">{{ $day['requests'] }}</span>
                                    <div class="w-full rounded-t" style="height: {{ max($height, 2) }}%; background-color: {{ $barColor }}; min-height: 2px;"></div>
                                    <span class="text-[10px] text-muted mt-1">{{ $day['label'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Token Usage Per Day & Request Per Day --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                {{-- Token Per Day --}}
                <div class="bg-surface border border-oat rounded-card p-5">
                    <h3 class="text-xs font-medium text-muted uppercase tracking-wide mb-3">Total Token Per Hari (30 Hari)</h3>
                    <div class="overflow-x-auto max-h-64 overflow-y-auto">
                        <table class="min-w-full divide-y divide-oat text-sm">
                            <thead class="bg-canvas sticky top-0">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-muted uppercase">Tanggal</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-muted uppercase">Input</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-muted uppercase">Output</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-muted uppercase">Total</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-muted uppercase">Cost</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-oat">
                                @forelse($dailyTokens as $day)
                                <tr class="hover:bg-canvas">
                                    <td class="px-3 py-2 text-off-black">{{ \Carbon\Carbon::parse($day->date)->format('d M Y') }}</td>
                                    <td class="px-3 py-2 text-right text-off-black">{{ number_format($day->total_input) }}</td>
                                    <td class="px-3 py-2 text-right text-off-black">{{ number_format($day->total_output) }}</td>
                                    <td class="px-3 py-2 text-right font-medium text-off-black">{{ number_format($day->total_tokens) }}</td>
                                    <td class="px-3 py-2 text-right text-muted">Rp {{ number_format($day->total_cost, 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-3 py-4 text-center text-muted">Belum ada data usage.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Request Per Day --}}
                <div class="bg-surface border border-oat rounded-card p-5">
                    <h3 class="text-xs font-medium text-muted uppercase tracking-wide mb-3">Total Request Per Hari (30 Hari)</h3>
                    <div class="overflow-x-auto max-h-64 overflow-y-auto">
                        <table class="min-w-full divide-y divide-oat text-sm">
                            <thead class="bg-canvas sticky top-0">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-muted uppercase">Tanggal</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-muted uppercase">Jumlah Request</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-muted uppercase">Avg Token/Req</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-oat">
                                @forelse($dailyTokens as $day)
                                <tr class="hover:bg-canvas">
                                    <td class="px-3 py-2 text-off-black">{{ \Carbon\Carbon::parse($day->date)->format('d M Y') }}</td>
                                    <td class="px-3 py-2 text-right font-medium text-off-black">{{ number_format($day->request_count) }}</td>
                                    <td class="px-3 py-2 text-right text-muted">{{ $day->request_count > 0 ? number_format($day->total_tokens / $day->request_count) : 0 }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="px-3 py-4 text-center text-muted">Belum ada data request.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Model Usage Breakdown --}}
            <div class="bg-surface border border-oat rounded-card p-5">
                <h3 class="text-xs font-medium text-muted uppercase tracking-wide mb-3">Model Usage Breakdown</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-oat text-sm">
                        <thead class="bg-canvas">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-muted uppercase">Model</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-muted uppercase">Request Count</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-muted uppercase">Total Tokens</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-muted uppercase">Total Cost</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-muted uppercase">Usage</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-oat">
                            @php $totalRequests = $modelUsage->sum('request_count'); @endphp
                            @forelse($modelUsage as $model)
                            @php
                                $modelPercent = $totalRequests > 0 ? round(($model->request_count / $totalRequests) * 100) : 0;
                            @endphp
                            <tr class="hover:bg-canvas">
                                <td class="px-3 py-2">
                                    <span class="font-mono text-off-black">{{ $model->model }}</span>
                                </td>
                                <td class="px-3 py-2 text-right font-medium text-off-black">{{ number_format($model->request_count) }}</td>
                                <td class="px-3 py-2 text-right text-off-black">{{ number_format($model->total_tokens) }}</td>
                                <td class="px-3 py-2 text-right text-muted">Rp {{ number_format($model->total_cost, 0, ',', '.') }}</td>
                                <td class="px-3 py-2">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-24 h-2 bg-gray-100 rounded-full">
                                            <div class="h-2 rounded-full" style="width: {{ $modelPercent }}%; background-color: #65b5ff;"></div>
                                        </div>
                                        <span class="text-xs text-muted">{{ $modelPercent }}%</span>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-3 py-4 text-center text-muted">Belum ada data model usage.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Recent Activity Log --}}
            <div class="bg-surface border border-oat rounded-card p-5">
                <h3 class="text-xs font-medium text-muted uppercase tracking-wide mb-3">Recent Activity (20 Request Terakhir)</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-oat text-sm">
                        <thead class="bg-canvas">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-muted uppercase">Waktu</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-muted uppercase">Model</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-muted uppercase">Input</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-muted uppercase">Output</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-muted uppercase">Total</th>
                                <th class="px-3 py-2 text-center text-xs font-medium text-muted uppercase">Status</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-muted uppercase">Response</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-muted uppercase">Cost</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-oat">
                            @forelse($recentActivity as $activity)
                            <tr class="hover:bg-canvas">
                                <td class="px-3 py-2 text-off-black whitespace-nowrap">{{ $activity->created_at->format('d M H:i:s') }}</td>
                                <td class="px-3 py-2">
                                    <span class="font-mono text-xs text-off-black">{{ $activity->model }}</span>
                                </td>
                                <td class="px-3 py-2 text-right text-off-black">{{ number_format($activity->input_tokens) }}</td>
                                <td class="px-3 py-2 text-right text-off-black">{{ number_format($activity->output_tokens) }}</td>
                                <td class="px-3 py-2 text-right font-medium text-off-black">{{ number_format($activity->total_tokens) }}</td>
                                <td class="px-3 py-2 text-center">
                                    @if($activity->status_code === 200)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium" style="background-color: #e6f9ed; color: #0a7c32;">{{ $activity->status_code }}</span>
                                    @elseif($activity->status_code >= 400)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium" style="background-color: #fde8e8; color: #c41c1c;">{{ $activity->status_code }}</span>
                                    @else
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">{{ $activity->status_code }}</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-right text-muted">{{ $activity->response_time_ms }}ms</td>
                                <td class="px-3 py-2 text-right text-muted">Rp {{ number_format($activity->cost_idr, 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="px-3 py-4 text-center text-muted">Belum ada activity.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Subscription History --}}
            <div class="bg-surface border border-oat rounded-card p-5">
                <h3 class="text-xs font-medium text-muted uppercase tracking-wide mb-3">Subscription History</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-oat text-sm">
                        <thead class="bg-canvas">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-muted uppercase">Plan</th>
                                <th class="px-3 py-2 text-center text-xs font-medium text-muted uppercase">Status</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-muted uppercase">Mulai</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-muted uppercase">Berakhir</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-muted uppercase">Token Used</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-muted uppercase">Daily Req</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-oat">
                            @foreach($subscriptionHistory as $sub)
                            <tr class="hover:bg-canvas">
                                <td class="px-3 py-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                        {{ $sub->plan_slug === 'free' ? 'bg-gray-100 text-gray-800' : ($sub->plan_slug === 'premium' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800') }}">
                                        {{ $sub->plan->name ?? $sub->plan_slug }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-center">
                                    @if($sub->status === 'active')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                                    @elseif($sub->status === 'expired')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Expired</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">{{ ucfirst($sub->status) }}</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-off-black">{{ $sub->starts_at ? $sub->starts_at->format('d M Y') : '—' }}</td>
                                <td class="px-3 py-2 text-off-black">{{ $sub->expires_at ? $sub->expires_at->format('d M Y') : '∞' }}</td>
                                <td class="px-3 py-2 text-right text-off-black">{{ number_format($sub->token_usage_total ?? 0) }}</td>
                                <td class="px-3 py-2 text-right text-off-black">{{ number_format($sub->daily_requests_used ?? 0) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Back Button --}}
            <div class="flex justify-start">
                <a href="{{ route('admin.subscriptions.index') }}" class="px-5 py-2 text-sm font-medium rounded-btn border border-oat hover:bg-canvas transition text-off-black">
                    &larr; Kembali ke Daftar Subscription
                </a>
            </div>

        </div>
    </div>
</x-app-layout>
