<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Per-period (month/year) fortnight configuration that drives the primera/
 * segunda quincena split in the worked-hours reports.
 *
 * Only the current period (this month/year) can be configured or modified. Past
 * periods are read-only so already-liquidated hours can never shift, and future
 * periods cannot be configured ahead of time — each one is set up when it begins.
 */
class PeriodoQuincena extends Model
{
    use HasFactory;

    protected $table = 'periodos_quincena';

    protected $fillable = [
        'anio',
        'mes',
        'primera_inicio',
        'primera_fin',
        'segunda_inicio',
        'segunda_fin',
    ];

    protected function casts(): array
    {
        return [
            'anio' => 'integer',
            'mes' => 'integer',
            'primera_inicio' => 'date',
            'primera_fin' => 'date',
            'segunda_inicio' => 'date',
            'segunda_fin' => 'date',
        ];
    }

    /**
     * A period is editable only while it is the current month/year.
     */
    public function esEditable(): bool
    {
        $ahora = Carbon::now();

        return $ahora->year === $this->anio && $ahora->month === $this->mes;
    }
}
