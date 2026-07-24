<?php

namespace App\Support;

/**
 * Spanish month-name helpers for report labels and select options.
 */
class Meses
{
    /** @var array<int, string> */
    private const NOMBRES = [
        1 => 'Enero',
        2 => 'Febrero',
        3 => 'Marzo',
        4 => 'Abril',
        5 => 'Mayo',
        6 => 'Junio',
        7 => 'Julio',
        8 => 'Agosto',
        9 => 'Septiembre',
        10 => 'Octubre',
        11 => 'Noviembre',
        12 => 'Diciembre',
    ];

    public static function nombre(int $mes): string
    {
        return self::NOMBRES[$mes] ?? '';
    }

    /**
     * @return array<int, string>
     */
    public static function opciones(): array
    {
        return self::NOMBRES;
    }

    /**
     * Human label for a reporting period, e.g. "Julio de 2026".
     */
    public static function periodo(int $mes, int $anio): string
    {
        return self::nombre($mes).' de '.$anio;
    }
}
