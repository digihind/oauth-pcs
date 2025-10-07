<x-layouts.app>
    <div class="max-w-md mx-auto bg-white shadow rounded p-6 space-y-6">
        <h1 class="text-2xl font-semibold text-center">{{ __('Sign in to continue') }}</h1>
        @livewire('auth.login-form')
        <div class="text-sm text-center">
            <a href="{{ route('signup') }}" class="text-blue-600">{{ __('Need an account? Complete signup') }}</a>
        </div>
    </div>
</x-layouts.app>
