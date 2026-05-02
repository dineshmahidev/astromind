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
            if (!Schema::hasColumn('consultations', 'duration')) {
                $table->integer('duration')->default(0)->after('status');
            }
            if (!Schema::hasColumn('consultations', 'start_time')) {
                $table->timestamp('start_time')->nullable()->after('duration');
            }
            if (!Schema::hasColumn('consultations', 'end_time')) {
                $table->timestamp('end_time')->nullable()->after('start_time');
            }
            if (!Schema::hasColumn('consultations', 'call_type')) {
                $table->enum('call_type', ['chat', 'audio', 'video'])->default('chat')->after('end_time');
            }
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('consultation_id')->nullable();
            $table->unsignedBigInteger('sender_id');
            $table->unsignedBigInteger('receiver_id');
            $table->enum('type', ['text', 'voice', 'image', 'call_request', 'system'])->default('text');
            $table->text('content');
            $table->boolean('is_read')->default(false);
            $table->integer('duration')->nullable(); // for voice/video
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            //
        });
    }
};
