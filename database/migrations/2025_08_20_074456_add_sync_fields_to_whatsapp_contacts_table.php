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
        Schema::table('whatsapp_contacts', function (Blueprint $table) {
            $table->string('whatsapp_id')->nullable()->after('phone_number'); // WhatsApp contact ID
            $table->boolean('is_my_contact')->default(false)->after('is_group'); // Is in user's contact list
            $table->timestamp('last_synced_at')->nullable()->after('last_seen'); // Last time contact was synced
            
            // Add index for whatsapp_id for faster lookups
            $table->index(['device_id', 'whatsapp_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('whatsapp_contacts', function (Blueprint $table) {
            $table->dropIndex(['device_id', 'whatsapp_id']);
            $table->dropColumn(['whatsapp_id', 'is_my_contact', 'last_synced_at']);
        });
    }
};
