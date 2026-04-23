<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3 min-w-0">
                <a href="{{ route('admin.support.index') }}" class="text-muted hover:text-off-black transition flex-shrink-0">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </a>
                <div class="min-w-0">
                    <h2 class="font-semibold text-xl text-off-black leading-tight tracking-heading truncate">
                        {{ $ticket->subject }}
                    </h2>
                    <nav class="mt-1 text-sm text-muted">
                        <a href="{{ route('admin.dashboard') }}" class="hover:text-fin-orange">Admin</a>
                        <span class="mx-1">/</span>
                        <a href="{{ route('admin.support.index') }}" class="hover:text-fin-orange">Support</a>
                        <span class="mx-1">/</span>
                        <span>Ticket #{{ $ticket->id }}</span>
                    </nav>
                </div>
            </div>
            <span class="inline-flex items-center rounded-md bg-orange-100 px-2.5 py-1 text-xs font-medium text-orange-700 ring-1 ring-inset ring-orange-600/20">
                Admin
            </span>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

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

            {{-- Ticket Info Card --}}
            <div class="bg-surface border border-oat rounded-card p-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    {{-- User --}}
                    <div>
                        <p class="text-xs font-medium text-muted uppercase">User</p>
                        <a href="{{ route('admin.users.show', $ticket->user) }}" class="mt-1 text-sm font-medium text-off-black hover:text-fin-orange transition-colors inline-block">
                            {{ $ticket->user->name }}
                        </a>
                        <p class="text-xs text-muted">{{ $ticket->user->email }}</p>
                    </div>

                    {{-- Category --}}
                    <div>
                        <p class="text-xs font-medium text-muted uppercase">Kategori</p>
                        @php
                            $catColors = [
                                'umum' => 'bg-gray-100 text-gray-700',
                                'teknis' => 'bg-blue-100 text-blue-700',
                                'pembayaran' => 'bg-green-100 text-green-700',
                                'saran' => 'bg-purple-100 text-purple-700',
                                'lainnya' => 'bg-yellow-100 text-yellow-700',
                            ];
                        @endphp
                        <span class="mt-1 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $catColors[$ticket->category] ?? 'bg-gray-100 text-gray-700' }}">
                            {{ $ticket->category_label }}
                        </span>
                    </div>

                    {{-- Status with Change --}}
                    <div>
                        <p class="text-xs font-medium text-muted uppercase mb-1">Status</p>
                        <form action="{{ route('admin.support.update-status', $ticket) }}" method="POST" class="flex items-center gap-2">
                            @csrf
                            @method('PATCH')
                            <select name="status"
                                class="rounded-btn border-oat focus:border-fin-orange focus:ring-fin-orange text-xs py-1.5"
                                onchange="this.form.submit()">
                                @foreach(\App\Models\SupportTicket::STATUS_LABELS as $value => $label)
                                    <option value="{{ $value }}" {{ $ticket->status === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>

                    {{-- Created --}}
                    <div>
                        <p class="text-xs font-medium text-muted uppercase">Dibuat</p>
                        <p class="mt-1 text-sm text-off-black">{{ $ticket->created_at->format('d M Y, H:i') }}</p>
                        <p class="text-xs text-muted">{{ $ticket->created_at->diffForHumans() }}</p>
                    </div>
                </div>
            </div>

            {{-- Conversation Thread --}}
            <div class="bg-surface border border-oat rounded-card">
                <div class="px-5 py-4 border-b border-oat">
                    <h3 class="text-sm font-semibold text-off-black tracking-sub">Percakapan</h3>
                </div>
                <div class="p-5 space-y-4" id="conversation-thread">
                    @foreach($ticket->replies as $reply)
                        <div class="flex {{ $reply->is_admin ? 'justify-end' : 'justify-start' }}">
                            {{-- User Avatar (left side) --}}
                            @if(!$reply->is_admin)
                                <div class="flex-shrink-0 mr-3">
                                    <div class="w-8 h-8 rounded-full bg-canvas border border-oat flex items-center justify-center">
                                        <span class="text-xs font-bold text-muted">{{ strtoupper(substr($reply->user->name, 0, 1)) }}</span>
                                    </div>
                                </div>
                            @endif

                            <div class="max-w-[80%]">
                                <div class="rounded-card p-4 {{ $reply->is_admin ? 'bg-blue-50 border border-blue-200' : 'bg-canvas border border-oat' }}">
                                    {{-- Header --}}
                                    <div class="flex items-center gap-2 mb-2">
                                        @if($reply->is_admin)
                                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-blue-700">
                                                <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                                                {{ $reply->user->name }} (Admin)
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-off-black">
                                                <i data-lucide="user" class="w-3.5 h-3.5"></i>
                                                {{ $reply->user->name }}
                                            </span>
                                        @endif
                                        <span class="text-xs text-muted">&middot; {{ $reply->created_at->format('d M Y, H:i') }}</span>
                                    </div>
                                    {{-- Message --}}
                                    <div class="text-sm text-off-black leading-relaxed whitespace-pre-wrap">{{ $reply->message }}</div>
                                </div>
                            </div>

                            {{-- Admin Avatar (right side) --}}
                            @if($reply->is_admin)
                                <div class="flex-shrink-0 ml-3">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                        <i data-lucide="shield-check" class="w-4 h-4 text-blue-600"></i>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Admin Reply Form --}}
            <div class="bg-surface border border-oat rounded-card">
                <div class="p-5">
                    <h3 class="text-sm font-semibold text-off-black mb-3">Balas sebagai Admin</h3>
                    <form action="{{ route('admin.support.reply', $ticket) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <textarea name="message" rows="4" required maxlength="5000"
                                placeholder="Tulis balasan untuk user..."
                                class="w-full rounded-btn border-oat focus:border-fin-orange focus:ring-fin-orange text-sm">{{ old('message') }}</textarea>
                            @error('message')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="flex items-center justify-between">
                            <p class="text-xs text-muted">
                                @if($ticket->isOpen())
                                    Status akan otomatis berubah ke "Diproses" saat Anda membalas.
                                @endif
                            </p>
                            <button type="submit"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-off-black text-white text-sm font-semibold rounded-btn hover:bg-off-black/90 btn-intercom transition-colors">
                                <i data-lucide="send" class="w-4 h-4"></i>
                                Kirim Balasan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
            // Auto-scroll to latest message
            const thread = document.getElementById('conversation-thread');
            if (thread) {
                const lastMsg = thread.lastElementChild;
                if (lastMsg) {
                    lastMsg.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    </script>
</x-app-layout>
