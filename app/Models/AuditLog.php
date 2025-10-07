<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'actor_id',
        'action',
        'auditable_type',
        'auditable_id',
        'before',
        'after',
        'ip_address',
        'user_agent',
        'context',
    ];

    protected $casts = [
        'before' => 'array',
        'after' => 'array',
        'context' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
