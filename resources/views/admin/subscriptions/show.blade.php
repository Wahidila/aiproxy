<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-off-black tracking-heading">
                    {{ __('Subscription #' . $subscription->id) }}
                </h2>
                <nav class="mt-1 text-sm text-muted">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-fin-orange">Admin</a>
                    <span class="mx-1">/</span>
                    <a href="{{ route('admin.subscriptions.index') }}" class="hover:text-fin-orange">Subscriptions</a>
                    <span class="mx-1">/</span>
                    <span class="text-off-black font-medium">#{{ $subscription->id }}</span>
                </nav>
            </div>
            <span class="inline-flex items-center rounded-md bg-orange-100 px-2.5 py-1 text-xs font-medium text-orange-700 ring-1 ring-inset ring-orange-600/20">
                Admin
            </span>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="rounded-card border border-green-200 bg-green-50 p-4">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            @endif
            @if(session('error'))
                <div class="rounded-card border border-red-200 bg-red-50 p-4">
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
            @endif

            {{-- Subscription Info & Actions Row --}}
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

                {{-- Info Card (2 cols) --}}
                <div class="lg:col-span-2 bg-surface border border-oat rounded-card">
                    <div class="px-6 py-5">
                        <div class="flex items-center gap-2 mb-4">
                            <i data-lucide="file-text" class="h-5 w-5 text-muted"></i>
                            <h3 class="text-lg font-semibold text-off-black tracking-sub">Subscription Details</h3>
                        </div>

                        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            {{-- User --}}
                            <div>
                                <dt class="text-xs font-medium text-muted uppercase tracking-wider">User</dt>
                                <dd class="mt-1 flex items-center gap-2">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-fin-orange-light text-xs font-semibold text-fin-orange">
                                        {{ strtoupper(substr($subscription->user->name ?? '?', 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-off-black">{{ $subscription->user->name ?? '-' }}</p>
                                        <p class="text-xs text-muted">{{ $subscription->user->email ?? '-' }}</p>
                                    </div>
                                </dd>
                            </div>

                            {{-- Plan --}}
                            <div>
                                <dt class="text-xs font-medium text-muted uppercase tracking-wider">Plan</dt>
                                <dd class="mt-1 text-sm font-medium text-off-black">
                                    {{ $subscription->plan->name ?? $subscription->plan_id }}
                                </dd>
                            </div>

                            {{-- Status --}}
                            <div>
                                <dt class="text-xs font-medium text-muted uppercase tracking-wider">Status</dt>
                                <dd class="mt-1">
                                    @switch($subscription->status)
                                        @case('pending')
                                            <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-700">
                                                Pending
                                            </span>
                                            @break
                                        @case('active')
                                            <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700">
                                                Active
                                            </span>
                                            @break
                                        @case('expired')
                                            <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-700">
                                                Expired
                                            </span>
                                            @break
                                        @case('cancelled')
                                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700">
                                                Cancelled
                                            </span>
                                            @break
                                        @default
                                            <span class="inline-flex items-center rounded-full bg-canvas px-2.5 py-0.5 text-xs font-medium text-muted">
                                                {{ ucfirst($subscription->status) }}
                                            </span>
                                    @endswitch
                                </dd>
                            </div>

                            {{-- Starts At --}}
                            <div>
                                <dt class="text-xs font-medium text-muted uppercase tracking-wider">Starts At</dt>
                                <dd class="mt-1 text-sm text-off-black">
                                    {{ $subscription->starts_at ? $subscription->starts_at->format('d M Y H:i') : '-' }}
                                </dd>
                            </div>

                            {{-- Expires At --}}
                            <div>
                                <dt class="text-xs font-medium text-muted uppercase tracking-wider">Expires At</dt>
                                <dd class="mt-1 text-sm text-off-black">
                                    {{ $subscription->expires_at ? $subscription->expires_at->format('d M Y H:i') : '-' }}
                                </dd>
                            </div>

                            {{-- Approved By --}}
                            <div>
                                <dt class="text-xs font-medium text-muted uppercase tracking-wider">Approved By</dt>
                                <dd class="mt-1 text-sm text-off-black">
                                    {{ $subscription->approvedBy->name ?? '-' }}
                                </dd>
                            </div>

                            {{-- Notes --}}
                            <div class="sm:col-span-2">
                                <dt class="text-xs font-medium text-muted uppercase tracking-wider">Notes</dt>
                                <dd class="mt-1 text-sm text-muted">
                                    {{ $subscription->notes ?? 'No notes.' }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                {{-- Action Buttons Card (1 col) --}}
                <div class="bg-surface border border-oat rounded-card">
                    <div class="px-6 py-5">
                        <div class="flex items-center gap-2 mb-4">
                            <i data-lucide="settings" class="h-5 w-5 text-muted"></i>
                            <h3 class="text-lg font-semibold text-off-black tracking-sub">Actions</h3>
                        </div>

                        <div class="space-y-4">
                            @if($subscription->status === 'pending')
                                {{-- Approve --}}
                                <form method="POST" action="{{ route('admin.subscriptions.approve', $subscription) }}">
                                    @csrf
                                    <button type="submit"
                                            class="w-full inline-flex items-center justify-center rounded-btn bg-green-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors">
                                        <i data-lucide="check" class="mr-1.5 h-4 w-4"></i>
                                        Approve Subscription
                                    </button>
                                </form>

                                {{-- Reject --}}
                                <form method="POST" action="{{ route('admin.subscriptions.reject', $subscription) }}">
                                    @csrf
                                    <button type="submit"
                                            onclick="return confirm('Are you sure you want to reject this subscription?')"
                                            class="w-full inline-flex items-center justify-center rounded-btn bg-white px-4 py-2.5 text-sm font-medium text-red-600 ring-1 ring-inset ring-red-300 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors">
                                        <i data-lucide="x" class="mr-1.5 h-4 w-4"></i>
                                        Reject Subscription
                                    </button>
                                </form>
                            @endif

                            @if($subscription->status === 'active')
                                {{-- Extend --}}
                                <form method="POST" action="{{ route('admin.subscriptions.extend', $subscription) }}" class="space-y-3">
                                    @csrf
                                    <div>
                                        <label for="days" class="block text-xs font-medium text-muted mb-1.5">Extend by (days)</label>
                                        <input type="number"
                                               id="days"
                                               name="days"
                                               min="1"
                                               required
                                               placeholder="e.g. 30"
                                               class="w-full rounded-btn border-oat text-sm focus:border-fin-orange focus:ring-fin-orange">
                                    </div>
                                    <button type="submit"
                                            class="w-full inline-flex items-center justify-center rounded-btn bg-fin-orange px-4 py-2.5 text-sm font-medium text-white hover:bg-fin-orange-hover focus:outline-none focus:ring-2 focus:ring-fin-orange focus:ring-offset-2 transition-colors">
                                        <i data-lucide="calendar-plus" class="mr-1.5 h-4 w-4"></i>
                                        Extend Subscription
                                    </button>
                                </form>

                                {{-- Cancel --}}
                                <form method="POST" action="{{ route('admin.subscriptions.cancel', $subscription) }}">
                                    @csrf
                                    <button type="submit"
                                            onclick="return confirm('Are you sure you want to cancel this subscription?')"
                                            class="w-full inline-flex items-center justify-center rounded-btn bg-white px-4 py-2.5 text-sm font-medium text-red-600 ring-1 ring-inset ring-red-300 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors">
                                        <i data-lucide="ban" class="mr-1.5 h-4 w-4"></i>
                                        Cancel Subscription
                                    </button>
                                </form>
                            @endif

                            @if(in_array($subscription->status, ['expired', 'cancelled']))
                                <div class="rounded-lg border border-oat bg-canvas p-4 text-center">
                                    <i data-lucide="info" class="mx-auto h-6 w-6 text-muted mb-2"></i>
                                    <p class="text-sm text-muted">
                                        This subscription is <span class="font-medium">{{ $subscription->status }}</span>. No actions available.
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Budget Card --}}
            <div class="bg-surface border border-oat rounded-card">
                <div class="px-6 py-5">
                    <div class="flex items-center gap-2 mb-4">
                        <i data-lucide="wallet" class="h-5 w-5 text-muted"></i>
                        <h3 class="text-lg font-semibold text-off-black tracking-sub">Budget & Usage</h3>
                    </div>

                    @php
                        $budgetLimit = $subscription->plan->budget_limit ?? 0;
                        $remaining = max(0, $budgetLimit - ($cycleCost ?? 0));
                        $percentage = $budgetLimit > 0 ? min(100, (($cycleCost ?? 0) / $budgetLimit) * 100) : 0;
                    @endphp

                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4 mb-4">
                        <div class="rounded-lg bg-canvas border border-oat p-3">
                            <p class="text-xs font-medium text-muted uppercase tracking-wider">Cycle Start</p>
                            <p class="mt-1 text-sm font-bold text-off-black">
                                {{ $cycleStart ? \Carbon\Carbon::parse($cycleStart)->format('d M Y') : '-' }}
                            </p>
                        </div>
                        <div class="rounded-lg bg-canvas border border-oat p-3">
                            <p class="text-xs font-medium text-muted uppercase tracking-wider">Spent</p>
                            <p class="mt-1 text-sm font-bold text-off-black">${{ number_format($cycleCost ?? 0, 4) }}</p>
                        </div>
                        <div class="rounded-lg bg-canvas border border-oat p-3">
                            <p class="text-xs font-medium text-muted uppercase tracking-wider">Budget Limit</p>
                            <p class="mt-1 text-sm font-bold text-off-black">${{ number_format($budgetLimit, 4) }}</p>
                        </div>
                        <div class="rounded-lg bg-canvas border border-oat p-3">
                            <p class="text-xs font-medium text-muted uppercase tracking-wider">Remaining</p>
                            <p class="mt-1 text-sm font-bold {{ $remaining > 0 ? 'text-green-600' : 'text-red-600' }}">
                                ${{ number_format($remaining, 4) }}
                            </p>
                        </div>
                    </div>

                    {{-- Progress Bar --}}
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-medium text-muted">Usage</span>
                            <span class="text-xs font-medium text-off-black">{{ number_format($percentage, 1) }}%</span>
                        </div>
                        <div class="w-full bg-canvas rounded-full h-2.5 border border-oat">
                            <div class="h-2.5 rounded-full transition-all duration-300 {{ $percentage >= 90 ? 'bg-red-500' : ($percentage >= 70 ? 'bg-yellow-500' : 'bg-green-500') }}"
                                 style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- API Keys Card --}}
            <div class="bg-surface border border-oat rounded-card">
                <div class="px-6 py-5">
                    <div class="flex items-center gap-2 mb-4">
                        <i data-lucide="key" class="h-5 w-5 text-muted"></i>
                        <h3 class="text-lg font-semibold text-off-black tracking-sub">
                            API Keys
                            <span class="ml-2 inline-flex items-center rounded-full bg-canvas px-2.5 py-0.5 text-xs font-medium text-muted">
                                {{ $subscription->user->apiKeys->count() ?? 0 }}
                            </span>
                        </h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-oat">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted">Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted">Key</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-muted">Active</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted">Last Used</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted">Created</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-oat">
                                @forelse($subscription->user->apiKeys ?? [] as $apiKey)
                                    <tr class="hover:bg-canvas">
                                        <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-off-black">
                                            {{ $apiKey->name }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-sm text-muted">
                                            <code class="rounded bg-canvas px-2 py-0.5 font-mono text-xs">{{ $apiKey->masked_key }}</code>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-center">
                                            @if($apiKey->is_active)
                                                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700">
                                                    Active
                                                </span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-canvas px-2.5 py-0.5 text-xs font-medium text-muted">
                                                    Inactive
                                                </span>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-sm text-muted">
                                            {{ $apiKey->last_used_at ? $apiKey->last_used_at->format('d M Y H:i') : '-' }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-sm text-muted">
                                            {{ $apiKey->created_at->format('d M Y') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-sm text-muted">
                                            No API keys found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Recent Usage Table --}}
            <div class="bg-surface border border-oat rounded-card">
                <div class="px-6 py-5">
                    <div class="flex items-center gap-2 mb-4">
                        <i data-lucide="activity" class="h-5 w-5 text-muted"></i>
                        <h3 class="text-lg font-semibold text-off-black tracking-sub">Recent Usage</h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-oat">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted">Model</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-muted">Input Tokens</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-muted">Output Tokens</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-muted">Cost USD</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-muted">Status</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-muted">Response Time</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-oat">
                                @forelse($recentUsages as $usage)
                                    <tr class="hover:bg-canvas">
                                        <td class="whitespace-nowrap px-4 py-3 text-sm text-muted">
                                            {{ $usage->created_at->format('d/m H:i') }}
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
                                            ${{ number_format($usage->cost_usd ?? 0, 6) }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-center">
                                            @if($usage->status_code >= 200 && $usage->status_code < 300)
                                                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                                    {{ $usage->status_code }}
                                                </span>
                                            @elseif($usage->status_code >= 400)
                                                <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">
                                                    {{ $usage->status_code }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">
                                                    {{ $usage->status_code }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-muted">
                                            {{ number_format($usage->response_time_ms) }} ms
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-8 text-center text-sm text-muted">
                                            No usage data found.
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
