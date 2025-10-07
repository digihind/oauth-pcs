<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Auth Provider') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="min-h-screen flex flex-col">
        <nav class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 py-3 flex justify-between items-center">
                <span class="font-semibold">{{ config('app.name', 'Auth Provider') }}</span>
                <div class="space-x-3">
                    @auth
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button class="text-sm text-gray-700">{{ __('Logout') }}</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-sm text-gray-700">{{ __('Login') }}</a>
                    @endauth
                </div>
            </div>
        </nav>
        <main class="flex-1">
            <div class="max-w-5xl mx-auto py-10 px-4">
                {{ $slot }}
            </div>
        </main>
    </div>
    @livewireScripts
</body>
</html>
