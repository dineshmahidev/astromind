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
        Schema::create('expert_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('astrologer_id');
            $table->decimal('amount', 10, 2);
            $table->string('type'); // 'credit' (earning), 'debit' (withdrawal)
            $table->string('description')->nullable();
            $table->string('status')->default('completed'); // 'completed', 'pending', 'rejected'
            $table->timestamps();

            $table->foreign('astrologer_id')->references('id')->on('astrologers')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expert_transactions');
    }
};
