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
        Schema::create('whatsapp_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Template name
            $table->text('content'); // Template content with variables
            $table->enum('type', ['text', 'media', 'location', 'contact'])->default('text');
            $table->json('variables')->nullable(); // Template variables {name}, {phone}, etc
            $table->json('media_data')->nullable(); // Media template data
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('usage_count')->default(0);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'is_active']);
            $table->index(['type', 'is_active']);
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_templates');
    }
};
