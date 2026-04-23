<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('support.index') }}" class="text-muted hover:text-off-black transition">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <h2 class="font-semibold text-xl text-off-black leading-tight tracking-heading">
                {{ __('Buat Ticket Baru') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Messages --}}
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <p class="text-red-800 text-sm font-medium">{{ session('error') }}</p>
                </div>
            @endif

            {{-- Info --}}
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-center">
                    <i data-lucide="info" class="w-5 h-5 text-blue-500 mr-3 flex-shrink-0"></i>
                    <p class="text-blue-800 text-sm">Jelaskan masalah atau pertanyaan Anda dengan detail agar tim kami bisa membantu dengan cepat.</p>
                </div>
            </div>

            {{-- Ticket Form --}}
            <div class="bg-surface border border-oat rounded-card">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-off-black tracking-sub mb-4">Detail Ticket</h3>

                    <form action="{{ route('support.store') }}" method="POST" class="space-y-6">
                        @csrf

                        {{-- Subject --}}
                        <div>
                            <label for="subject" class="block text-sm font-medium text-off-black mb-1">Subjek <span class="text-red-500">*</span></label>
                            <input type="text" name="subject" id="subject" value="{{ old('subject') }}"
                                placeholder="Ringkasan singkat masalah atau pertanyaan Anda"
                                class="w-full rounded-btn border-oat focus:border-fin-orange focus:ring-fin-orange"
                                required maxlength="255">
                            @error('subject')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Category --}}
                        <div>
                            <label for="category" class="block text-sm font-medium text-off-black mb-1">Kategori <span class="text-red-500">*</span></label>
                            <select name="category" id="category"
                                class="w-full rounded-btn border-oat focus:border-fin-orange focus:ring-fin-orange" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach(\App\Models\SupportTicket::CATEGORY_LABELS as $value => $label)
                                    <option value="{{ $value }}" {{ old('category') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('category')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Message --}}
                        <div>
                            <label for="message" class="block text-sm font-medium text-off-black mb-1">Pesan <span class="text-red-500">*</span></label>
                            <textarea name="message" id="message" rows="6" required maxlength="5000"
                                placeholder="Jelaskan masalah atau pertanyaan Anda secara detail..."
                                class="w-full rounded-btn border-oat focus:border-fin-orange focus:ring-fin-orange">{{ old('message') }}</textarea>
                            @error('message')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-muted">Maksimal 5000 karakter</p>
                        </div>

                        {{-- Submit --}}
                        <div class="flex items-center gap-3">
                            <button type="submit"
                                class="inline-flex items-center gap-2 px-6 py-3 bg-off-black text-white font-semibold rounded-btn hover:bg-off-black/90 btn-intercom transition-colors">
                                <i data-lucide="send" class="w-4 h-4"></i>
                                Kirim Ticket
                            </button>
                            <a href="{{ route('support.index') }}" class="text-sm text-muted hover:text-off-black transition">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => lucide.createIcons());
    </script>
</x-app-layout>
