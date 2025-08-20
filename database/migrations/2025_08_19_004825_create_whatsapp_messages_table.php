<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('whatsapp_devices')->onDelete('cascade');
            $table->string('message_id')->unique(); // WhatsApp message ID
            $table->string('chat_id'); // WhatsApp chat ID (phone number or group ID)
            $table->enum('direction', ['incoming', 'outgoing']);
            $table->enum('type', ['text', 'image', 'document', 'audio', 'video', 'location', 'contact', 'sticker'])->default('text');
            $table->enum('status', ['pending', 'sent', 'delivered', 'read', 'failed'])->default('pending');
            $table->text('content')->nullable(); // Message content/text
            $table->json('media_data')->nullable(); // Media file information
            $table->string('from_number')->nullable(); // Sender number
            $table->string('to_number')->nullable(); // Recipient number
            $table->string('from_name')->nullable(); // Sender name
            $table->boolean('is_group')->default(false);
            $table->string('group_name')->nullable();
            $table->json('quoted_message')->nullable(); // Quoted/replied message
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->json('metadata')->nullable(); // Additional message metadata
            $table->timestamps();
            
            $table->index(['device_id', 'direction']);
            $table->index(['chat_id', 'created_at']);
            $table->index('message_id');
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
    }
};
