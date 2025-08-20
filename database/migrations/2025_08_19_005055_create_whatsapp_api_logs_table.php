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
        Schema::create('whatsapp_api_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('device_id')->nullable()->constrained('whatsapp_devices')->onDelete('set null');
            $table->string('endpoint'); // API endpoint called
            $table->string('method'); // HTTP method
            $table->string('ip_address');
            $table->string('user_agent')->nullable();
            $table->json('request_headers')->nullable();
            $table->json('request_payload')->nullable();
            $table->json('response_data')->nullable();
            $table->integer('response_code');
            $table->float('response_time')->nullable(); // Response time in seconds
            $table->enum('status', ['success', 'error', 'unauthorized', 'rate_limited'])->default('success');
            $table->text('error_message')->nullable();
            $table->timestamp('logged_at');
            $table->timestamps();
            
            $table->index(['user_id', 'logged_at']);
            $table->index(['device_id', 'logged_at']);
            $table->index(['endpoint', 'method']);
            $table->index(['status', 'logged_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_api_logs');
    }
};
