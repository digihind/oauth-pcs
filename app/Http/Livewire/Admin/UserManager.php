<?php

namespace App\Http\Livewire\Admin;

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use App\Models\UserType;
use App\Services\Auth\UserProvisioningService;
use Livewire\Component;
use Livewire\WithPagination;

class UserManager extends Component
{
    use WithPagination;

    public string $search = '';
    public array $selectedRoles = [];
    public array $selectedTypes = [];
    public ?int $departmentId = null;
    public array $importPreview = [];

    public function __construct(public UserProvisioningService $provisioningService)
    {
    }

    public function render()
    {
        $query = User::query()->with(['roles', 'department', 'userTypes']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('employee_id', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            });
        }

        if ($this->departmentId) {
            $query->where('department_id', $this->departmentId);
        }

        if ($this->selectedRoles) {
            $query->whereHas('roles', fn ($q) => $q->whereIn('roles.id', $this->selectedRoles));
        }

        if ($this->selectedTypes) {
            $query->whereHas('userTypes', fn ($q) => $q->whereIn('user_types.id', $this->selectedTypes));
        }

        return view('livewire.admin.user-manager', [
            'users' => $query->paginate(15),
            'roles' => Role::orderBy('name')->get(),
            'types' => UserType::orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
            'importPreview' => $this->importPreview,
        ]);
    }

    public function toggleRole(int $userId, int $roleId)
    {
        $user = User::findOrFail($userId);
        $user->roles()->toggle($roleId);
        $this->dispatch('role-updated', user: $userId, role: $roleId);
    }

    public function toggleType(int $userId, int $typeId)
    {
        $user = User::findOrFail($userId);
        $user->userTypes()->toggle($typeId);
        $this->dispatch('type-updated', user: $userId, type: $typeId);
    }

    public function import(array $rows)
    {
        $this->provisioningService->import($rows);
        $this->resetPage();
    }

    public function previewImport(array $rows)
    {
        $this->importPreview = $this->provisioningService->prepareImportPreview($rows);
    }
}
