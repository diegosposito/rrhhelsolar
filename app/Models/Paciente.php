<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Paciente extends Model
{
    use HasFactory;

    protected $table = 'pacientes';

    protected $fillable = [
        'nombre',
        'apellido',
        'direccion',
        'ciudad',
        'celular',
        'email',
        'observaciones',
        'obra_social_id',
    ];

    public function obraSocial(): BelongsTo
    {
        return $this->belongsTo(ObraSocial::class);
    }
}
