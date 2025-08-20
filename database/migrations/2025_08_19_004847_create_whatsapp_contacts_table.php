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
        Schema::create('whatsapp_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('whatsapp_devices')->onDelete('cascade');
            $table->string('phone_number'); // WhatsApp number
            $table->string('name')->nullable(); // Contact name
            $table->string('push_name')->nullable(); // WhatsApp push name
            $table->boolean('is_business')->default(false);
            $table->boolean('is_group')->default(false);
            $table->string('group_id')->nullable(); // If it's a group
            $table->json('group_participants')->nullable(); // Group participants
            $table->string('profile_picture_url')->nullable();
            $table->timestamp('last_seen')->nullable();
            $table->boolean('is_blocked')->default(false);
            $table->json('metadata')->nullable(); // Additional contact data
            $table->timestamps();
            
            $table->unique(['device_id', 'phone_number']);
            $table->index(['device_id', 'is_group']);
            $table->index('phone_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_contacts');
    }
};
