<?php

namespace App\Http\Livewire\Auth;

use App\Services\Auth\MfaService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MfaChallenge extends Component
{
    public string $code = '';
    public ?string $error = null;

    public function __construct(public MfaService $mfaService)
    {
    }

    public function render()
    {
        return view('livewire.auth.mfa-challenge');
    }

    public function submit()
    {
        $this->validate([
            'code' => ['required', 'string', 'min:6', 'max:10'],
        ]);

        $user = Auth::user();
        abort_unless($user, 401);

        if (! $this->mfaService->verify($user, $this->code)) {
            $this->error = __('auth.mfa_failed');
            return;
        }

        session()->put('mfa_passed', true);
        session()->regenerate();

        $portalId = session()->pull('mfa_portal_id');
        if ($portalId) {
            return redirect()->route('sso.continue', ['portal' => $portalId]);
        }

        return redirect()->intended(route('dashboard'));
    }
}
