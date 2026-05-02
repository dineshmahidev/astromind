<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('astrologer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('question');
            $table->text('answer')->nullable();
            $table->enum('status', ['pending', 'answered', 'closed'])->default('pending');
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->boolean('is_video_call')->default(false);
            $table->string('video_call_room')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};
