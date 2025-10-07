<div class="space-y-4">
    <div class="flex gap-4">
        <input type="text" wire:model.debounce.500ms="search" placeholder="{{ __('Search users...') }}" class="flex-1 border rounded p-2" />
        <select wire:model="departmentId" class="border rounded p-2">
            <option value="">{{ __('All Departments') }}</option>
            @foreach ($departments as $department)
                <option value="{{ $department->id }}">{{ $department->name }}</option>
            @endforeach
        </select>
    </div>
    <table class="min-w-full border divide-y">
        <thead>
            <tr class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wider">
                <th class="px-3 py-2">{{ __('Employee') }}</th>
                <th class="px-3 py-2">{{ __('Department') }}</th>
                <th class="px-3 py-2">{{ __('Roles') }}</th>
                <th class="px-3 py-2">{{ __('User Types') }}</th>
                <th class="px-3 py-2">{{ __('MFA') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @foreach ($users as $user)
                <tr>
                    <td class="px-3 py-2">
                        <div class="font-medium">{{ $user->name }}</div>
                        <div class="text-sm text-gray-500">{{ $user->employee_id }}</div>
                    </td>
                    <td class="px-3 py-2">{{ optional($user->department)->name }}</td>
                    <td class="px-3 py-2 space-y-1">
                        @foreach ($roles as $role)
                            <label class="flex items-center text-sm gap-2">
                                <input type="checkbox" wire:click="toggleRole({{ $user->id }}, {{ $role->id }})" @checked($user->roles->contains($role)) />
                                <span>{{ $role->name }}</span>
                            </label>
                        @endforeach
                    </td>
                    <td class="px-3 py-2 space-y-1">
                        @foreach ($types as $type)
                            <label class="flex items-center text-sm gap-2">
                                <input type="checkbox" wire:click="toggleType({{ $user->id }}, {{ $type->id }})" @checked($user->userTypes->contains($type)) />
                                <span>{{ $type->name }}</span>
                            </label>
                        @endforeach
                    </td>
                    <td class="px-3 py-2">
                        <span class="text-sm">{{ $user->mfa_enabled ? __('Enabled') : __('Disabled') }}</span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div>
        {{ $users->links() }}
    </div>
    @if ($importPreview)
        <div class="border rounded p-4 bg-yellow-50">
            <h3 class="font-semibold mb-2">{{ __('Import Preview') }}</h3>
            <ul class="space-y-1 text-sm">
                @foreach ($importPreview as $row)
                    <li>{{ $row['employee_id'] }} — {{ $row['name'] }} ({{ $row['department'] }})</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
