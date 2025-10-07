<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\SocialAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function __construct(private SocialAccountService $socialAccountService)
    {
    }

    public function redirect(string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, ['google', 'microsoft']), 404);

        return Socialite::driver($provider)
            ->scopes(config("services.$provider.scopes", []))
            ->redirect();
    }

    public function callback(string $provider)
    {
        $socialiteUser = Socialite::driver($provider)->stateless()->user();

        $user = Auth::user();
        if (! $user) {
            $user = $this->socialAccountService->findOrCreateUser($provider, $socialiteUser);
            abort_unless($user, 403, 'No account matches provided identity');
            Auth::login($user);
        }

        $this->socialAccountService->link($user, $provider, $socialiteUser);
        $this->socialAccountService->provisionMissingEmail($user, $socialiteUser);

        return redirect()->route('profile.social')->with('status', __('Social account linked.'));
    }
}
