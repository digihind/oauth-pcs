<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'default_role_id',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function defaultRole()
    {
        return $this->belongsTo(Role::class, 'default_role_id');
    }
}
