<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;

    protected $fillable = [
        'employee_id',
        'name',
        'email',
        'password',
        'department_id',
        'primary_user_type_id',
        'mfa_enabled',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'mfa_enabled' => 'boolean',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function userTypes()
    {
        return $this->belongsToMany(UserType::class)->withTimestamps();
    }

    public function portals()
    {
        return $this->belongsToMany(Portal::class)
            ->withPivot(['role_id', 'access_scope'])
            ->withTimestamps();
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class)
            ->withPivot(['granted_via'])
            ->withTimestamps();
    }

    public function socialAccounts()
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function mfaSetting()
    {
        return $this->hasOne(MfaSetting::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function loginAttempts()
    {
        return $this->hasMany(LoginAttempt::class);
    }

    public function scopeWithPortalAccess($query, Portal $portal)
    {
        return $query->whereHas('portals', fn ($q) => $q->where('portals.id', $portal->id));
    }

    public function createToken(string $name, array $abilities = ['*'], ?DateTimeInterface $expiresAt = null)
    {
        return parent::createToken($name, $abilities, $expiresAt);
    }
}
