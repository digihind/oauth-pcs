<div class="space-y-4">
    <p>{{ __('Enter the verification code from your authenticator app or a recovery code.') }}</p>
    <form wire:submit.prevent="submit" class="space-y-4">
        <input type="text" wire:model.defer="code" class="w-full border rounded p-2 text-center text-xl tracking-widest" />
        @error('code')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror
        @if ($error)
            <p class="text-sm text-red-600">{{ $error }}</p>
        @endif
        <button type="submit" class="w-full py-2 bg-indigo-600 text-white rounded">
            {{ __('Verify') }}
        </button>
    </form>
</div>
