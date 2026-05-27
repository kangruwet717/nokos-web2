<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLogService
{
    public function record(
        string $action,
        ?User $actor = null,
        ?Model $target = null,
        array $metadata = [],
        ?Request $request = null,
    ): AuditLog {
        $request ??= request();

        return AuditLog::create([
            'actor_user_id' => $actor?->id,
            'actor_role' => $actor?->role,
            'action' => $action,
            'target_type' => $target?->getMorphClass(),
            'target_id' => $target?->getKey(),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'metadata' => $metadata ?: null,
        ]);
    }
}
