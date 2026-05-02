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
        Schema::create('astrologers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('specialization'); // e.g. Vedic, Numerology
            $table->integer('experience'); // years
            $table->string('languages'); // comma separated
            $table->text('bio');
            $table->decimal('price_per_minute', 8, 2);
            $table->float('rating')->default(5.0);
            $table->string('profile_image')->nullable();
            $table->boolean('is_online')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('astrologers');
    }
};
