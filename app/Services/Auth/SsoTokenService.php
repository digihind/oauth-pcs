<?php

namespace App\Services\Auth;

use App\Models\AuthorizationCode;
use App\Models\Portal;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SsoTokenService
{
    public function issueAuthorizationCode(User $user, Portal $portal, array $scopes, string $codeChallenge, string $codeChallengeMethod): AuthorizationCode
    {
        $authorizationCode = Str::random(64);

        return AuthorizationCode::create([
            'code' => $authorizationCode,
            'expires_at' => now()->addMinutes(10),
            'payload' => [
                'user_id' => $user->id,
                'portal_id' => $portal->id,
                'scopes' => $scopes,
                'code_challenge' => $codeChallenge,
                'code_challenge_method' => $codeChallengeMethod,
            ],
        ]);
    }

    public function redeemAuthorizationCode(string $code, string $codeVerifier): array
    {
        $authorization = AuthorizationCode::query()->find($code);
        if (! $authorization) {
            throw new HttpException(400, 'Invalid authorization code');
        }

        if ($authorization->hasExpired()) {
            $authorization->delete();
            throw new HttpException(400, 'Authorization code expired');
        }

        $payload = $authorization->payload;

        $authorization->delete();

        return $this->exchangeCodeForTokens($payload, $codeVerifier);
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

        $isValid = match ($method) {
            'S256' => hash_equals(
                rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '='),
                $challenge
            ),
            default => hash_equals($codeVerifier, $challenge),
        };

        if (! $isValid) {
            throw new HttpException(400, 'Invalid PKCE verifier');
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
