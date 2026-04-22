<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-off-black tracking-heading">
                    {{ __('Subscription Plans') }}
                </h2>
                <nav class="mt-1 text-sm text-muted">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-fin-orange">Admin</a>
                    <span class="mx-1">/</span>
                    <span>Subscriptions</span>
                    <span class="mx-1">/</span>
                    <span class="text-off-black font-medium">Plans</span>
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

            {{-- Action Bar --}}
            <div class="flex items-center justify-end">
                <a href="{{ route('admin.subscription-plans.create') }}"
                   class="inline-flex items-center rounded-btn bg-fin-orange px-4 py-2 text-sm font-medium text-white hover:bg-fin-orange-hover focus:outline-none focus:ring-2 focus:ring-fin-orange focus:ring-offset-2 btn-intercom transition-colors">
                    <i data-lucide="plus" class="mr-1.5 h-4 w-4"></i>
                    Create Plan
                </a>
            </div>

            {{-- Plans Table --}}
            <div class="bg-surface border border-oat rounded-card">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-oat">
                        <thead class="bg-canvas">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted">Name</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted">Slug</th>
                                <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-muted">Price</th>
                                <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-muted">RPM</th>
                                <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-muted">Parallel</th>
                                <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-muted">Budget/Cycle</th>
                                <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-muted">Cycle Hours</th>
                                <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-muted">Status</th>
                                <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-muted">Subscribers</th>
                                <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-muted">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-oat bg-surface">
                            @forelse($plans as $plan)
                                <tr class="hover:bg-canvas">
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <span class="text-sm font-medium text-off-black">{{ $plan->name }}</span>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <span class="text-sm font-mono text-muted">{{ $plan->slug }}</span>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right">
                                        <span class="text-sm font-medium text-off-black">Rp {{ number_format($plan->price_idr, 0, ',', '.') }}</span>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-center text-sm text-muted">
                                        {{ $plan->rpm_limit }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-center text-sm text-muted">
                                        {{ $plan->parallel_limit }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right">
                                        <span class="text-sm text-off-black">${{ number_format($plan->budget_usd_per_cycle, 2) }}</span>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-center text-sm text-muted">
                                        {{ $plan->cycle_hours }}h
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-center">
                                        @if($plan->is_active)
                                            <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700">
                                                Active
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-700">
                                                Inactive
                                            </span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-center text-sm text-muted">
                                        {{ $plan->subscribers_count ?? 0 }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-center">
                                        <div class="flex items-center justify-center space-x-2">
                                            <a href="{{ route('admin.subscription-plans.edit', $plan) }}"
                                               class="inline-flex items-center rounded-btn bg-fin-orange-light px-2.5 py-1.5 text-xs font-medium text-fin-orange hover:bg-fin-orange-light/80 transition-colors">
                                                <i data-lucide="pencil" class="mr-1 h-3.5 w-3.5"></i>
                                                Edit
                                            </a>
                                            <form action="{{ route('admin.subscription-plans.destroy', $plan) }}"
                                                  method="POST"
                                                  class="inline"
                                                  onsubmit="return confirm('Are you sure you want to delete the plan \'{{ $plan->name }}\'?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="inline-flex items-center rounded-btn bg-red-50 px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-100 transition-colors">
                                                    <i data-lucide="trash-2" class="mr-1 h-3.5 w-3.5"></i>
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-4 py-8 text-center text-sm text-muted">
                                        No subscription plans found. Create your first plan to get started.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
