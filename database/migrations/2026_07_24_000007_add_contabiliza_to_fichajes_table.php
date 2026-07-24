<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fichajes', function (Blueprint $table) {
            // Whether this punch counts towards worked-hours totals. Punches
            // flagged false are still shown (as invalid/red in the detail) but
            // never contribute time. Mirrors the legacy `horarios.controlar`.
            $table->boolean('contabiliza')->default(true)->after('tipo');
        });
    }

    public function down(): void
    {
        Schema::table('fichajes', function (Blueprint $table) {
            $table->dropColumn('contabiliza');
        });
    }
};
