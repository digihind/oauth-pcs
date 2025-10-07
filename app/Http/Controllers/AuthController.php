<?php

namespace App\Http\Controllers;

use App\Models\Portal;
use App\Services\Auth\MfaService;
use App\Services\Auth\SsoTokenService;
use App\Services\Auth\UserProvisioningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    public function __construct(
        protected SsoTokenService $tokenService,
        protected MfaService $mfaService,
        protected UserProvisioningService $provisioningService
    ) {
    }

    public function showLogin(Request $request)
    {
        return view('auth.login', [
            'portal' => $this->resolvePortal($request),
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'employee_id' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $portal = $this->resolvePortal($request);

        $this->ensureIsNotRateLimited($request);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey($request));
            return back()->withErrors(['employee_id' => __('auth.failed')]);
        }

        $user = $request->user();
        RateLimiter::clear($this->throttleKey($request));

        if ($portal && $portal->enforce_mfa && ! $user->mfa_enabled) {
            return redirect()->route('mfa.enroll');
        }

        if ($user->mfa_enabled && ! $request->session()->get('mfa_passed')) {
            $request->session()->put('mfa_portal_id', optional($portal)->id);
            return redirect()->route('mfa.challenge');
        }

        if ($portal) {
            return $this->handleSsoRedirect($request, $portal);
        }

        return redirect()->intended('/dashboard');
    }

    public function signup(Request $request)
    {
        $payload = $request->validate([
            'employee_id' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email'],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
        ]);

        $user = $this->provisioningService->createFromSignup($payload);

        abort_unless($user, 403, 'Employee ID not eligible for signup');

        Auth::login($user);

        return redirect()->intended('/dashboard');
    }

    public function showMfaEnroll(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 401);

        $secret = $user->mfaSetting?->secret ? decrypt($user->mfaSetting->secret) : $this->mfaService->generateSecret();
        $recovery = $user->mfaSetting?->recovery_codes ?? $this->mfaService->generateRecoveryCodes();

        $request->session()->put('mfa_pending_secret', $secret);
        $request->session()->put('mfa_pending_recovery', $recovery);

        return view('auth.mfa-enroll', [
            'secret' => $secret,
            'recoveryCodes' => $recovery,
        ]);
    }

    public function verifyMfa(Request $request)
    {
        $payload = $request->validate([
            'code' => ['required', 'string'],
        ]);

        $user = $request->user();
        abort_unless($user, 401);

        $secret = $request->session()->get('mfa_pending_secret');
        $recovery = $request->session()->get('mfa_pending_recovery', []);
        abort_unless($secret, 400, 'Missing MFA enrollment state');

        abort_unless($this->mfaService->verifySecret($secret, $payload['code']), 422, 'Invalid verification code');

        $this->mfaService->enable($user, $secret, $recovery);
        $user->forceFill(['mfa_enabled' => true])->save();

        $request->session()->forget(['mfa_pending_secret', 'mfa_pending_recovery']);

        return redirect()->route('dashboard')->with('status', __('MFA enabled successfully.'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    protected function handleSsoRedirect(Request $request, Portal $portal)
    {
        $scopes = explode(' ', (string) $request->input('scope', ''));
        $codeChallenge = $request->input('code_challenge');
        $codeChallengeMethod = $request->input('code_challenge_method', 'plain');

        $authorization = $this->tokenService->issueAuthorizationCode(
            $request->user(),
            $portal,
            $scopes,
            $codeChallenge,
            $codeChallengeMethod
        );

        return redirect()->away(
            sprintf(
                '%s?code=%s&state=%s',
                $portal->callback_url,
                $authorization->code,
                $request->input('state')
            )
        );
    }

    protected function resolvePortal(Request $request): ?Portal
    {
        $clientId = $request->input('client_id');
        return $clientId ? Portal::where('client_id', $clientId)->first() : null;
    }

    protected function ensureIsNotRateLimited(Request $request): void
    {
        if (RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            abort(429, __('auth.throttle', ['seconds' => RateLimiter::availableIn($this->throttleKey($request))]));
        }
    }

    protected function throttleKey(Request $request): string
    {
        return strtolower($request->input('employee_id')).'|'.$request->ip();
    }
}
