<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('dashboard') }}" class="font-display text-2xl leading-none tracking-tight md:text-[30px]">
                {{ __('Zippd') }}
            </a>
        </div>
    </x-slot>

    <div class="dashboard-fade-up py-8 md:py-10">
        <div class="mx-auto grid w-full gap-6 px-5 md:px-10 lg:px-14" style="max-width: 1120px;">
            <div class="dashboard-solid-card relative overflow-hidden rounded-2xl p-6 sm:p-8">
                <div style="position: absolute; top: 1.5rem; right: 1.5rem; z-index: 10;">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-xl border border-black/20 bg-white px-4 py-2 font-roboto text-sm font-medium text-black transition hover:bg-black/5 focus:outline-none focus:ring-2 focus:ring-black/20">
                        {{ __('Back to Dashboard') }}
                    </a>
                </div>
                <div class="max-w-3xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="dashboard-solid-card overflow-hidden rounded-2xl p-6 sm:p-8">
                <div class="max-w-3xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>

    @include('partials.mssql-console-debug')
</x-app-layout>
