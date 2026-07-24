<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ObraSocial extends Model
{
    use HasFactory;

    protected $table = 'obras_sociales';

    protected $fillable = [
        'denominacion',
        'abreviada',
    ];

    public function pacientes(): HasMany
    {
        return $this->hasMany(Paciente::class);
    }
}
