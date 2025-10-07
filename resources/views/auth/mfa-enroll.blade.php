<x-layouts.app>
    <div class="max-w-lg mx-auto bg-white shadow rounded p-6 space-y-6">
        <h1 class="text-2xl font-semibold text-center">{{ __('Set up Multi-Factor Authentication') }}</h1>
        <p class="text-sm text-gray-600">{{ __('Scan the QR code with your authenticator app and enter the code to confirm.') }}</p>
        <div class="bg-gray-50 border rounded p-4 text-center space-y-2">
            <span class="text-sm text-gray-500">{{ __('Scan this key with your authenticator app:') }}</span>
            <code class="block text-lg tracking-widest">{{ $secret ?? 'SECRETKEY' }}</code>
        </div>
        <form method="POST" action="{{ route('mfa.verify') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium">{{ __('Verification Code') }}</label>
                <input type="text" name="code" class="mt-1 block w-full border rounded p-2 text-center tracking-widest" required />
            </div>
            <button type="submit" class="w-full py-2 bg-indigo-600 text-white rounded">{{ __('Enable MFA') }}</button>
        </form>
        <div class="bg-gray-100 rounded p-4 text-sm">
            <h2 class="font-semibold mb-2">{{ __('Recovery Codes') }}</h2>
            <ul class="grid grid-cols-2 gap-2 text-mono">
                @foreach (($recoveryCodes ?? []) as $code)
                    <li>{{ $code }}</li>
                @endforeach
            </ul>
        </div>
    </div>
</x-layouts.app>
