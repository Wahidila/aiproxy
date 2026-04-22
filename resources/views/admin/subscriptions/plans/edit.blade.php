<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-off-black tracking-heading">
                    {{ __('Edit Plan') }}
                </h2>
                <nav class="mt-1 text-sm text-muted">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-fin-orange">Admin</a>
                    <span class="mx-1">/</span>
                    <a href="{{ route('admin.subscription-plans.index') }}" class="hover:text-fin-orange">Subscription Plans</a>
                    <span class="mx-1">/</span>
                    <span class="text-off-black font-medium">Edit: {{ $plan->name }}</span>
                </nav>
            </div>
            <span class="inline-flex items-center rounded-md bg-orange-100 px-2.5 py-1 text-xs font-medium text-orange-700 ring-1 ring-inset ring-orange-600/20">
                Admin
            </span>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

            {{-- Validation Errors --}}
            @if($errors->any())
                <div class="rounded-card border border-red-200 bg-red-50 p-4">
                    <div class="flex items-start gap-3">
                        <i data-lucide="alert-circle" class="h-5 w-5 text-red-600 mt-0.5 shrink-0"></i>
                        <div>
                            <h3 class="text-sm font-medium text-red-800">There were errors with your submission</h3>
                            <ul class="mt-2 list-disc list-inside text-sm text-red-700 space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Edit Form --}}
            <div class="bg-surface border border-oat rounded-card">
                <div class="px-6 py-5 border-b border-oat">
                    <h3 class="text-lg font-semibold text-off-black tracking-sub">Plan Details</h3>
                    <p class="mt-1 text-sm text-muted">Update the subscription plan settings and limits.</p>
                </div>

                <form action="{{ route('admin.subscription-plans.update', $plan) }}" method="POST" class="px-6 py-6 space-y-6">
                    @csrf
                    @method('PUT')

                    {{-- Basic Info --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-off-black mb-1">Name <span class="text-red-500">*</span></label>
                            <input type="text"
                                   name="name"
                                   id="name"
                                   value="{{ old('name', $plan->name) }}"
                                   required
                                   placeholder="e.g. Pro Plan"
                                   class="w-full rounded-btn border-oat focus:border-fin-orange focus:ring-fin-orange text-sm">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="slug" class="block text-sm font-medium text-off-black mb-1">Slug <span class="text-red-500">*</span></label>
                            <input type="text"
                                   name="slug"
                                   id="slug"
                                   value="{{ old('slug', $plan->slug) }}"
                                   required
                                   placeholder="e.g. pro-plan"
                                   class="w-full rounded-btn border-oat focus:border-fin-orange focus:ring-fin-orange text-sm">
                            @error('slug')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Pricing --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="price_idr" class="block text-sm font-medium text-off-black mb-1">Price IDR <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-muted">Rp</span>
                                <input type="number"
                                       name="price_idr"
                                       id="price_idr"
                                       value="{{ old('price_idr', $plan->price_idr) }}"
                                       required
                                       min="0"
                                       placeholder="0"
                                       class="w-full rounded-btn border-oat focus:border-fin-orange focus:ring-fin-orange text-sm pl-9">
                            </div>
                            @error('price_idr')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="budget_usd_per_cycle" class="block text-sm font-medium text-off-black mb-1">Budget USD / Cycle <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-muted">$</span>
                                <input type="number"
                                       name="budget_usd_per_cycle"
                                       id="budget_usd_per_cycle"
                                       value="{{ old('budget_usd_per_cycle', $plan->budget_usd_per_cycle) }}"
                                       required
                                       min="0"
                                       step="0.01"
                                       placeholder="0.00"
                                       class="w-full rounded-btn border-oat focus:border-fin-orange focus:ring-fin-orange text-sm pl-7">
                            </div>
                            @error('budget_usd_per_cycle')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="cycle_hours" class="block text-sm font-medium text-off-black mb-1">Cycle Hours <span class="text-red-500">*</span></label>
                            <input type="number"
                                   name="cycle_hours"
                                   id="cycle_hours"
                                   value="{{ old('cycle_hours', $plan->cycle_hours) }}"
                                   required
                                   min="1"
                                   placeholder="6"
                                   class="w-full rounded-btn border-oat focus:border-fin-orange focus:ring-fin-orange text-sm">
                            @error('cycle_hours')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Rate Limits --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="rpm_limit" class="block text-sm font-medium text-off-black mb-1">RPM Limit <span class="text-red-500">*</span></label>
                            <input type="number"
                                   name="rpm_limit"
                                   id="rpm_limit"
                                   value="{{ old('rpm_limit', $plan->rpm_limit) }}"
                                   required
                                   min="1"
                                   placeholder="30"
                                   class="w-full rounded-btn border-oat focus:border-fin-orange focus:ring-fin-orange text-sm">
                            <p class="mt-1 text-xs text-muted">Requests per minute</p>
                            @error('rpm_limit')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="parallel_limit" class="block text-sm font-medium text-off-black mb-1">Parallel Limit <span class="text-red-500">*</span></label>
                            <input type="number"
                                   name="parallel_limit"
                                   id="parallel_limit"
                                   value="{{ old('parallel_limit', $plan->parallel_limit) }}"
                                   required
                                   min="1"
                                   placeholder="3"
                                   class="w-full rounded-btn border-oat focus:border-fin-orange focus:ring-fin-orange text-sm">
                            <p class="mt-1 text-xs text-muted">Maximum concurrent requests</p>
                            @error('parallel_limit')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Models & Description --}}
                    <div>
                        <label for="allowed_models" class="block text-sm font-medium text-off-black mb-1">Allowed Models</label>
                        <input type="text"
                               name="allowed_models"
                               id="allowed_models"
                               value="{{ old('allowed_models', is_array($plan->allowed_models) ? implode(', ', $plan->allowed_models) : $plan->allowed_models) }}"
                               placeholder="e.g. gpt-4o, claude-3-sonnet, gemini-pro (comma-separated, leave empty for all)"
                               class="w-full rounded-btn border-oat focus:border-fin-orange focus:ring-fin-orange text-sm">
                        <p class="mt-1 text-xs text-muted">Comma-separated list of model IDs. Leave empty to allow all models.</p>
                        @error('allowed_models')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-off-black mb-1">Description</label>
                        <textarea name="description"
                                  id="description"
                                  rows="3"
                                  placeholder="Brief description of this plan..."
                                  class="w-full rounded-btn border-oat focus:border-fin-orange focus:ring-fin-orange text-sm">{{ old('description', $plan->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Options --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex items-center gap-3">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox"
                                   name="is_active"
                                   id="is_active"
                                   value="1"
                                   {{ old('is_active', $plan->is_active) ? 'checked' : '' }}
                                   class="rounded border-oat text-fin-orange focus:ring-fin-orange">
                            <label for="is_active" class="text-sm font-medium text-off-black">Active</label>
                        </div>

                        <div>
                            <label for="sort_order" class="block text-sm font-medium text-off-black mb-1">Sort Order</label>
                            <input type="number"
                                   name="sort_order"
                                   id="sort_order"
                                   value="{{ old('sort_order', $plan->sort_order) }}"
                                   min="0"
                                   placeholder="0"
                                   class="w-full rounded-btn border-oat focus:border-fin-orange focus:ring-fin-orange text-sm">
                            <p class="mt-1 text-xs text-muted">Lower numbers appear first</p>
                            @error('sort_order')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-oat">
                        <a href="{{ route('admin.subscription-plans.index') }}"
                           class="inline-flex items-center rounded-btn border border-oat bg-surface px-4 py-2 text-sm font-medium text-off-black hover:bg-canvas transition-colors">
                            Cancel
                        </a>
                        <button type="submit"
                                class="inline-flex items-center rounded-btn bg-fin-orange px-4 py-2 text-sm font-medium text-white hover:bg-fin-orange-hover focus:outline-none focus:ring-2 focus:ring-fin-orange focus:ring-offset-2 btn-intercom transition-colors">
                            <i data-lucide="save" class="mr-1.5 h-4 w-4"></i>
                            Update Plan
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
