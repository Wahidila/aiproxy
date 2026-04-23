@auth
    @php
        $allBroadcastNotifications = \App\Models\BroadcastNotification::visibleTo(auth()->user())->get();
        $bannerNotifications = $allBroadcastNotifications->filter(fn($n) => in_array($n->display_type, ['banner', 'both']));
        $popupNotifications = $allBroadcastNotifications->filter(fn($n) => in_array($n->display_type, ['popup', 'both']));

        $typeConfig = [
            'info' => [
                'border' => 'border-l-[#ff5600]',
                'icon' => 'info',
                'iconColor' => 'text-[#ff5600]',
                'bg' => 'bg-orange-50/50',
                'headerColor' => 'text-[#ff5600]',
            ],
            'warning' => [
                'border' => 'border-l-[#fe4c02]',
                'icon' => 'alert-triangle',
                'iconColor' => 'text-[#fe4c02]',
                'bg' => 'bg-amber-50/50',
                'headerColor' => 'text-[#fe4c02]',
            ],
            'success' => [
                'border' => 'border-l-[#0bdf50]',
                'icon' => 'check-circle',
                'iconColor' => 'text-[#0bdf50]',
                'bg' => 'bg-green-50/50',
                'headerColor' => 'text-green-600',
            ],
            'danger' => [
                'border' => 'border-l-[#c41c1c]',
                'icon' => 'alert-circle',
                'iconColor' => 'text-[#c41c1c]',
                'bg' => 'bg-red-50/50',
                'headerColor' => 'text-[#c41c1c]',
            ],
        ];
    @endphp

    {{-- ============================================ --}}
    {{-- BANNER NOTIFICATIONS (top of page, inline)   --}}
    {{-- ============================================ --}}
    @if($bannerNotifications->isNotEmpty())
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4 space-y-3">
            @foreach($bannerNotifications as $notification)
                @php $config = $typeConfig[$notification->type] ?? $typeConfig['info']; @endphp

                <div x-data="{ visible: true, dismissing: false }"
                     x-show="visible"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-2"
                     class="rounded-card border border-oat border-l-4 {{ $config['border'] }} {{ $config['bg'] }} p-4">
                    <div class="flex flex-col sm:flex-row items-start gap-3">
                        <div class="flex items-start gap-3 flex-1 min-w-0">
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
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center gap-2 flex-shrink-0 w-full sm:w-auto justify-end">
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
                                <span x-show="!dismissing">Jangan ingatkan lagi</span>
                                <span x-show="dismissing" x-cloak>
                                    <svg class="animate-spin h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                            </button>

                            <button type="button"
                                    @click="visible = false"
                                    class="p-1.5 text-muted hover:text-off-black transition-all rounded-btn hover:bg-canvas hover:scale-105 active:scale-95"
                                    title="Tutup sementara">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- ============================================ --}}
    {{-- POPUP MODAL NOTIFICATIONS (centered overlay) --}}
    {{-- ============================================ --}}
    @if($popupNotifications->isNotEmpty())
        <div x-data="{
                popups: [
                    @foreach($popupNotifications as $i => $notification)
                        { id: {{ $notification->id }}, visible: true, dismissing: false, url: '{{ route('notifications.dismiss', $notification) }}' }{{ !$loop->last ? ',' : '' }}
                    @endforeach
                ],
                get hasVisible() {
                    return this.popups.some(p => p.visible);
                },
                get currentPopup() {
                    return this.popups.find(p => p.visible) || null;
                },
                dismissPopup(popup) {
                    popup.dismissing = true;
                    fetch(popup.url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    }).then(() => {
                        popup.visible = false;
                    }).catch(() => {
                        popup.dismissing = false;
                    });
                },
                closePopup(popup) {
                    popup.visible = false;
                }
             }"
             x-init="$watch('hasVisible', value => {
                 if (value) {
                     document.body.classList.add('overflow-hidden');
                 } else {
                     document.body.classList.remove('overflow-hidden');
                 }
             })"
             x-on:keydown.escape.window="if(hasVisible && currentPopup) closePopup(currentPopup)"
             x-show="hasVisible"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6 overflow-y-auto"
             style="display: none;">

            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-off-black/50" @click="if(currentPopup) closePopup(currentPopup)"></div>

            {{-- Modal Cards --}}
            @foreach($popupNotifications as $notification)
                @php $config = $typeConfig[$notification->type] ?? $typeConfig['info']; @endphp

                <template x-if="popups.find(p => p.id === {{ $notification->id }})?.visible">
                    <div x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="relative w-full max-w-[calc(100vw-1.5rem)] sm:max-w-md bg-surface border border-oat rounded-card overflow-hidden">

                        {{-- Colored top bar --}}
                        <div class="h-1 {{ str_replace('border-l-', 'bg-', $config['border']) }}"></div>

                        {{-- Content --}}
                        <div class="p-4 sm:p-6">
                            {{-- Header with icon --}}
                            <div class="flex items-start gap-3 mb-3 sm:mb-4">
                                <div class="flex-shrink-0 flex items-center justify-center w-10 h-10 rounded-lg {{ $config['bg'] }}">
                                    <i data-lucide="{{ $config['icon'] }}" class="w-5 h-5 {{ $config['iconColor'] }}"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    @if($notification->title)
                                        <h3 class="text-base font-semibold text-off-black tracking-tight">{{ $notification->title }}</h3>
                                    @else
                                        <h3 class="text-base font-semibold {{ $config['headerColor'] }} tracking-tight">
                                            {{ ucfirst($notification->type) === 'Info' ? 'Informasi' : (ucfirst($notification->type) === 'Warning' ? 'Peringatan' : (ucfirst($notification->type) === 'Danger' ? 'Penting' : ucfirst($notification->type))) }}
                                        </h3>
                                    @endif
                                    <p class="text-xs text-muted mt-0.5">{{ $notification->created_at->diffForHumans() }}</p>
                                </div>

                                {{-- Close X button --}}
                                <button type="button"
                                        @click="closePopup(popups.find(p => p.id === {{ $notification->id }}))"
                                        class="flex-shrink-0 p-1.5 text-muted hover:text-off-black transition-all rounded-btn hover:bg-canvas hover:scale-105 active:scale-95">
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                </button>
                            </div>

                            {{-- Message body --}}
                            <div class="text-sm text-off-black leading-relaxed max-h-[40vh] overflow-y-auto scrollbar-warm">
                                {{ $notification->message }}
                            </div>
                        </div>

                        {{-- Footer actions --}}
                        <div class="px-4 sm:px-6 py-3 sm:py-4 bg-canvas border-t border-oat flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2 sm:gap-3">
                            <button type="button"
                                    :disabled="popups.find(p => p.id === {{ $notification->id }})?.dismissing"
                                    @click="dismissPopup(popups.find(p => p.id === {{ $notification->id }}))"
                                    class="inline-flex items-center justify-center gap-1.5 w-full sm:w-auto px-4 py-2.5 sm:py-2 text-sm font-medium text-off-black bg-transparent border border-off-black rounded-btn hover:scale-105 active:scale-95 transition-transform">
                                <i data-lucide="bell-off" class="w-3.5 h-3.5"></i>
                                <span x-show="!popups.find(p => p.id === {{ $notification->id }})?.dismissing">Jangan ingatkan lagi</span>
                                <span x-show="popups.find(p => p.id === {{ $notification->id }})?.dismissing" x-cloak>
                                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                            </button>
                            <button type="button"
                                    @click="closePopup(popups.find(p => p.id === {{ $notification->id }}))"
                                    class="inline-flex items-center justify-center w-full sm:w-auto px-4 py-2.5 sm:py-2 text-sm font-medium text-white bg-off-black rounded-btn hover:scale-105 active:scale-95 transition-transform">
                                Tutup
                            </button>
                        </div>
                    </div>
                </template>
            @endforeach
        </div>
    @endif
@endauth
