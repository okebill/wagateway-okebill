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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'user'])->default('user')->after('password');
            $table->boolean('is_approved')->default(false)->after('role');
            $table->timestamp('approved_at')->nullable()->after('is_approved');
            $table->unsignedBigInteger('approved_by')->nullable()->after('approved_at');
            
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['role', 'is_approved']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropIndex(['role', 'is_approved']);
            $table->dropColumn(['role', 'is_approved', 'approved_at', 'approved_by']);
        });
    }
};
