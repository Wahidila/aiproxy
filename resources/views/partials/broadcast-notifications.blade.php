@auth
    @php
        $broadcastNotifications = \App\Models\BroadcastNotification::visibleTo(auth()->user())->get();
    @endphp

    @if($broadcastNotifications->isNotEmpty())
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4 space-y-3">
            @foreach($broadcastNotifications as $notification)
                @php
                    $typeConfig = [
                        'info' => [
                            'border' => 'border-l-[#ff5600]',
                            'icon' => 'info',
                            'iconColor' => 'text-[#ff5600]',
                            'bg' => 'bg-orange-50/50',
                        ],
                        'warning' => [
                            'border' => 'border-l-[#fe4c02]',
                            'icon' => 'alert-triangle',
                            'iconColor' => 'text-[#fe4c02]',
                            'bg' => 'bg-amber-50/50',
                        ],
                        'success' => [
                            'border' => 'border-l-[#0bdf50]',
                            'icon' => 'check-circle',
                            'iconColor' => 'text-[#0bdf50]',
                            'bg' => 'bg-green-50/50',
                        ],
                        'danger' => [
                            'border' => 'border-l-[#c41c1c]',
                            'icon' => 'alert-circle',
                            'iconColor' => 'text-[#c41c1c]',
                            'bg' => 'bg-red-50/50',
                        ],
                    ];
                    $config = $typeConfig[$notification->type] ?? $typeConfig['info'];
                @endphp

                <div x-data="{ visible: true, dismissing: false }"
                     x-show="visible"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-2"
                     class="rounded-card border border-oat border-l-4 {{ $config['border'] }} {{ $config['bg'] }} p-4">
                    <div class="flex items-start gap-3">
                        {{-- Icon --}}
                        <div class="flex-shrink-0 mt-0.5">
                            <i data-lucide="{{ $config['icon'] }}" class="w-5 h-5 {{ $config['iconColor'] }}"></i>
                        </div>

                        {{-- Content --}}
                        <div class="flex-1 min-w-0">
                            @if($notification->title)
                                <p class="text-sm font-semibold text-off-black">{{ $notification->title }}</p>
                            @endif
                            <p class="text-sm text-off-black {{ $notification->title ? 'mt-0.5' : '' }}">{{ $notification->message }}</p>
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center gap-2 flex-shrink-0">
                            {{-- Don't Remind Me Button --}}
                            <button type="button"
                                    :disabled="dismissing"
                                    @click="
                                        dismissing = true;
                                        fetch('{{ route('notifications.dismiss', $notification) }}', {
                                            method: 'POST',
                                            headers: {
                                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                                'Accept': 'application/json',
                                                'Content-Type': 'application/json'
                                            }
                                        }).then(() => {
                                            visible = false;
                                        }).catch(() => {
                                            dismissing = false;
                                        });
                                    "
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-off-black bg-transparent border border-off-black rounded-btn hover:scale-105 active:scale-95 transition-transform whitespace-nowrap">
                                <i data-lucide="bell-off" class="w-3 h-3"></i>
                                <span x-show="!dismissing">Don't remind me</span>
                                <span x-show="dismissing" x-cloak>
                                    <svg class="animate-spin h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                            </button>

                            {{-- Close (temporary hide, will show again on next page load) --}}
                            <button type="button"
                                    @click="visible = false"
                                    class="p-1 text-muted hover:text-off-black transition-colors"
                                    title="Tutup sementara">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endauth
