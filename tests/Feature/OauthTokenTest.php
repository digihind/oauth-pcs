<?php

namespace Tests\Feature;

use App\Models\AuthorizationCode;
use App\Models\Portal;
use App\Models\User;
use App\Services\Auth\SsoTokenService;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class OauthTokenTest extends TestCase
{
    public function test_redeems_valid_authorization_code(): void
    {
        $service = new SsoTokenService();
        $user = new User(['id' => 1]);
        $portal = new Portal(['id' => 2]);

        $authorization = $service->issueAuthorizationCode(
            $user,
            $portal,
            ['profile'],
            'verifier',
            'plain'
        );

        $tokens = $service->redeemAuthorizationCode($authorization->code, 'verifier');

        $this->assertArrayHasKey('access_token', $tokens);
        $this->assertArrayHasKey('refresh_token', $tokens);
        $this->assertSame('Bearer', $tokens['token_type']);
        $this->assertSame(30 * 60, $tokens['expires_in']);
        $this->assertNull(AuthorizationCode::query()->find($authorization->code));
    }

    public function test_authorization_code_cannot_be_reused(): void
    {
        $service = new SsoTokenService();
        $user = new User(['id' => 1]);
        $portal = new Portal(['id' => 2]);

        $authorization = $service->issueAuthorizationCode(
            $user,
            $portal,
            ['profile'],
            'verifier',
            'plain'
        );

        $service->redeemAuthorizationCode($authorization->code, 'verifier');

        try {
            $service->redeemAuthorizationCode($authorization->code, 'verifier');
            $this->fail('Authorization code reuse should throw an exception.');
        } catch (HttpException $exception) {
            $this->assertSame(400, $exception->getStatusCode());
        }
    }

    public function test_authorization_code_expires(): void
    {
        $service = new SsoTokenService();

        AuthorizationCode::query()->create([
            'code' => 'expired',
            'payload' => [
                'user_id' => 1,
                'portal_id' => 2,
                'scopes' => ['profile'],
                'code_challenge' => 'verifier',
                'code_challenge_method' => 'plain',
            ],
            'expires_at' => now()->subMinute(),
        ]);

        try {
            $service->redeemAuthorizationCode('expired', 'verifier');
            $this->fail('Expired authorization codes must not be redeemable.');
        } catch (HttpException $exception) {
            $this->assertSame(400, $exception->getStatusCode());
        }

        $this->assertNull(AuthorizationCode::query()->find('expired'));
    }
}
