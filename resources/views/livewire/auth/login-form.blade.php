<div>
    <form wire:submit.prevent="submit" class="space-y-4">
        <div>
            <label class="block text-sm font-medium">{{ __('Employee ID') }}</label>
            <input type="text" wire:model.defer="employee_id" class="mt-1 block w-full border rounded" />
            @error('employee_id')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block text-sm font-medium">{{ __('Password') }}</label>
            <input type="password" wire:model.defer="password" class="mt-1 block w-full border rounded" />
            @error('password')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="flex items-center">
            <input id="remember" type="checkbox" wire:model="remember" class="mr-2" />
            <label for="remember">{{ __('Remember me') }}</label>
        </div>
        @if ($error)
            <p class="text-sm text-red-600">{{ $error }}</p>
        @endif
        <button type="submit" class="w-full py-2 bg-blue-600 text-white rounded">
            {{ __('Sign in') }}
        </button>
    </form>
</div>
