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
        Schema::create('whatsapp_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('whatsapp_devices')->onDelete('cascade');
            $table->string('session_id')->unique();
            $table->longText('session_data')->nullable(); // Store session auth data
            $table->enum('status', ['active', 'expired', 'invalid'])->default('active');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_activity')->nullable();
            $table->json('metadata')->nullable(); // Additional session metadata
            $table->timestamps();
            
            $table->index(['device_id', 'status']);
            $table->index('session_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_sessions');
    }
};
