<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PacienteResource\Pages;
use App\Models\Paciente;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PacienteResource extends Resource
{
    protected static ?string $model = Paciente::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Gestión';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Pacientes';

    protected static ?string $modelLabel = 'Paciente';

    protected static ?string $pluralModelLabel = 'Pacientes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('apellido')
                    ->label('Apellido')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('obra_social_id')
                    ->label('Obra Social')
                    ->relationship('obraSocial', 'denominacion')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Forms\Components\TextInput::make('direccion')
                    ->label('Dirección')
                    ->maxLength(255),
                Forms\Components\TextInput::make('ciudad')
                    ->label('Ciudad')
                    ->maxLength(255),
                Forms\Components\TextInput::make('celular')
                    ->label('Celular')
                    ->tel()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->label('Correo electrónico')
                    ->email()
                    ->maxLength(255),
                Forms\Components\Textarea::make('observaciones')
                    ->label('Observaciones')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('apellido')
                    ->label('Paciente')
                    ->formatStateUsing(fn (Paciente $record): string => "{$record->apellido}, {$record->nombre}")
                    ->searchable(['apellido', 'nombre'])
                    ->sortable(['apellido', 'nombre']),
                Tables\Columns\TextColumn::make('celular')
                    ->label('Celular')
                    ->searchable(),
                Tables\Columns\TextColumn::make('obraSocial.denominacion')
                    ->label('Obra Social')
                    ->badge()
                    ->sortable(),
            ])
            ->defaultSort('apellido')
            ->filters([
                Tables\Filters\SelectFilter::make('obra_social_id')
                    ->label('Obra Social')
                    ->relationship('obraSocial', 'denominacion')
                    ->searchable()
                    ->preload(),
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
            'index' => Pages\ListPacientes::route('/'),
            'create' => Pages\CreatePaciente::route('/create'),
            'edit' => Pages\EditPaciente::route('/{record}/edit'),
        ];
    }
}
