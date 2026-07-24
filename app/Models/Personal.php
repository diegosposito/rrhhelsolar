<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Personal extends Model
{
    use HasFactory;

    protected $table = 'personal';

    protected $fillable = [
        'nombre',
        'apellido',
        'dni',
        'sexo',
        'fecha_nacimiento',
        'fecha_ingreso',
        'direccion',
        'ciudad',
        'telefono',
        'celular',
        'email',
        'area',
        'horario_semanal',
        'observaciones',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'fecha_ingreso' => 'date',
            'activo' => 'boolean',
        ];
    }

    public function fichajes(): HasMany
    {
        return $this->hasMany(Fichaje::class);
    }

    /**
     * Scope to an active personal matching the given DNI.
     */
    public function scopeActivoPorDni(Builder $query, string $dni): Builder
    {
        return $query->where('dni', $dni)->where('activo', true);
    }
}
