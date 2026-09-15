<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_counters', function (Blueprint $table): void {
            $table->string('page')->primary();
            $table->unsignedBigInteger('visits');
        });

        DB::table('page_counters')->insert([
            'page' => 'homepage',
            'visits' => 799999,
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('page_counters');
    }
};
