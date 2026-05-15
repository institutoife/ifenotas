<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('grade_level', 80)->nullable()->after('phone');
            $table->string('guardian_name')->nullable()->after('grade_level');
            $table->string('guardian_phone', 30)->nullable()->after('guardian_name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['grade_level', 'guardian_name', 'guardian_phone']);
        });
    }
};
