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
            // Drop the old unique constraint that conflicts with groups
            $table->dropUnique(['device_id', 'phone_number']);
            
            // Add new unique constraint using whatsapp_id instead
            $table->unique(['device_id', 'whatsapp_id'], 'unique_device_whatsapp_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('whatsapp_contacts', function (Blueprint $table) {
            // Restore the old constraint
            $table->dropUnique('unique_device_whatsapp_id');
            $table->unique(['device_id', 'phone_number']);
        });
    }
};
