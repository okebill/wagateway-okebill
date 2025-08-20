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
        Schema::create('whatsapp_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('device_name');
            $table->string('device_key')->unique(); // Unique identifier for API
            $table->string('phone_number')->nullable();
            $table->enum('status', ['disconnected', 'connecting', 'connected', 'error'])->default('disconnected');
            $table->json('qr_code')->nullable(); // Store QR code data
            $table->timestamp('last_seen')->nullable();
            $table->timestamp('connected_at')->nullable();
            $table->json('device_info')->nullable(); // Store device information
            $table->json('webhook_config')->nullable(); // Webhook configuration
            $table->boolean('is_active')->default(true);
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index('device_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_devices');
    }
};
