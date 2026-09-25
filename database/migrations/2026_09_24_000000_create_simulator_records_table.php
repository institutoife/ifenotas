<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('simulator_records', function (Blueprint $table) {
            $table->id();
            $table->char('visitor_key', 64);
            $table->uuid('submission_id');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('first');
            $table->unsignedTinyInteger('second');
            $table->unsignedSmallInteger('required_third');
            $table->unsignedSmallInteger('pass_score');
            $table->string('status', 20)->index();
            $table->timestamps();
            $table->unique(['visitor_key', 'submission_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simulator_records');
    }
};
