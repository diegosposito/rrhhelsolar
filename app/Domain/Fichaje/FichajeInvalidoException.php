<?php

namespace App\Domain\Fichaje;

use DomainException;

/**
 * Raised when a time-clock punch violates a business rule
 * (alternation, day boundary, or DNI resolution).
 */
class FichajeInvalidoException extends DomainException
{
    public static function entradaAbierta(): self
    {
        return new self('Ya existe una entrada sin cerrar');
    }

    public static function sinEntradaAbierta(): self
    {
        return new self('No hay una entrada abierta para cerrar');
    }

    public static function personalNoEncontrado(string $dni): self
    {
        return new self("No existe personal activo con el DNI {$dni}");
    }
}
