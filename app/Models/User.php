<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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
        'role',
        'is_approved',
        'approved_at',
        'approved_by',
        'status',
        'limit_device',
        'account_expires_at',
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
            'approved_at' => 'datetime',
            'account_expires_at' => 'datetime',
            'password' => 'hashed',
            'is_approved' => 'boolean',
        ];
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is approved
     */
    public function isApproved(): bool
    {
        return $this->is_approved === true;
    }

    /**
     * Check if user can login (approved and active)
     */
    public function canLogin(): bool
    {
        return $this->isApproved() && $this->isActive();
    }

    /**
     * Check if user account is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && 
               ($this->account_expires_at === null || $this->account_expires_at->isFuture());
    }

    /**
     * Check if user account is expired
     */
    public function isExpired(): bool
    {
        return $this->account_expires_at !== null && $this->account_expires_at->isPast();
    }

    /**
     * Get the user who approved this user
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get users approved by this user
     */
    public function approvedUsers()
    {
        return $this->hasMany(User::class, 'approved_by');
    }

    // WhatsApp Relationships
    public function whatsappDevices()
    {
        return $this->hasMany(WhatsappDevice::class);
    }

    public function whatsappApiLogs()
    {
        return $this->hasMany(WhatsappApiLog::class);
    }

    public function whatsappTemplates()
    {
        return $this->hasMany(WhatsappTemplate::class);
    }

    // WhatsApp helper methods
    public function getActiveWhatsappDevicesCount()
    {
        return $this->whatsappDevices()->active()->count();
    }

    public function getConnectedWhatsappDevicesCount()
    {
        return $this->whatsappDevices()->connected()->count();
    }

    public function canCreateMoreDevices()
    {
        return $this->getActiveWhatsappDevicesCount() < $this->limit_device;
    }

    // Scopes
    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeUsers($query)
    {
        return $query->where('role', 'user');
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopePendingApproval($query)
    {
        return $query->where('is_approved', false);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
