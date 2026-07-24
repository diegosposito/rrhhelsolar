<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periodos_quincena', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('anio');
            $table->unsignedTinyInteger('mes'); // 1-12
            // Full-coverage, contiguous fortnight ranges for the period. The two
            // ranges always tile the whole month: primera_inicio is day 1,
            // segunda_fin is the last day, segunda_inicio = primera_fin + 1 day.
            $table->date('primera_inicio');
            $table->date('primera_fin');
            $table->date('segunda_inicio');
            $table->date('segunda_fin');
            $table->timestamps();

            $table->unique(['anio', 'mes']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periodos_quincena');
    }
};
