<?php

namespace App\Filament\Resources\PeriodoQuincenaResource\Pages;

use App\Filament\Resources\PeriodoQuincenaResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePeriodoQuincena extends CreateRecord
{
    use DerivaFechasQuincena;

    protected static string $resource = PeriodoQuincenaResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Only the current period can be created — enforce it server-side
        // regardless of what the form submitted.
        $data['anio'] = (int) now()->year;
        $data['mes'] = (int) now()->month;

        return $this->derivarFechas($data);
    }
}
