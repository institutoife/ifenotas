<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_prize_winners', function (Blueprint $table): void {
            $table->id();
            $table->string('winner_name', 120);
            $table->string('prize', 160);
            $table->unsignedTinyInteger('drawn_number')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_prize_winners');
    }
};
