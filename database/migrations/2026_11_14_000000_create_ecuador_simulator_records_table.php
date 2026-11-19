<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ecuador_simulator_records', function (Blueprint $table) {
            $table->id();
            $table->char('visitor_key', 64);
            $table->uuid('submission_id');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('province', 64);
            $table->string('subject', 80);
            $table->boolean('is_custom_subject')->default(false);
            $table->unsignedSmallInteger('first_cents');
            $table->unsignedSmallInteger('second_cents');
            $table->unsignedSmallInteger('required_third_cents');
            $table->unsignedSmallInteger('maximum_average_cents');
            $table->unsignedSmallInteger('target_total_cents');
            $table->string('status', 20);
            $table->timestamps();
            $table->unique(['visitor_key', 'submission_id'], 'ec_records_visitor_submission_unique');
            $table->index(['province', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ecuador_simulator_records');
    }
};
