<x-layouts.app>
    <div class="space-y-6">
        <h1 class="text-2xl font-semibold">{{ __('Portal Registry') }}</h1>
        <form method="POST" action="{{ route('admin.portals.store') }}" class="bg-white rounded shadow p-4 space-y-3">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium">{{ __('Name') }}</label>
                    <input type="text" name="name" class="mt-1 w-full border rounded p-2" required />
                </div>
                <div>
                    <label class="block text-sm font-medium">{{ __('Slug') }}</label>
                    <input type="text" name="slug" class="mt-1 w-full border rounded p-2" required />
                </div>
                <div>
                    <label class="block text-sm font-medium">{{ __('Callback URL') }}</label>
                    <input type="url" name="callback_url" class="mt-1 w-full border rounded p-2" required />
                </div>
                <div>
                    <label class="block text-sm font-medium">{{ __('Logout URL') }}</label>
                    <input type="url" name="logout_url" class="mt-1 w-full border rounded p-2" />
                </div>
            </div>
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="enforce_mfa" value="1" />
                <span>{{ __('Require MFA for this portal') }}</span>
            </label>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">{{ __('Register Portal') }}</button>
        </form>

        <div class="bg-white rounded shadow p-4">
            <h2 class="text-lg font-semibold mb-2">{{ __('Existing Portals') }}</h2>
            <p class="text-sm text-gray-600">{{ __('Manage portal clients via the Livewire permission matrix.') }}</p>
        </div>
    </div>
</x-layouts.app>
