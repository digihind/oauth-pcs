<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sso_clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('client_id')->unique();
            $table->string('client_secret');
            $table->string('redirect_uri');
            $table->json('scopes')->nullable();
            $table->boolean('enforce_mfa')->default(false);
            $table->timestamps();
        });

        Schema::create('sso_authorizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('client_id')->constrained('sso_clients');
            $table->string('code')->unique();
            $table->string('code_challenge');
            $table->string('code_challenge_method');
            $table->json('scopes');
            $table->timestamp('expires_at');
            $table->timestamps();
        });

        Schema::create('sso_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('client_id')->constrained('sso_clients');
            $table->string('refresh_token')->unique();
            $table->json('claims');
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sso_tokens');
        Schema::dropIfExists('sso_authorizations');
        Schema::dropIfExists('sso_clients');
    }
};
