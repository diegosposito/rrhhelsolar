<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PersonalResource\Pages;
use App\Models\Personal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PersonalResource extends Resource
{
    protected static ?string $model = Personal::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationGroup = 'Gestión';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Personal';

    protected static ?string $modelLabel = 'Personal';

    protected static ?string $pluralModelLabel = 'Personal';

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
                Forms\Components\TextInput::make('dni')
                    ->label('DNI')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\Select::make('sexo')
                    ->label('Sexo')
                    ->options([
                        'M' => 'Masculino',
                        'F' => 'Femenino',
                    ]),
                Forms\Components\DatePicker::make('fecha_nacimiento')
                    ->label('Fecha de nacimiento')
                    ->displayFormat('d/m/Y'),
                Forms\Components\DatePicker::make('fecha_ingreso')
                    ->label('Fecha de ingreso')
                    ->displayFormat('d/m/Y'),
                Forms\Components\TextInput::make('direccion')
                    ->label('Dirección')
                    ->maxLength(255),
                Forms\Components\TextInput::make('ciudad')
                    ->label('Ciudad')
                    ->maxLength(255),
                Forms\Components\TextInput::make('telefono')
                    ->label('Teléfono')
                    ->tel()
                    ->maxLength(255),
                Forms\Components\TextInput::make('celular')
                    ->label('Celular')
                    ->tel()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->label('Correo electrónico')
                    ->email()
                    ->maxLength(255),
                Forms\Components\TextInput::make('area')
                    ->label('Área')
                    ->maxLength(255),
                Forms\Components\Toggle::make('activo')
                    ->label('Activo')
                    ->default(true),
                Forms\Components\Textarea::make('horario_semanal')
                    ->label('Horario semanal')
                    ->rows(3)
                    ->columnSpanFull(),
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
                    ->label('Personal')
                    ->formatStateUsing(fn (Personal $record): string => "{$record->apellido}, {$record->nombre}")
                    ->searchable(['apellido', 'nombre'])
                    ->sortable(['apellido', 'nombre']),
                Tables\Columns\TextColumn::make('dni')
                    ->label('DNI')
                    ->searchable(),
                Tables\Columns\TextColumn::make('area')
                    ->label('Área')
                    ->badge()
                    ->searchable(),
                Tables\Columns\IconColumn::make('activo')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),
            ])
            ->defaultSort('apellido')
            ->filters([
                Tables\Filters\TernaryFilter::make('activo')
                    ->label('Activo')
                    ->boolean()
                    ->trueLabel('Solo activos')
                    ->falseLabel('Solo inactivos')
                    ->native(false),
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
            'index' => Pages\ListPersonals::route('/'),
            'create' => Pages\CreatePersonal::route('/create'),
            'edit' => Pages\EditPersonal::route('/{record}/edit'),
        ];
    }
}
