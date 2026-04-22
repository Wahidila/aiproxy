<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-off-black tracking-heading">
                    {{ __('Broadcast Notifications') }}
                </h2>
                <nav class="mt-1 text-sm text-muted">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-fin-orange">Admin</a>
                    <span class="mx-1">/</span>
                    <span>Broadcast Notifications</span>
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

            {{-- Create New Notification --}}
            <div class="bg-surface border border-oat rounded-card">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-off-black tracking-sub mb-4">Buat Notifikasi Baru</h3>
                    <form action="{{ route('admin.broadcast-notifications.store') }}" method="POST" class="space-y-4">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Title --}}
                            <div>
                                <label for="title" class="block text-sm font-medium text-off-black mb-1">Judul <span class="text-muted">(opsional)</span></label>
                                <input type="text" name="title" id="title" value="{{ old('title') }}" placeholder="Contoh: Maintenance Terjadwal"
                                    class="w-full rounded-lg border-oat focus:border-fin-orange focus:ring-fin-orange text-sm">
                                @error('title')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Type --}}
                            <div>
                                <label for="type" class="block text-sm font-medium text-off-black mb-1">Tipe Notifikasi</label>
                                <select name="type" id="type"
                                    class="w-full rounded-lg border-oat focus:border-fin-orange focus:ring-fin-orange text-sm">
                                    <option value="info" {{ old('type') === 'info' ? 'selected' : '' }}>Info — Informasi umum</option>
                                    <option value="warning" {{ old('type') === 'warning' ? 'selected' : '' }}>Warning — Peringatan</option>
                                    <option value="success" {{ old('type') === 'success' ? 'selected' : '' }}>Success — Kabar baik</option>
                                    <option value="danger" {{ old('type') === 'danger' ? 'selected' : '' }}>Danger — Penting/Urgent</option>
                                </select>
                                @error('type')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Message --}}
                        <div>
                            <label for="message" class="block text-sm font-medium text-off-black mb-1">Pesan <span class="text-red-500">*</span></label>
                            <textarea name="message" id="message" rows="3" required placeholder="Tulis pesan notifikasi yang akan ditampilkan ke semua user..."
                                class="w-full rounded-lg border-oat focus:border-fin-orange focus:ring-fin-orange text-sm">{{ old('message') }}</textarea>
                            @error('message')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Expires At --}}
                        <div>
                            <label for="expires_at" class="block text-sm font-medium text-off-black mb-1">Kedaluwarsa <span class="text-muted">(opsional)</span></label>
                            <input type="datetime-local" name="expires_at" id="expires_at" value="{{ old('expires_at') }}"
                                class="w-full md:w-1/2 rounded-lg border-oat focus:border-fin-orange focus:ring-fin-orange text-sm">
                            <p class="mt-1 text-xs text-muted">Kosongkan jika notifikasi tidak memiliki batas waktu.</p>
                            @error('expires_at')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Preview --}}
                        <div x-data="{ type: '{{ old('type', 'info') }}', title: '{{ old('title', '') }}', message: '{{ old('message', '') }}' }"
                             x-init="
                                $watch('type', () => {});
                                document.getElementById('type').addEventListener('change', e => type = e.target.value);
                                document.getElementById('title').addEventListener('input', e => title = e.target.value);
                                document.getElementById('message').addEventListener('input', e => message = e.target.value);
                             ">
                            <p class="block text-sm font-medium text-off-black mb-2">Preview</p>
                            <div class="rounded-card border border-oat bg-canvas p-4"
                                 :class="{
                                    'border-l-4 border-l-[#ff5600]': type === 'info',
                                    'border-l-4 border-l-[#fe4c02]': type === 'warning',
                                    'border-l-4 border-l-[#0bdf50]': type === 'success',
                                    'border-l-4 border-l-[#c41c1c]': type === 'danger'
                                 }">
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0 mt-0.5">
                                        <template x-if="type === 'info'">
                                            <i data-lucide="info" class="w-5 h-5 text-[#ff5600]"></i>
                                        </template>
                                        <template x-if="type === 'warning'">
                                            <i data-lucide="alert-triangle" class="w-5 h-5 text-[#fe4c02]"></i>
                                        </template>
                                        <template x-if="type === 'success'">
                                            <i data-lucide="check-circle" class="w-5 h-5 text-[#0bdf50]"></i>
                                        </template>
                                        <template x-if="type === 'danger'">
                                            <i data-lucide="alert-circle" class="w-5 h-5 text-[#c41c1c]"></i>
                                        </template>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p x-show="title" class="text-sm font-semibold text-off-black" x-text="title"></p>
                                        <p class="text-sm text-off-black" :class="{ 'mt-1': title }" x-text="message || 'Pesan notifikasi akan muncul di sini...'"></p>
                                    </div>
                                    <button type="button" class="flex-shrink-0 text-muted hover:text-off-black transition" disabled>
                                        <i data-lucide="x" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Submit --}}
                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-off-black text-white text-sm font-semibold rounded-btn hover:scale-105 active:scale-95 transition-transform">
                                <i data-lucide="send" class="w-4 h-4"></i>
                                Broadcast Notifikasi
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Notifications List --}}
            <div class="bg-surface border border-oat rounded-card">
                <div class="px-6 py-4 border-b border-oat">
                    <h3 class="text-lg font-semibold text-off-black tracking-sub">Daftar Notifikasi</h3>
                    <p class="text-sm text-muted mt-1">Kelola semua notifikasi broadcast yang pernah dibuat.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-oat">
                        <thead class="bg-canvas">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted">Notifikasi</th>
                                <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-muted">Tipe</th>
                                <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-muted">Status</th>
                                <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-muted">Dismissed</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted">Dibuat</th>
                                <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-muted">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-oat bg-surface">
                            @forelse($notifications as $notification)
                                <tr class="hover:bg-canvas" x-data="{ editing: false }">
                                    {{-- Notification Content --}}
                                    <td class="px-4 py-3 max-w-md">
                                        {{-- View Mode --}}
                                        <div x-show="!editing">
                                            @if($notification->title)
                                                <p class="text-sm font-semibold text-off-black">{{ $notification->title }}</p>
                                            @endif
                                            <p class="text-sm text-off-black {{ $notification->title ? 'mt-0.5' : '' }}">{{ Str::limit($notification->message, 120) }}</p>
                                            @if($notification->expires_at)
                                                <p class="text-xs text-muted mt-1">
                                                    Kedaluwarsa: {{ $notification->expires_at->format('d M Y, H:i') }}
                                                    @if($notification->expires_at->isPast())
                                                        <span class="text-red-500">(Expired)</span>
                                                    @endif
                                                </p>
                                            @endif
                                        </div>

                                        {{-- Edit Mode --}}
                                        <form x-show="editing" x-cloak method="POST" action="{{ route('admin.broadcast-notifications.update', $notification) }}" class="space-y-2">
                                            @csrf
                                            @method('PATCH')
                                            <input type="text" name="title" value="{{ $notification->title }}" placeholder="Judul (opsional)"
                                                class="w-full rounded-lg border-oat focus:border-fin-orange focus:ring-fin-orange text-xs py-1.5">
                                            <textarea name="message" rows="2" required
                                                class="w-full rounded-lg border-oat focus:border-fin-orange focus:ring-fin-orange text-xs py-1.5">{{ $notification->message }}</textarea>
                                            <div class="flex items-center gap-2">
                                                <select name="type" class="rounded-lg border-oat focus:border-fin-orange focus:ring-fin-orange text-xs py-1.5">
                                                    @foreach(['info', 'warning', 'success', 'danger'] as $type)
                                                        <option value="{{ $type }}" {{ $notification->type === $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                                                    @endforeach
                                                </select>
                                                <input type="datetime-local" name="expires_at" value="{{ $notification->expires_at?->format('Y-m-d\TH:i') }}"
                                                    class="rounded-lg border-oat focus:border-fin-orange focus:ring-fin-orange text-xs py-1.5">
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <button type="submit" class="inline-flex items-center rounded-btn bg-off-black px-3 py-1 text-xs font-medium text-white hover:scale-105 active:scale-95 transition-transform">
                                                    Simpan
                                                </button>
                                                <button type="button" @click="editing = false" class="inline-flex items-center rounded-btn bg-canvas border border-oat px-3 py-1 text-xs font-medium text-off-black hover:bg-oat transition-colors">
                                                    Batal
                                                </button>
                                            </div>
                                        </form>
                                    </td>

                                    {{-- Type Badge --}}
                                    <td class="whitespace-nowrap px-4 py-3 text-center">
                                        @php
                                            $typeColors = [
                                                'info' => 'bg-orange-100 text-orange-700',
                                                'warning' => 'bg-yellow-100 text-yellow-700',
                                                'success' => 'bg-green-100 text-green-700',
                                                'danger' => 'bg-red-100 text-red-700',
                                            ];
                                        @endphp
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $typeColors[$notification->type] ?? $typeColors['info'] }}">
                                            {{ ucfirst($notification->type) }}
                                        </span>
                                    </td>

                                    {{-- Status --}}
                                    <td class="whitespace-nowrap px-4 py-3 text-center">
                                        @if($notification->is_active && (!$notification->expires_at || $notification->expires_at->isFuture()))
                                            <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700">
                                                <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                                                Aktif
                                            </span>
                                        @elseif($notification->expires_at && $notification->expires_at->isPast())
                                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-500">
                                                Expired
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-500">
                                                Nonaktif
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Dismissal Count --}}
                                    <td class="whitespace-nowrap px-4 py-3 text-center">
                                        <span class="text-sm text-muted">{{ $notification->dismissals_count }}</span>
                                    </td>

                                    {{-- Created --}}
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <p class="text-sm text-off-black">{{ $notification->created_at->format('d M Y') }}</p>
                                        <p class="text-xs text-muted">{{ $notification->created_at->format('H:i') }} &middot; {{ $notification->creator?->name ?? 'Admin' }}</p>
                                    </td>

                                    {{-- Actions --}}
                                    <td class="whitespace-nowrap px-4 py-3 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            {{-- Edit --}}
                                            <button x-show="!editing" @click="editing = true" type="button"
                                                class="inline-flex items-center rounded-btn bg-canvas border border-oat p-1.5 text-muted hover:text-off-black hover:bg-oat transition-colors"
                                                title="Edit">
                                                <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                            </button>

                                            {{-- Toggle Active --}}
                                            <form method="POST" action="{{ route('admin.broadcast-notifications.toggle', $notification) }}">
                                                @csrf
                                                <button type="submit"
                                                    class="inline-flex items-center rounded-btn bg-canvas border border-oat p-1.5 transition-colors {{ $notification->is_active ? 'text-green-600 hover:text-red-600 hover:bg-red-50 hover:border-red-200' : 'text-muted hover:text-green-600 hover:bg-green-50 hover:border-green-200' }}"
                                                    title="{{ $notification->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                    @if($notification->is_active)
                                                        <i data-lucide="eye-off" class="w-3.5 h-3.5"></i>
                                                    @else
                                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                                    @endif
                                                </button>
                                            </form>

                                            {{-- Delete --}}
                                            <form method="POST" action="{{ route('admin.broadcast-notifications.destroy', $notification) }}"
                                                  onsubmit="return confirm('Hapus notifikasi ini? Tindakan ini tidak dapat dibatalkan.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="inline-flex items-center rounded-btn bg-canvas border border-oat p-1.5 text-muted hover:text-red-600 hover:bg-red-50 hover:border-red-200 transition-colors"
                                                    title="Hapus">
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-sm text-warm-sand">
                                        Belum ada notifikasi broadcast. Buat notifikasi pertama di atas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    {{-- Re-init Lucide icons after Alpine renders --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => lucide.createIcons());
    </script>
</x-app-layout>
