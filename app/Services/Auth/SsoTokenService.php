<?php

namespace App\Services\Auth;

use App\Models\Portal;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Str;

class SsoTokenService
{
    public function issueAuthorizationCode(User $user, Portal $portal, array $scopes, string $codeChallenge, string $codeChallengeMethod): array
    {
        $authorizationCode = Str::random(64);

        return [
            'code' => $authorizationCode,
            'expires_at' => now()->addMinutes(10),
            'payload' => [
                'user_id' => $user->id,
                'portal_id' => $portal->id,
                'scopes' => $scopes,
                'code_challenge' => $codeChallenge,
                'code_challenge_method' => $codeChallengeMethod,
            ],
        ];
    }

    public function exchangeCodeForTokens(array $authorizationPayload, string $codeVerifier): array
    {
        $this->assertValidCodeVerifier($authorizationPayload, $codeVerifier);

        $accessToken = $this->createJwtToken($authorizationPayload, now()->addMinutes(config('sso.access_token_ttl')));
        $refreshToken = $this->createJwtToken($authorizationPayload, now()->addDays(config('sso.refresh_token_ttl')));

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'expires_in' => config('sso.access_token_ttl') * 60,
        ];
    }

    protected function assertValidCodeVerifier(array $authorizationPayload, string $codeVerifier): void
    {
        $challenge = $authorizationPayload['code_challenge'];
        $method = $authorizationPayload['code_challenge_method'];

        if ($method === 'S256') {
            $expected = rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');
            abort_unless(hash_equals($expected, $challenge), 400, 'Invalid PKCE verifier');
        } else {
            abort_unless(hash_equals($codeVerifier, $challenge), 400, 'Invalid PKCE verifier');
        }
    }

    protected function createJwtToken(array $payload, \DateTimeInterface $expiresAt): string
    {
        $claims = [
            'iss' => config('app.url'),
            'aud' => $payload['portal_id'],
            'sub' => $payload['user_id'],
            'exp' => $expiresAt->getTimestamp(),
            'iat' => now()->getTimestamp(),
            'scopes' => $payload['scopes'],
            'roles' => $payload['roles'] ?? [],
            'permissions' => $payload['permissions'] ?? [],
        ];

        return JWT::encode($claims, config('sso.signing_key'), config('sso.algorithm'));
    }

    public function parseToken(string $token): array
    {
        $decoded = JWT::decode($token, new Key(config('sso.signing_key'), config('sso.algorithm')));

        return (array) $decoded;
    }
}
