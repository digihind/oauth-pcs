<?php

namespace App\Http\Livewire\Auth;

use App\Services\Auth\SocialAccountService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SocialLinkManager extends Component
{
    public array $providers = ['google', 'microsoft'];

    public function __construct(public SocialAccountService $socialAccountService)
    {
    }

    public function render()
    {
        return view('livewire.auth.social-link-manager', [
            'accounts' => Auth::user()?->socialAccounts()->get() ?? collect(),
        ]);
    }

    public function unlink(string $provider)
    {
        $user = Auth::user();
        abort_unless($user, 401);

        $this->socialAccountService->unlink($user, $provider);
        $this->dispatch('account-unlinked', provider: $provider);
    }
}
