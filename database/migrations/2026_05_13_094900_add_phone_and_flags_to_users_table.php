<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('phone')->nullable()->unique()->after('email');
            $table->boolean('is_admin')->default(false)->after('remember_token');
            $table->boolean('is_follower_unlocked')->default(false)->after('is_admin');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['phone', 'is_admin', 'is_follower_unlocked']);
        });
    }
};
