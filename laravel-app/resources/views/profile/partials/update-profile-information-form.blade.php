<section>
    <header>
        <h2 class="font-mono text-3xl leading-none tracking-tight text-black">
            {{ __('Account Settings') }}
        </h2>

        <p class="font-roboto mt-2 text-sm text-black/65">
            {{ __('Edit your account information.') }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" class="mt-8 space-y-6">
        @csrf
        @method('patch')

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div class="info-row min-h-[128px] p-4">
                <x-input-label for="full_name" :value="__('Full Name')" />
                <x-text-input id="full_name" name="full_name" type="text" class="font-roboto mt-2 block h-12 w-full rounded-xl border-black/15 bg-white/95 focus:border-black focus:ring-black/20" :value="old('full_name', $user->full_name)" required autofocus autocomplete="name" />
                <x-input-error class="mt-2" :messages="$errors->get('full_name')" />
            </div>

            <div class="info-row min-h-[128px] p-4">
                <x-input-label for="phone" :value="__('Phone Number')" />
                <x-text-input id="phone" name="phone" type="text" class="font-roboto mt-2 block h-12 w-full rounded-xl border-black/15 bg-white/95 focus:border-black focus:ring-black/20" :value="old('phone', $user->phone)" autocomplete="tel" />
                <x-input-error class="mt-2" :messages="$errors->get('phone')" />
            </div>

            <div class="info-row min-h-[128px] p-4">
                <x-input-label for="email_readonly" :value="__('Email (unchangeable)')" />
                <x-text-input id="email_readonly" name="email" type="email" class="font-roboto mt-2 block h-12 w-full rounded-xl border-black/10 bg-black/5 text-black/60" :value="$user->email" readonly autocomplete="email" />
            </div>

            <div class="info-row min-h-[128px] p-4">
                <x-input-label for="address" :value="__('Address')" />
                <x-text-input id="address" name="address" type="text" class="font-roboto mt-2 block h-12 w-full rounded-xl border-black/15 bg-white/95 focus:border-black focus:ring-black/20" :value="old('address', $user->address)" autocomplete="off" />
                <x-input-error class="mt-2" :messages="$errors->get('address')" />
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div class="info-row min-h-[128px] p-4">
                <x-input-label for="password" :value="__('New Password (optional)')" />
                <x-text-input id="password" name="password" type="password" class="font-roboto mt-2 block h-12 w-full rounded-xl border-black/15 bg-white/95 focus:border-black focus:ring-black/20" autocomplete="new-password" />
                <x-input-error class="mt-2" :messages="$errors->get('password')" />
            </div>

            <div class="info-row min-h-[128px] p-4">
                <x-input-label for="password_confirmation" :value="__('Confirm New Password')" />
                <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="font-roboto mt-2 block h-12 w-full rounded-xl border-black/15 bg-white/95 focus:border-black focus:ring-black/20" autocomplete="new-password" />
                <x-input-error class="mt-2" :messages="$errors->get('password_confirmation')" />
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div class="info-row min-h-[128px] border-black/20 p-4">
                <x-input-label for="current_password" :value="__('Current Password (required)')" />
                <x-text-input id="current_password" name="current_password" type="password" class="font-roboto mt-2 block h-12 w-full rounded-xl border-black/25 bg-white focus:border-black focus:ring-black/30" required autocomplete="current-password" />
                <x-input-error class="mt-2" :messages="$errors->get('current_password')" />
            </div>
        </div>

        <x-input-error class="mt-2" :messages="$errors->get('profile')" />

        <div class="flex items-center gap-4">
            <x-primary-button class="rounded-xl bg-black px-5 py-2.5 hover:bg-black/85 focus:ring-black/40">
                {{ __('Save Changes') }}
            </x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="font-roboto text-sm text-black/60"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
