<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'company_id',
        'user_id',
        'action',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get the company.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user who performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the audited model.
     */
    public function auditable()
    {
        return $this->morphTo('auditable', 'model_type', 'model_id');
    }

    /**
     * Scope for filtering by model type.
     */
    public function scopeForModel($query, string $modelType)
    {
        return $query->where('model_type', $modelType);
    }

    /**
     * Scope for filtering by user.
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for filtering by action.
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope for filtering by date range.
     */
    public function scopeDateRange($query, ?string $from, ?string $to)
    {
        if ($from) {
            $query->where('created_at', '>=', $from);
        }
        if ($to) {
            $query->where('created_at', '<=', $to);
        }
        return $query;
    }

    /**
     * Get human-readable model name.
     */
    public function getModelNameAttribute(): string
    {
        $map = [
            'App\\Models\\Journal' => 'Jurnal',
            'App\\Models\\ChartOfAccount' => 'Akun',
            'App\\Models\\Contact' => 'Kontak',
            'App\\Models\\Invoice' => 'Invoice',
            'App\\Models\\Inventory' => 'Persediaan',
            'App\\Models\\FixedAsset' => 'Aset Tetap',
            'App\\Models\\Budget' => 'Anggaran',
        ];
        
        return $map[$this->model_type] ?? class_basename($this->model_type);
    }

    /**
     * Get human-readable action name.
     */
    public function getActionNameAttribute(): string
    {
        $map = [
            'CREATE' => 'Dibuat',
            'UPDATE' => 'Diubah',
            'DELETE' => 'Dihapus',
        ];
        
        return $map[$this->action] ?? $this->action;
    }
}
