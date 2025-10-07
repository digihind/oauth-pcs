<?php

use App\Http\Controllers\AuthController;
use App\Services\Auth\SsoTokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', fn (Request $request) => $request->user());
});

Route::post('/oauth/token', function (Request $request, SsoTokenService $tokenService) {
    $request->validate([
        'code' => ['required', 'string'],
        'code_verifier' => ['required', 'string'],
    ]);

    $authorization = session()->pull('auth_code_'.$request->input('code'));
    abort_unless($authorization, 400, 'Invalid authorization code');

    return response()->json($tokenService->exchangeCodeForTokens($authorization['payload'], $request->input('code_verifier')));
});
