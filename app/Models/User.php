<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'company_id',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the role.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Check if user is Super User.
     */
    public function isSuper(): bool
    {
        return ($this->role()->exists() && $this->role->name === 'Super User') || $this->role === 'Super User';
    }

    /**
     * Check if user is Administrator.
     */
    public function isAdmin(): bool
    {
        if ($this->isSuper()) return true;
        return ($this->role()->exists() && $this->role->name === 'Administrator') || $this->role === 'Administrator';
    }

    /**
     * Check if user is Manajer.
     */
    public function isManajer(): bool
    {
        if ($this->isAdmin()) return true;
        return ($this->role()->exists() && $this->role->name === 'Manajer') || $this->role === 'Manajer';
    }

    /**
     * Check if user is Operator (Staff).
     */
    public function isOperator(): bool
    {
        if ($this->isManajer()) return true;
        return ($this->role()->exists() && $this->role->name === 'Operator') || $this->role === 'Operator';
    }

    /**
     * Check if user is Peninjau (Viewer).
     */
    public function isPeninjau(): bool
    {
        if ($this->isOperator()) return true;
        return ($this->role()->exists() && $this->role->name === 'Peninjau') || $this->role === 'Peninjau';
    }

    /**
     * Check if user can create/edit data.
     */
    public function canEdit(): bool
    {
        return $this->isAdmin() || $this->isManajer() || $this->isOperator();
    }

    /**
     * Check if user can delete data.
     */
    public function canDelete(): bool
    {
        return $this->isAdmin() || $this->isManajer();
    }

    /**
     * Check if user can manage company settings.
     */
    public function canManageCompany(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Check if user can manage users.
     */
    public function canManageUsers(): bool
    {
        return $this->isAdmin() || $this->isManajer();
    }

    /**
     * Check if user can approve/supervise.
     */
    public function canApprove(): bool
    {
        return $this->isAdmin() || $this->isManajer();
    }

    /**
     * Check if user has specific module permission.
     */
    public function hasPermission(string $permission): bool
    {
        // Super user has all permissions
        if ($this->isSuper()) {
            return true;
        }

        // Check if role is dynamic (database)
        if ($this->role()->exists()) {
            // Priority: direct permission check via relationship
            $hasPerm = $this->role->permissions()->where('slug', $permission)->exists();
            if ($hasPerm) return true;

            // Handle wildcards like 'sales.*'
            if (str_contains($permission, '.')) {
                $module = explode('.', $permission)[0];
                $wildcard = $module . '.*';
                if ($this->role->permissions()->where('slug', $wildcard)->exists()) {
                    return true;
                }
            }
        }

        // Fallback to hardcoded permissions if role is still a string or relationship doesn't have it
        $roleName = $this->role()->exists() ? $this->role->name : $this->role;
        return \App\Helpers\PermissionHelper::hasPermission($roleName, $permission);
    }

    /**
     * Check if user can view reports.
     */
    public function canViewReports(): bool
    {
        return $this->hasPermission('reports.view');
    }
}
