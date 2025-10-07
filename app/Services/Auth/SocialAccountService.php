<?php

namespace App\Services\Auth;

use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class SocialAccountService
{
    public function link(User $user, string $provider, SocialiteUser $socialiteUser): SocialAccount
    {
        return $user->socialAccounts()->updateOrCreate(
            [
                'provider' => $provider,
                'provider_user_id' => $socialiteUser->getId(),
            ],
            [
                'provider_email' => $socialiteUser->getEmail(),
                'meta' => [
                    'name' => $socialiteUser->getName(),
                    'avatar' => $socialiteUser->getAvatar(),
                ],
                'last_linked_at' => now(),
            ]
        );
    }

    public function unlink(User $user, string $provider): void
    {
        $user->socialAccounts()->where('provider', $provider)->delete();
    }

    public function findOrCreateUser(string $provider, SocialiteUser $socialiteUser): ?User
    {
        $account = SocialAccount::where('provider', $provider)
            ->where('provider_user_id', $socialiteUser->getId())
            ->first();

        if ($account) {
            return $account->user;
        }

        $employeeId = $socialiteUser->user['employee_id'] ?? null;
        if (! $employeeId) {
            return null;
        }

        return User::where('employee_id', $employeeId)->first();
    }

    public function provisionMissingEmail(User $user, SocialiteUser $socialiteUser): void
    {
        if (! $user->email && $socialiteUser->getEmail()) {
            $user->forceFill([
                'email' => $socialiteUser->getEmail(),
                'email_verified_at' => now(),
            ])->save();
        }
    }

    public function randomPassword(): string
    {
        return Hash::make(Str::random(24));
    }
}
