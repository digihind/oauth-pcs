<div class="space-y-4">
    <div class="flex flex-wrap gap-2">
        <select wire:model="type" class="border rounded p-2">
            <option value="audit">{{ __('Account Changes') }}</option>
            <option value="login">{{ __('Login Attempts') }}</option>
        </select>
        <input type="text" wire:model.debounce.500ms="search" placeholder="{{ __('Search...') }}" class="border rounded p-2" />
        <input type="date" wire:model="dateFrom" class="border rounded p-2" />
        <input type="date" wire:model="dateTo" class="border rounded p-2" />
    </div>
    <table class="min-w-full border divide-y text-sm">
        <thead>
            <tr class="bg-gray-50 text-left">
                <th class="px-3 py-2">{{ __('Timestamp') }}</th>
                <th class="px-3 py-2">{{ __('Action') }}</th>
                <th class="px-3 py-2">{{ __('Actor') }}</th>
                <th class="px-3 py-2">{{ __('IP Address') }}</th>
                <th class="px-3 py-2">{{ __('Context') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @foreach ($entries as $entry)
                <tr>
                    <td class="px-3 py-2">{{ $entry->created_at }}</td>
                    <td class="px-3 py-2">{{ $entry->action ?? $entry->status }}</td>
                    <td class="px-3 py-2">{{ optional($entry->actor ?? $entry->user)->name }}</td>
                    <td class="px-3 py-2">{{ $entry->ip_address }}</td>
                    <td class="px-3 py-2">
                        <pre class="bg-gray-100 p-2 rounded overflow-auto text-xs">{{ json_encode($entry->context ?? $entry->metadata, JSON_PRETTY_PRINT) }}</pre>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $entries->links() }}
</div>
