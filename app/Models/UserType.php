<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'default_role_id',
    ];

    public function portals()
    {
        return $this->belongsToMany(Portal::class)
            ->withPivot(['access_scope'])
            ->withTimestamps();
    }

    public function defaultRole()
    {
        return $this->belongsTo(Role::class, 'default_role_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
}
