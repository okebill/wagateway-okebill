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
        Schema::create('whatsapp_webhooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('whatsapp_devices')->onDelete('cascade');
            $table->string('webhook_url');
            $table->enum('event_type', ['message', 'status', 'connection', 'all'])->default('all');
            $table->json('payload'); // Webhook payload data
            $table->enum('status', ['pending', 'sent', 'failed', 'retry'])->default('pending');
            $table->integer('attempts')->default(0);
            $table->integer('max_attempts')->default(3);
            $table->text('response')->nullable(); // Webhook response
            $table->integer('response_code')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('next_retry_at')->nullable();
            $table->json('headers')->nullable(); // Custom headers
            $table->timestamps();
            
            $table->index(['device_id', 'status']);
            $table->index(['event_type', 'created_at']);
            $table->index('next_retry_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_webhooks');
    }
};
