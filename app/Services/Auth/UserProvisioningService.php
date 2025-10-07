<?php

namespace App\Services\Auth;

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserProvisioningService
{
    public function import(array $rows): void
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                $this->upsertUser($row);
            }
        });
    }

    public function upsertUser(array $payload): User
    {
        $department = Department::firstOrCreate(
            ['code' => $payload['department_code']],
            ['name' => $payload['department_name'] ?? $payload['department_code']]
        );

        $user = User::updateOrCreate(
            ['employee_id' => $payload['employee_id']],
            [
                'name' => $payload['name'],
                'email' => $payload['email'] ?? null,
                'department_id' => $department->id,
                'password' => $payload['password'] ?? Hash::make(str()->random(32)),
            ]
        );

        if (! empty($payload['roles'])) {
            $roleIds = collect($payload['roles'])
                ->map(fn ($role) => Role::firstOrCreate(['slug' => $role], ['name' => str($role)->title()])->id)
                ->all();
            $user->roles()->syncWithoutDetaching($roleIds);
        }

        if (! empty($payload['user_types'])) {
            $typeIds = collect($payload['user_types'])
                ->map(fn ($type) => UserType::firstOrCreate(['slug' => $type], ['name' => str($type)->title()])->id)
                ->all();
            $user->userTypes()->syncWithoutDetaching($typeIds);
        }

        return $user;
    }

    public function eligibleForSignup(string $employeeId): bool
    {
        return User::where('employee_id', $employeeId)->exists();
    }

    public function createFromSignup(array $payload): ?User
    {
        if (! $this->eligibleForSignup($payload['employee_id'])) {
            return null;
        }

        $user = User::where('employee_id', $payload['employee_id'])->firstOrFail();

        $user->forceFill([
            'name' => $payload['name'] ?? $user->name,
            'email' => $payload['email'] ?? $user->email,
            'password' => Hash::make($payload['password']),
        ])->save();

        return $user;
    }

    public function prepareImportPreview(array $rows): array
    {
        return collect($rows)
            ->map(fn ($row) => [
                'employee_id' => Arr::get($row, 'employee_id'),
                'name' => Arr::get($row, 'name'),
                'department' => Arr::get($row, 'department_code'),
                'roles' => Arr::get($row, 'roles', []),
                'user_types' => Arr::get($row, 'user_types', []),
            ])
            ->all();
    }
}
