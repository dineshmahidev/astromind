<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('user'); // admin, user, astrologer
            }
            if (!Schema::hasColumn('users', 'is_premium')) {
                $table->boolean('is_premium')->default(false);
            }
            if (!Schema::hasColumn('users', 'wallet_balance')) {
                $table->decimal('wallet_balance', 10, 2)->default(0);
            }
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable();
            }
            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'is_premium', 'wallet_balance', 'phone', 'avatar']);
        });
    }
};
