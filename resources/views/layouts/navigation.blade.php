<nav x-data="{ open: false }" class="bg-surface border-b border-oat">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2 font-semibold text-lg text-off-black tracking-heading">
                        <i data-lucide="zap" class="w-5 h-5" style="color: #ff5600;"></i>
                        AIMurah
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        Dashboard
                    </x-nav-link>
                    <x-nav-link :href="route('api-keys.index')" :active="request()->routeIs('api-keys.*')">
                        API Keys
                    </x-nav-link>
                    <x-nav-link :href="route('usage.index')" :active="request()->routeIs('usage.*')">
                        Usage
                    </x-nav-link>
                    <x-nav-link :href="route('donations.index')" :active="request()->routeIs('donations.*')">
                        Top Up
                    </x-nav-link>
                    @if(Auth::user()->isAdmin())
                    <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*') && !request()->routeIs('admin.broadcast-notifications.*')" class="!text-fin-orange">
                        Admin
                    </x-nav-link>
                    <x-nav-link :href="route('admin.broadcast-notifications.index')" :active="request()->routeIs('admin.broadcast-notifications.*')" class="!text-fin-orange">
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="megaphone" class="w-3.5 h-3.5"></i>
                            Broadcast
                        </span>
                    </x-nav-link>
                    @php $navPendingTrials = \App\Models\TrialRequest::pending()->count(); @endphp
                    @if($navPendingTrials > 0)
                    <x-nav-link :href="route('admin.trial-requests.index', ['status' => 'pending'])" :active="request()->routeIs('admin.trial-requests.*')" class="!text-purple-600">
                        <span class="flex items-center gap-1.5">
                            Trial
                            <span class="inline-flex items-center justify-center h-5 min-w-[20px] rounded-full bg-purple-100 text-xs font-semibold text-purple-700 px-1.5">{{ $navPendingTrials }}</span>
                        </span>
                    </x-nav-link>
                    @endif
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-btn text-muted bg-surface hover:text-off-black focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            Profile
                        </x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                Log Out
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-btn text-muted hover:text-off-black hover:bg-canvas focus:outline-none focus:bg-canvas focus:text-off-black transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                Dashboard
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('api-keys.index')" :active="request()->routeIs('api-keys.*')">
                API Keys
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('usage.index')" :active="request()->routeIs('usage.*')">
                Usage
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('donations.index')" :active="request()->routeIs('donations.*')">
                Top Up
            </x-responsive-nav-link>
            @if(Auth::user()->isAdmin())
            <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*') && !request()->routeIs('admin.broadcast-notifications.*')">
                Admin Panel
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.broadcast-notifications.index')" :active="request()->routeIs('admin.broadcast-notifications.*')">
                Broadcast Notifications
            </x-responsive-nav-link>
            @php $navPendingTrialsMobile = \App\Models\TrialRequest::pending()->count(); @endphp
            @if($navPendingTrialsMobile > 0)
            <x-responsive-nav-link :href="route('admin.trial-requests.index', ['status' => 'pending'])" :active="request()->routeIs('admin.trial-requests.*')">
                Trial Requests ({{ $navPendingTrialsMobile }})
            </x-responsive-nav-link>
            @endif
            @endif
        </div>

        <div class="pt-4 pb-1 border-t border-oat">
            <div class="px-4">
                <div class="font-medium text-base text-off-black">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-muted">{{ Auth::user()->email }}</div>
            </div>
            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    Profile
                </x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault(); this.closest('form').submit();">
                        Log Out
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
