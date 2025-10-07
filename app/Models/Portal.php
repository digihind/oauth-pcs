<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Portal extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'callback_url',
        'logout_url',
        'enforce_mfa',
        'client_id',
        'client_secret',
        'scopes',
    ];

    protected $casts = [
        'enforce_mfa' => 'boolean',
        'scopes' => 'array',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class)->withTimestamps();
    }

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['role_id', 'access_scope'])
            ->withTimestamps();
    }

    public function userTypes()
    {
        return $this->belongsToMany(UserType::class)
            ->withPivot(['access_scope'])
            ->withTimestamps();
    }
}
