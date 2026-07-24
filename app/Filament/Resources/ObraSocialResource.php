<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ObraSocialResource\Pages;
use App\Models\ObraSocial;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ObraSocialResource extends Resource
{
    protected static ?string $model = ObraSocial::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?string $navigationGroup = 'Gestión';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Obras Sociales';

    protected static ?string $modelLabel = 'Obra Social';

    protected static ?string $pluralModelLabel = 'Obras Sociales';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('denominacion')
                    ->label('Denominación')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('abreviada')
                    ->label('Sigla')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('denominacion')
                    ->label('Denominación')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('abreviada')
                    ->label('Sigla')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pacientes_count')
                    ->label('Pacientes')
                    ->counts('pacientes')
                    ->badge()
                    ->sortable(),
            ])
            ->defaultSort('denominacion')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListObraSocials::route('/'),
            'create' => Pages\CreateObraSocial::route('/create'),
            'edit' => Pages\EditObraSocial::route('/{record}/edit'),
        ];
    }
}
