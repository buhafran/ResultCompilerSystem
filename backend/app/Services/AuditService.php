<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

final class AuditService
{
    public function record(string $event, ?Model $model = null, ?array $before = null, ?array $after = null, ?int $schoolId = null): AuditLog
    {
        return AuditLog::create([
            'school_id' => $schoolId ?? data_get($model, 'school_id'),
            'user_id' => auth()->id(),
            'event' => $event,
            'auditable_type' => $model?->getMorphClass(),
            'auditable_id' => $model?->getKey(),
            'before' => $before,
            'after' => $after,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'created_at' => now(),
        ]);
    }
}
