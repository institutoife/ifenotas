<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('simulator_records', function (Blueprint $table) {
            $table->string('subject', 100)->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('simulator_records', function (Blueprint $table) {
            $table->dropIndex(['subject']);
            $table->dropColumn('subject');
        });
    }
};
