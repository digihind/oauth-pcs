<div class="space-y-6">
    <h2 class="text-lg font-semibold">{{ __('Linked Accounts') }}</h2>
    <ul class="space-y-2">
        @foreach ($accounts as $account)
            <li class="flex items-center justify-between border rounded p-3">
                <span>{{ ucfirst($account->provider) }} — {{ $account->provider_email }}</span>
                <button wire:click="unlink('{{ $account->provider }}')" class="text-sm text-red-600">
                    {{ __('Unlink') }}
                </button>
            </li>
        @endforeach
    </ul>
    <div class="space-y-2">
        <p class="text-sm text-gray-600">{{ __('Link a new provider:') }}</p>
        @foreach ($providers as $provider)
            <a href="{{ route('social.redirect', $provider) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded">
                {{ __('Link :provider', ['provider' => ucfirst($provider)]) }}
            </a>
        @endforeach
    </div>
</div>
