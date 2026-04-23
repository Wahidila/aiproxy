<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('support.index') }}" class="text-muted hover:text-off-black transition">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div class="flex-1 min-w-0">
                <h2 class="font-semibold text-xl text-off-black leading-tight tracking-heading truncate">
                    {{ $ticket->subject }}
                </h2>
                <p class="text-sm text-muted mt-0.5">Ticket #{{ $ticket->id }}</p>
            </div>
            @php
                $statusColors = [
                    'open' => 'bg-green-100 text-green-700',
                    'in_progress' => 'bg-orange-100 text-orange-700',
                    'closed' => 'bg-gray-100 text-gray-500',
                ];
            @endphp
            <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-medium {{ $statusColors[$ticket->status] ?? 'bg-gray-100 text-gray-500' }}">
                @if($ticket->status === 'open')
                    <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                @elseif($ticket->status === 'in_progress')
                    <span class="h-1.5 w-1.5 rounded-full bg-orange-500"></span>
                @endif
                {{ $ticket->status_label }}
            </span>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

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

            {{-- Ticket Info --}}
            <div class="bg-surface border border-oat rounded-card p-5">
                <div class="flex items-center gap-4 flex-wrap">
                    @php
                        $catColors = [
                            'umum' => 'bg-gray-100 text-gray-700',
                            'teknis' => 'bg-blue-100 text-blue-700',
                            'pembayaran' => 'bg-green-100 text-green-700',
                            'saran' => 'bg-purple-100 text-purple-700',
                            'lainnya' => 'bg-yellow-100 text-yellow-700',
                        ];
                    @endphp
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $catColors[$ticket->category] ?? 'bg-gray-100 text-gray-700' }}">
                        {{ $ticket->category_label }}
                    </span>
                    <span class="text-xs text-muted">Dibuat {{ $ticket->created_at->format('d M Y, H:i') }}</span>
                    <span class="text-xs text-warm-sand">{{ $ticket->created_at->diffForHumans() }}</span>
                </div>
            </div>

            {{-- Conversation Thread --}}
            <div class="bg-surface border border-oat rounded-card">
                <div class="px-5 py-4 border-b border-oat">
                    <h3 class="text-sm font-semibold text-off-black tracking-sub">Percakapan</h3>
                </div>
                <div class="p-5 space-y-4" id="conversation-thread">
                    @foreach($ticket->replies as $reply)
                        <div class="flex {{ $reply->is_admin ? 'justify-start' : 'justify-end' }}">
                            {{-- Avatar --}}
                            @if($reply->is_admin)
                                <div class="flex-shrink-0 mr-3">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                        <i data-lucide="shield-check" class="w-4 h-4 text-blue-600"></i>
                                    </div>
                                </div>
                            @endif

                            <div class="max-w-[80%]">
                                <div class="rounded-card p-4 {{ $reply->is_admin ? 'bg-blue-50 border border-blue-200' : 'bg-canvas border border-oat' }}">
                                    {{-- Header --}}
                                    <div class="flex items-center gap-2 mb-2">
                                        @if($reply->is_admin)
                                            <span class="text-xs font-semibold text-blue-700">Admin</span>
                                        @else
                                            <span class="text-xs font-semibold text-off-black">{{ $reply->user->name }}</span>
                                        @endif
                                        <span class="text-xs text-muted">&middot; {{ $reply->created_at->format('d M Y, H:i') }}</span>
                                    </div>
                                    {{-- Message --}}
                                    <div class="text-sm text-off-black leading-relaxed whitespace-pre-wrap">{{ $reply->message }}</div>
                                </div>
                            </div>

                            {{-- User Avatar --}}
                            @if(!$reply->is_admin)
                                <div class="flex-shrink-0 ml-3">
                                    <div class="w-8 h-8 rounded-full bg-fin-orange-light flex items-center justify-center">
                                        <span class="text-xs font-bold text-fin-orange">{{ strtoupper(substr($reply->user->name, 0, 1)) }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Reply Form --}}
            @if(!$ticket->isClosed())
                <div class="bg-surface border border-oat rounded-card">
                    <div class="p-5">
                        <h3 class="text-sm font-semibold text-off-black mb-3">Tambah Balasan</h3>
                        <form action="{{ route('support.reply', $ticket) }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <textarea name="message" rows="4" required maxlength="5000"
                                    placeholder="Tulis balasan Anda..."
                                    class="w-full rounded-btn border-oat focus:border-fin-orange focus:ring-fin-orange text-sm">{{ old('message') }}</textarea>
                                @error('message')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="flex justify-end">
                                <button type="submit"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-off-black text-white text-sm font-semibold rounded-btn hover:bg-off-black/90 btn-intercom transition-colors">
                                    <i data-lucide="send" class="w-4 h-4"></i>
                                    Kirim Balasan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @else
                <div class="bg-canvas border border-oat rounded-card p-5 text-center">
                    <div class="flex flex-col items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center">
                            <i data-lucide="check-circle" class="w-5 h-5 text-gray-400"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted">Ticket ini sudah ditutup</p>
                            <p class="text-xs text-warm-sand mt-1">Buat ticket baru jika Anda membutuhkan bantuan lebih lanjut</p>
                        </div>
                        <a href="{{ route('support.create') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-fin-orange hover:text-fin-orange/80 transition">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            Buat Ticket Baru
                        </a>
                    </div>
                </div>
            @endif

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
            // Auto-scroll to latest message
            const thread = document.getElementById('conversation-thread');
            if (thread) {
                thread.scrollTop = thread.scrollHeight;
                // Also scroll the page to the bottom of conversation
                const lastMsg = thread.lastElementChild;
                if (lastMsg) {
                    lastMsg.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    </script>
</x-app-layout>
