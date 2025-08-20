<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WhatsappDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'device_name',
        'device_key',
        'phone_number',
        'status',
        'qr_code',
        'last_seen',
        'connected_at',
        'device_info',
        'webhook_config',
        'is_active',
        'error_message',
    ];

    protected $casts = [
        'qr_code' => 'array',
        'device_info' => 'array',
        'webhook_config' => 'array',
        'last_seen' => 'datetime',
        'connected_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($device) {
            if (empty($device->device_key)) {
                $device->device_key = 'wa_' . Str::random(32);
            }
        });
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sessions()
    {
        return $this->hasMany(WhatsappSession::class, 'device_id');
    }

    public function messages()
    {
        return $this->hasMany(WhatsappMessage::class, 'device_id');
    }

    public function contacts()
    {
        return $this->hasMany(WhatsappContact::class, 'device_id');
    }

    public function webhooks()
    {
        return $this->hasMany(WhatsappWebhook::class, 'device_id');
    }

    public function apiLogs()
    {
        return $this->hasMany(WhatsappApiLog::class, 'device_id');
    }

    // Status methods
    public function isConnected()
    {
        return $this->status === 'connected';
    }

    public function isConnecting()
    {
        return $this->status === 'connecting';
    }

    public function isDisconnected()
    {
        return $this->status === 'disconnected';
    }

    public function hasError()
    {
        return $this->status === 'error';
    }

    // Utility methods
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'connected' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"><i class="fas fa-check-circle mr-1"></i>Connected</span>',
            'connecting' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800"><i class="fas fa-spinner fa-spin mr-1"></i>Connecting</span>',
            'error' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800"><i class="fas fa-exclamation-circle mr-1"></i>Error</span>',
            default => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800"><i class="fas fa-times-circle mr-1"></i>Disconnected</span>',
        };
    }

    public function getLastSeenFormattedAttribute()
    {
        if (!$this->last_seen) return 'Never';
        return $this->last_seen->diffForHumans();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeConnected($query)
    {
        return $query->where('status', 'connected');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
