<?php

namespace App\Filament\Resources\PeriodoQuincenaResource\Pages;

use App\Filament\Resources\PeriodoQuincenaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPeriodoQuincenas extends ListRecords
{
    protected static string $resource = PeriodoQuincenaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
