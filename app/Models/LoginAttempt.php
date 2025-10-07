<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'employee_id',
        'status',
        'ip_address',
        'user_agent',
        'mfa_passed',
        'metadata',
    ];

    protected $casts = [
        'mfa_passed' => 'boolean',
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
