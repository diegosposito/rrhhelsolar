<?php

namespace App\Filament\Resources\ObraSocialResource\Pages;

use App\Filament\Resources\ObraSocialResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListObraSocials extends ListRecords
{
    protected static string $resource = ObraSocialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
