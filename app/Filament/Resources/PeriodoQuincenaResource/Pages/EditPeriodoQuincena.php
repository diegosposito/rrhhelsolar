<?php

namespace App\Filament\Resources\PeriodoQuincenaResource\Pages;

use App\Filament\Resources\PeriodoQuincenaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPeriodoQuincena extends EditRecord
{
    use DerivaFechasQuincena;

    protected static string $resource = PeriodoQuincenaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->derivarFechas($data);
    }
}
