<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImpersonationAuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'impersonator_id',
        'impersonated_user_id',
        'action',
        'route_name',
        'url',
        'ip_address',
        'user_agent',
        'payload',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'metadata' => 'array',
        ];
    }
}
