<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappContact extends Model
{
    protected $fillable = [
        'device_id',
        'phone_number',
        'whatsapp_id',
        'name',
        'push_name',
        'is_business',
        'is_group',
        'is_my_contact',
        'group_id',
        'group_participants',
        'profile_picture_url',
        'last_seen',
        'last_synced_at',
        'is_blocked',
        'metadata',
    ];

    protected $casts = [
        'is_business' => 'boolean',
        'is_group' => 'boolean',
        'is_my_contact' => 'boolean',
        'is_blocked' => 'boolean',
        'group_participants' => 'array',
        'metadata' => 'array',
        'last_seen' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    /**
     * Get the device that owns this contact
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(WhatsappDevice::class, 'device_id');
    }

    /**
     * Get formatted last synced time
     */
    public function getLastSyncedFormattedAttribute(): string
    {
        return $this->last_synced_at ? $this->last_synced_at->diffForHumans() : 'Never';
    }

    /**
     * Get display name (prioritize name over push_name)
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name ?: ($this->push_name ?: $this->phone_number);
    }
}
