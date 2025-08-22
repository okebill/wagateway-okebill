<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappMessage extends Model
{
    protected $fillable = [
        'device_id',
        'message_id',
        'chat_id',
        'direction',
        'type',
        'status',
        'content',
        'media_data',
        'from_number',
        'to_number',
        'from_name',
        'is_group',
        'group_name',
        'quoted_message',
        'sent_at',
        'delivered_at',
        'read_at',
        'metadata'
    ];

    protected $casts = [
        'media_data' => 'json',
        'quoted_message' => 'json',
        'metadata' => 'json',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'is_group' => 'boolean'
    ];

    // Relationship dengan WhatsappDevice
    public function device(): BelongsTo
    {
        return $this->belongsTo(WhatsappDevice::class);
    }
}
