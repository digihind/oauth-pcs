<?php

namespace App\Http\Livewire\Auth;

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Component;

class LoginForm extends Component
{
    public string $employee_id = '';
    public string $password = '';
    public bool $remember = false;
    public ?string $error = null;

    public function render()
    {
        return view('livewire.auth.login-form');
    }

    public function submit()
    {
        $this->validate([
            'employee_id' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $key = $this->throttleKey();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            $this->error = trans('auth.throttle', ['seconds' => $seconds]);
            return;
        }

        if (! Auth::attempt(['employee_id' => $this->employee_id, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($key);
            $this->error = trans('auth.failed');
            return;
        }

        RateLimiter::clear($key);
        session()->regenerate();
        $this->redirectIntended(route('dashboard'));
    }

    protected function throttleKey(): string
    {
        return Str::lower($this->employee_id).'|'.request()->ip();
    }
}
