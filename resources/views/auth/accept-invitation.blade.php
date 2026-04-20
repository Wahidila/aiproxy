<x-guest-layout>
    <div class="mb-4">
        <h2 class="text-lg font-semibold text-off-black tracking-sub">Buat Password</h2>
        <p class="mt-1 text-sm text-muted">
            Selamat datang, <strong class="text-off-black">{{ $invitation->name }}</strong>! Buat password untuk mengaktifkan akun Anda.
        </p>
    </div>

    <form method="POST" action="{{ route('invitation.store', $token) }}">
        @csrf

        <!-- Email (readonly) -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full bg-canvas" type="email" name="email" :value="$invitation->email" disabled />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autofocus autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-6">
            <x-primary-button class="w-full justify-center">
                Buat Akun
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
