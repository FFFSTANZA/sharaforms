<?php

namespace App\Support;

use App\Models\ImpersonationAuditLog;
use Illuminate\Http\Request;

class ImpersonationAudit
{
    /**
     * @param array<string, mixed> $metadata
     * @param array<string, mixed>|null $payload
     */
    public static function record(
        int $impersonatorId,
        int $impersonatedUserId,
        string $action,
        ?Request $request = null,
        ?array $payload = null,
        array $metadata = []
    ): void {
        ImpersonationAuditLog::create([
            'impersonator_id' => $impersonatorId,
            'impersonated_user_id' => $impersonatedUserId,
            'action' => $action,
            'route_name' => $request?->route()?->getName(),
            'url' => $request?->fullUrl(),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'payload' => $payload,
            'metadata' => $metadata,
        ]);
    }
}
