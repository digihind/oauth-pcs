<?php

namespace App\Services\Auth;

use App\Models\MfaSetting;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use OTPHP\TOTP;

class MfaService
{
    public function generateSecret(): string
    {
        return Str::random(32);
    }

    public function enable(User $user, string $secret, array $recoveryCodes): MfaSetting
    {
        return $user->mfaSetting()->updateOrCreate([], [
            'secret' => encrypt($secret),
            'recovery_codes' => $recoveryCodes,
            'enabled_at' => now(),
        ]);
    }

    public function disable(User $user): void
    {
        $user->mfaSetting()?->delete();
        $user->forceFill(['mfa_enabled' => false])->save();
    }

    public function verify(User $user, string $code): bool
    {
        $setting = $user->mfaSetting;
        if (! $setting) {
            return false;
        }

        if ($this->verifySecret(decrypt($setting->secret), $code)) {
            $setting->update(['last_used_at' => now()]);
            return true;
        }

        if (in_array($code, $setting->recovery_codes, true)) {
            $remaining = Arr::where($setting->recovery_codes, fn ($value) => $value !== $code);
            $setting->update(['recovery_codes' => array_values($remaining)]);
            return true;
        }

        return false;
    }

    public function verifySecret(string $secret, string $code): bool
    {
        $totp = TOTP::create($secret);
        return $totp->verify($code);
    }

    public function generateRecoveryCodes(int $count = 8): array
    {
        return collect(range(1, $count))
            ->map(fn () => Str::upper(Str::random(10)))
            ->toArray();
    }
}
