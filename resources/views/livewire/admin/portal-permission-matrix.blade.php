<div class="space-y-4">
    <div class="flex gap-2">
        @foreach ($portals as $portalOption)
            <button wire:click="$set('selectedPortalId', {{ $portalOption->id }})" class="px-3 py-1 rounded {{ optional($portal)->id === $portalOption->id ? 'bg-blue-600 text-white' : 'bg-gray-200' }}">
                {{ $portalOption->name }}
            </button>
        @endforeach
    </div>

    @if ($portal)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="font-semibold mb-2">{{ __('Roles') }}</h3>
                <ul class="space-y-2">
                    @foreach ($roles as $role)
                        <li class="flex items-center gap-2">
                            <input type="checkbox" wire:click="toggleRole({{ $portal->id }}, {{ $role->id }})" @checked($portal->roles->contains($role)) />
                            <span>{{ $role->name }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div>
                <h3 class="font-semibold mb-2">{{ __('Permissions') }}</h3>
                <ul class="space-y-2">
                    @foreach ($permissions as $permission)
                        <li class="flex items-center gap-2">
                            <input type="checkbox" wire:click="togglePermission({{ $portal->id }}, {{ $permission->id }})" @checked($portal->permissions->contains($permission)) />
                            <span>{{ $permission->name }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @else
        <p class="text-sm text-gray-600">{{ __('Select a portal to manage access.') }}</p>
    @endif
</div>
