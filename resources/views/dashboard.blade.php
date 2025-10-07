<x-layouts.app>
    <div class="space-y-4">
        <h1 class="text-3xl font-semibold">{{ __('Welcome back, :name', ['name' => auth()->user()->name ?? '']) }}</h1>
        <p class="text-gray-600">{{ __('You are signed in via the centralized authentication provider.') }}</p>
        <div class="bg-white shadow rounded p-4">
            <h2 class="text-xl font-semibold mb-2">{{ __('Connected Portals') }}</h2>
            <ul class="space-y-1">
                @foreach (auth()->user()->portals ?? [] as $portal)
                    <li>{{ $portal->name }}</li>
                @endforeach
            </ul>
        </div>
    </div>
</x-layouts.app>
