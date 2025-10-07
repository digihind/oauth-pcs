<?php

use App\Http\Controllers\Admin\PortalAccessController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\AuthController;
use App\Http\Livewire\Admin\AuditTrailTable;
use App\Http\Livewire\Admin\PortalPermissionMatrix;
use App\Http\Livewire\Admin\UserManager;
use App\Http\Livewire\Auth\LoginForm;
use App\Http\Livewire\Auth\MfaChallenge;
use App\Http\Livewire\Auth\SocialLinkManager;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
    Route::get('/signup', fn () => view('auth.signup'))->name('signup');
    Route::post('/signup', [AuthController::class, 'signup'])->name('signup.submit');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');

    Route::get('/mfa/challenge', MfaChallenge::class)->name('mfa.challenge');
    Route::get('/mfa/enroll', [AuthController::class, 'showMfaEnroll'])->name('mfa.enroll');
    Route::post('/mfa/enroll', [AuthController::class, 'verifyMfa'])->name('mfa.verify');

    Route::get('/profile/social', SocialLinkManager::class)->name('profile.social');
    Route::delete('/profile/social/{provider}', [SocialLinkManager::class, 'unlink'])->name('profile.social.unlink');

    Route::prefix('admin')->middleware('can:manage-users')->group(function () {
        Route::get('/users', UserManager::class)->name('admin.users');
        Route::get('/portals', [PortalAccessController::class, 'index'])->name('admin.portals');
        Route::post('/portals', [PortalAccessController::class, 'store'])->name('admin.portals.store');
        Route::put('/portals/{portal}', [PortalAccessController::class, 'update'])->name('admin.portals.update');
        Route::get('/permissions', PortalPermissionMatrix::class)->name('admin.permissions');
        Route::get('/audits', AuditTrailTable::class)->name('admin.audits');
    });
});

Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])->name('social.redirect');
Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])->name('social.callback');
