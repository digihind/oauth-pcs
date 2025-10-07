<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->foreignId('default_role_id')->nullable()->constrained('roles');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('is_admin')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('user_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('default_role_id')->nullable()->constrained('roles');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->unique();
            $table->string('name');
            $table->string('email')->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->foreignId('department_id')->nullable()->constrained('departments');
            $table->foreignId('primary_user_type_id')->nullable()->constrained('user_types');
            $table->boolean('mfa_enabled')->default(false);
            $table->timestamp('last_login_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('is_global')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('portals', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('client_id')->unique();
            $table->string('client_secret');
            $table->string('callback_url');
            $table->string('logout_url')->nullable();
            $table->boolean('enforce_mfa')->default(false);
            $table->json('scopes')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->timestamps();
        });

        Schema::create('permission_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permission_id')->constrained();
            $table->foreignId('role_id')->constrained();
            $table->timestamps();
        });

        Schema::create('permission_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permission_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->string('granted_via')->nullable();
            $table->timestamps();
        });

        Schema::create('portal_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portal_id')->constrained();
            $table->foreignId('role_id')->constrained();
            $table->timestamps();
        });

        Schema::create('permission_portal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permission_id')->constrained();
            $table->foreignId('portal_id')->constrained();
            $table->timestamps();
        });

        Schema::create('portal_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portal_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('role_id')->nullable()->constrained('roles');
            $table->string('access_scope')->nullable();
            $table->timestamps();
        });

        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('provider');
            $table->string('provider_user_id');
            $table->string('provider_email')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('last_linked_at')->nullable();
            $table->timestamps();
            $table->unique(['provider', 'provider_user_id']);
        });

        Schema::create('mfa_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->text('secret');
            $table->json('recovery_codes');
            $table->json('trusted_devices')->nullable();
            $table->timestamp('enabled_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mfa_settings');
        Schema::dropIfExists('social_accounts');
        Schema::dropIfExists('portal_user');
        Schema::dropIfExists('permission_portal');
        Schema::dropIfExists('portal_role');
        Schema::dropIfExists('permission_user');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('portals');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('users');
        Schema::dropIfExists('user_types');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('departments');
    }
};
