<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fichajes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personal_id')
                ->constrained('personal')
                ->cascadeOnDelete();
            $table->string('tipo');
            $table->dateTime('fecha_hora');
            $table->string('observacion', 255)->nullable();
            $table->timestamps();

            $table->index(['personal_id', 'fecha_hora']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fichajes');
    }
};
