<?php

namespace App\Models;

use App\Enums\TipoFichaje;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fichaje extends Model
{
    use HasFactory;

    protected $table = 'fichajes';

    protected $fillable = [
        'personal_id',
        'tipo',
        'contabiliza',
        'fecha_hora',
        'observacion',
    ];

    protected $attributes = [
        'contabiliza' => true,
    ];

    protected function casts(): array
    {
        return [
            'tipo' => TipoFichaje::class,
            'contabiliza' => 'boolean',
            'fecha_hora' => 'datetime',
        ];
    }

    public function personal(): BelongsTo
    {
        return $this->belongsTo(Personal::class);
    }
}
