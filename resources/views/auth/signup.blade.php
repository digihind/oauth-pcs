<x-layouts.app>
    <div class="max-w-lg mx-auto bg-white shadow rounded p-6 space-y-6">
        <h1 class="text-2xl font-semibold text-center">{{ __('Complete your signup') }}</h1>
        <form method="POST" action="{{ route('signup.submit') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium">{{ __('Employee ID') }}</label>
                <input type="text" name="employee_id" value="{{ old('employee_id') }}" class="mt-1 block w-full border rounded p-2" required />
                @error('employee_id')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium">{{ __('Name') }}</label>
                <input type="text" name="name" value="{{ old('name') }}" class="mt-1 block w-full border rounded p-2" required />
                @error('name')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium">{{ __('Email') }}</label>
                <input type="email" name="email" value="{{ old('email') }}" class="mt-1 block w-full border rounded p-2" />
                @error('email')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium">{{ __('Password') }}</label>
                <input type="password" name="password" class="mt-1 block w-full border rounded p-2" required />
                @error('password')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium">{{ __('Confirm Password') }}</label>
                <input type="password" name="password_confirmation" class="mt-1 block w-full border rounded p-2" required />
            </div>
            <button type="submit" class="w-full py-2 bg-green-600 text-white rounded">{{ __('Create account') }}</button>
        </form>
    </div>
</x-layouts.app>
