<?php

namespace App\Support;

/**
 * Duration formatting helpers.
 */
class Duracion
{
    /**
     * Format a number of seconds as zero-padded HH:MM:SS.
     *
     * Hours are not capped at 24 (e.g. 222856 -> "61:54:16"). Negative
     * inputs are clamped to zero. No microseconds.
     */
    public static function hms(int $segundos): string
    {
        $segundos = max(0, $segundos);

        $horas = intdiv($segundos, 3600);
        $minutos = intdiv($segundos % 3600, 60);
        $restoSegundos = $segundos % 60;

        return sprintf('%02d:%02d:%02d', $horas, $minutos, $restoSegundos);
    }
}
