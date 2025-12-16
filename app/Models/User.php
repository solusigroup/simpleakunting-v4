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
     * Get the company.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Check if user is Admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'Admin';
    }

    /**
     * Check if user is Manajer.
     */
    public function isManajer(): bool
    {
        return $this->role === 'Manajer';
    }

    /**
     * Check if user is Staff.
     */
    public function isStaff(): bool
    {
        return $this->role === 'Staff';
    }

    /**
     * Check if user is Viewer.
     */
    public function isViewer(): bool
    {
        return $this->role === 'Viewer';
    }

    /**
     * Check if user can create/edit data.
     */
    public function canEdit(): bool
    {
        return in_array($this->role, ['Admin', 'Manajer', 'Staff']);
    }

    /**
     * Check if user can delete data.
     */
    public function canDelete(): bool
    {
        return in_array($this->role, ['Admin', 'Manajer']);
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
        return $this->isAdmin();
    }

    /**
     * Check if user can approve/supervise.
     */
    public function canApprove(): bool
    {
        return in_array($this->role, ['Admin', 'Manajer']);
    }

    /**
     * Check if user can view reports.
     */
    public function canViewReports(): bool
    {
        return true; // All roles can view reports
    }
}
