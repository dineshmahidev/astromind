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
        Schema::table('consultations', function (Blueprint $table) {
            // Drop the old foreign key that points to 'users'
            try {
                $table->dropForeign(['astrologer_id']);
            } catch (\Exception $e) {
                // If it doesn't exist, just continue
            }

            // Add the correct foreign key pointing to 'astrologers'
            $table->foreign('astrologer_id')
                  ->references('id')
                  ->on('astrologers')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropForeign(['astrologer_id']);
            $table->foreign('astrologer_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }
};
