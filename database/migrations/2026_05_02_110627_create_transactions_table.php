<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('payment_id')->nullable(); // Gateway ID (e.g. Razorpay ID)
            $table->decimal('amount', 10, 2);
            $table->string('currency')->default('INR');
            $table->string('status')->default('pending'); // pending, success, failed
            $table->string('type')->default('wallet_recharge'); // recharge, consultation, premium
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
