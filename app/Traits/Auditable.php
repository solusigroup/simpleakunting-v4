<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    /**
     * Boot the auditable trait.
     */
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            $model->logAudit('CREATE', null, $model->getAttributes());
        });

        static::updated(function ($model) {
            $oldValues = $model->getOriginal();
            $newValues = $model->getChanges();
            
            // Only log if there are actual changes
            if (!empty($newValues)) {
                // Filter old values to only include changed fields
                $oldValues = array_intersect_key($oldValues, $newValues);
                $model->logAudit('UPDATE', $oldValues, $newValues);
            }
        });

        static::deleted(function ($model) {
            $model->logAudit('DELETE', $model->getOriginal(), null);
        });
    }

    /**
     * Log an audit entry.
     */
    protected function logAudit(string $action, ?array $oldValues, ?array $newValues): void
    {
        // Skip if no authenticated user or no company context
        $user = Auth::user();
        if (!$user) {
            return;
        }

        // Get company_id from the model or user
        $companyId = $this->company_id ?? $user->company_id ?? null;
        if (!$companyId) {
            return;
        }

        // Filter out sensitive/unnecessary fields
        $excludeFields = ['password', 'remember_token', 'updated_at', 'created_at'];
        
        if ($oldValues) {
            $oldValues = array_diff_key($oldValues, array_flip($excludeFields));
        }
        if ($newValues) {
            $newValues = array_diff_key($newValues, array_flip($excludeFields));
        }

        // Skip if no meaningful changes after filtering
        if ($action === 'UPDATE' && empty($newValues)) {
            return;
        }

        AuditLog::create([
            'company_id' => $companyId,
            'user_id' => $user->id,
            'action' => $action,
            'model_type' => static::class,
            'model_id' => $this->getKey(),
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Get audit logs for this model.
     */
    public function auditLogs()
    {
        return AuditLog::where('model_type', static::class)
            ->where('model_id', $this->getKey())
            ->orderBy('created_at', 'desc');
    }
}
