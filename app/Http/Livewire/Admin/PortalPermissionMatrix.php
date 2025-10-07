<?php

namespace App\Http\Livewire\Admin;

use App\Models\Permission;
use App\Models\Portal;
use App\Models\Role;
use Livewire\Component;

class PortalPermissionMatrix extends Component
{
    public ?int $selectedPortalId = null;

    public function render()
    {
        $portals = Portal::orderBy('name')->get();
        $portal = $this->selectedPortalId ? $portals->firstWhere('id', $this->selectedPortalId) : $portals->first();

        return view('livewire.admin.portal-permission-matrix', [
            'portals' => $portals,
            'portal' => $portal,
            'roles' => Role::orderBy('name')->get(),
            'permissions' => Permission::orderBy('name')->get(),
        ]);
    }

    public function toggleRole(int $portalId, int $roleId)
    {
        $portal = Portal::findOrFail($portalId);
        $portal->roles()->toggle($roleId);
        $this->dispatch('portal-role-updated', portal: $portalId, role: $roleId);
    }

    public function togglePermission(int $portalId, int $permissionId)
    {
        $portal = Portal::findOrFail($portalId);
        $portal->permissions()->toggle($permissionId);
        $this->dispatch('portal-permission-updated', portal: $portalId, permission: $permissionId);
    }
}
