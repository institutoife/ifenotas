<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
            $table->string('codigo_rue', 40)->unique();
            $table->string('departamento', 80)->nullable();
            $table->string('provincia', 100)->nullable();
            $table->string('municipio', 100)->nullable();
            $table->string('distrito', 100)->nullable();
            $table->string('area', 60)->nullable();
            $table->string('dependencia', 80)->nullable();
            $table->string('niveles')->nullable();
            $table->string('turnos')->nullable();
            $table->string('director')->nullable();
            $table->string('direccion')->nullable();
            $table->string('telefonos')->nullable();
            $table->string('url_ficha')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('nombre');
            $table->index('codigo_rue');
            $table->index(['departamento', 'provincia']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
