<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MfaSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'secret',
        'recovery_codes',
        'enabled_at',
        'last_used_at',
        'trusted_devices',
    ];

    protected $casts = [
        'recovery_codes' => 'array',
        'trusted_devices' => 'array',
        'enabled_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
