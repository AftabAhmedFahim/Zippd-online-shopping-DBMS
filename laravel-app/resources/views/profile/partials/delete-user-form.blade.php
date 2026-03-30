<section class="space-y-6">
    <header>
        <h2 class="font-mono text-3xl leading-none tracking-tight text-black">
            {{ __('Delete Account') }}
        </h2>

        <p class="font-roboto mt-2 text-sm text-black/65">
            {{ __('This action is permanent. Enter your current password and type Confirm to delete your account.') }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.destroy') }}" class="space-y-4 rounded-2xl bg-white/80 p-5 shadow-sm">
        @csrf
        @method('delete')

        <div class="info-row border-black/10 bg-white p-4">
            <x-input-label for="delete_password" :value="__('Current Password')" />
            <x-text-input id="delete_password" name="password" type="password" class="font-roboto mt-2 block h-12 w-full rounded-xl border-black/15 bg-white focus:border-black focus:ring-black/20" autocomplete="current-password" />
            <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
        </div>

        <div class="info-row border-black/10 bg-white p-4">
            <x-input-label for="delete_confirmation" :value="__('Type Confirm to continue')" />
            <x-text-input id="delete_confirmation" name="delete_confirmation" type="text" class="font-roboto mt-2 block h-12 w-full rounded-xl border-black/15 bg-white focus:border-black focus:ring-black/20" placeholder="Confirm" />
            <x-input-error :messages="$errors->userDeletion->get('delete_confirmation')" class="mt-2" />
        </div>

        <x-input-error :messages="$errors->userDeletion->get('delete')" class="mt-2" />

        <div class="pt-2">
            <x-danger-button class="rounded-xl bg-rose-600 px-5 py-2.5 hover:bg-rose-500 focus:ring-rose-400">
                {{ __('Delete My Account') }}
            </x-danger-button>
        </div>
    </form>
</section>
