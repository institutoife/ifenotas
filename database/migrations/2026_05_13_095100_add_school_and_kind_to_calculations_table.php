<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calculations', function (Blueprint $table): void {
            $table->foreignId('school_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->string('kind', 20)->default('calculation')->after('school_id');
        });
    }

    public function down(): void
    {
        Schema::table('calculations', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('school_id');
            $table->dropColumn('kind');
        });
    }
};
