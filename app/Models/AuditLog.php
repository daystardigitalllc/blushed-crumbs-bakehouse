<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'event_type',
        'severity',
        'ip_address',
        'user_agent',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record a security audit event safely.
     */
    public static function logEvent(
        string $eventType,
        ?int $tenantId = null,
        ?int $userId = null,
        array $payload = [],
        string $severity = 'info'
    ): ?self {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('audit_logs')) {
                \Illuminate\Support\Facades\Log::warning("AuditLog skipped: audit_logs table does not exist.");
                return null;
            }

            return static::create([
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'event_type' => $eventType,
                'severity' => $severity,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'payload' => $payload,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("AuditLog creation error: " . $e->getMessage());
            return null;
        }
    }
}
