<?php

namespace App\Filament\Resources;

use App\Domain\Fichaje\Quincenas;
use App\Filament\Resources\PeriodoQuincenaResource\Pages;
use App\Models\PeriodoQuincena;
use App\Support\Meses;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PeriodoQuincenaResource extends Resource
{
    protected static ?string $model = PeriodoQuincena::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Horarios';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Quincenas';

    protected static ?string $modelLabel = 'Período de Quincena';

    protected static ?string $pluralModelLabel = 'Quincenas';

    /** A period stops being editable once its month has fully elapsed. */
    public static function canEdit(Model $record): bool
    {
        return $record->esEditable();
    }

    public static function canDelete(Model $record): bool
    {
        return $record->esEditable();
    }

    public static function form(Form $form): Form
    {
        // Keeps the derived fields (day 1, second-fortnight start, month end) in
        // sync while the admin only chooses the cut day. Saving re-derives all
        // four dates server-side (see the Create/Edit pages), so coverage is
        // guaranteed regardless of the live state.
        $recalcular = function (Get $get, Set $set): void {
            $anio = (int) $get('anio');
            $mes = (int) $get('mes');
            if ($anio < 1 || $mes < 1) {
                return;
            }

            $quincenas = app(Quincenas::class);
            $corteDia = $get('primera_fin')
                ? Carbon::parse($get('primera_fin'))->day
                : $quincenas->corteSugerido($mes, $anio);

            foreach ($quincenas->derivarFechas($anio, $mes, $corteDia) as $campo => $valor) {
                $set($campo, $valor);
            }
        };

        return $form
            ->schema([
                Forms\Components\Select::make('anio')
                    ->label('Año')
                    ->options(self::opcionesAnio())
                    ->required()
                    ->live()
                    ->afterStateUpdated($recalcular)
                    ->default(fn (): int => (int) now()->year),
                Forms\Components\Select::make('mes')
                    ->label('Mes')
                    // Only the current period can be configured, so this is the
                    // sole option offered.
                    ->options(self::opcionesMes())
                    ->required()
                    ->live()
                    ->afterStateUpdated($recalcular)
                    ->default(fn (): int => (int) now()->month)
                    // Combination (año, mes) must be unique.
                    ->rule(fn (Get $get, ?Model $record) => function (string $attr, $value, \Closure $fail) use ($get, $record) {
                        $existe = PeriodoQuincena::query()
                            ->where('anio', (int) $get('anio'))
                            ->where('mes', (int) $value)
                            ->when($record, fn ($q) => $q->whereKeyNot($record->getKey()))
                            ->exists();

                        if ($existe) {
                            $fail('Ya existe una configuración para ese período.');
                        }
                    }),
                Forms\Components\Section::make('Primera quincena')
                    ->columns(2)
                    ->schema([
                        Forms\Components\DatePicker::make('primera_inicio')
                            ->label('Desde')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->disabled()
                            ->dehydrated(false)
                            ->default(fn (): string => self::fechasPorDefecto()['primera_inicio'])
                            ->helperText('Siempre el día 1 del mes.'),
                        Forms\Components\DatePicker::make('primera_fin')
                            ->label('Hasta (corte)')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated($recalcular)
                            ->default(fn (): string => self::fechasPorDefecto()['primera_fin'])
                            ->minDate(fn (Get $get): ?Carbon => self::primerDia($get))
                            ->maxDate(fn (Get $get): ?Carbon => self::ultimoDia($get)?->copy()->subDay())
                            ->helperText('Único campo editable: define dónde corta la primera quincena.'),
                    ]),
                Forms\Components\Section::make('Segunda quincena')
                    ->columns(2)
                    ->schema([
                        Forms\Components\DatePicker::make('segunda_inicio')
                            ->label('Desde')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->disabled()
                            ->dehydrated(false)
                            ->default(fn (): string => self::fechasPorDefecto()['segunda_inicio'])
                            ->helperText('Día siguiente al corte.'),
                        Forms\Components\DatePicker::make('segunda_fin')
                            ->label('Hasta')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->disabled()
                            ->dehydrated(false)
                            ->default(fn (): string => self::fechasPorDefecto()['segunda_fin'])
                            ->helperText('Siempre el último día del mes.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('periodo')
                    ->label('Período')
                    ->state(fn (PeriodoQuincena $record): string => Meses::periodo($record->mes, $record->anio))
                    ->sortable(['anio', 'mes']),
                Tables\Columns\TextColumn::make('primera')
                    ->label('1ra quincena')
                    ->state(fn (PeriodoQuincena $record): string => $record->primera_inicio->format('d/m').' → '.$record->primera_fin->format('d/m')),
                Tables\Columns\TextColumn::make('segunda')
                    ->label('2da quincena')
                    ->state(fn (PeriodoQuincena $record): string => $record->segunda_inicio->format('d/m').' → '.$record->segunda_fin->format('d/m')),
                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->state(fn (PeriodoQuincena $record): string => $record->esEditable() ? 'Abierto' : 'Cerrado')
                    ->color(fn (string $state): string => $state === 'Abierto' ? 'success' : 'gray'),
            ])
            ->defaultSort('anio', 'desc')
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn (PeriodoQuincena $record): bool => $record->esEditable()),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (PeriodoQuincena $record): bool => $record->esEditable()),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPeriodoQuincenas::route('/'),
            'create' => Pages\CreatePeriodoQuincena::route('/create'),
            'edit' => Pages\EditPeriodoQuincena::route('/{record}/edit'),
        ];
    }

    /**
     * Full-coverage dates to pre-fill a new period with: current month, cut day
     * inherited from the most recent earlier period (or 15 by default).
     *
     * @return array{primera_inicio: string, primera_fin: string, segunda_inicio: string, segunda_fin: string}
     */
    private static function fechasPorDefecto(): array
    {
        $anio = (int) now()->year;
        $mes = (int) now()->month;
        $quincenas = app(Quincenas::class);

        return $quincenas->derivarFechas($anio, $mes, $quincenas->corteSugerido($mes, $anio));
    }

    /**
     * Only the current year is configurable.
     *
     * @return array<int, int>
     */
    private static function opcionesAnio(): array
    {
        $actual = (int) now()->year;

        return [$actual => $actual];
    }

    /**
     * Only the current month is configurable.
     *
     * @return array<int, string>
     */
    private static function opcionesMes(): array
    {
        $mes = (int) now()->month;

        return [$mes => Meses::nombre($mes)];
    }

    private static function primerDia(Get $get): ?Carbon
    {
        $anio = (int) $get('anio');
        $mes = (int) $get('mes');

        return $anio > 0 && $mes > 0 ? Carbon::create($anio, $mes, 1) : null;
    }

    private static function ultimoDia(Get $get): ?Carbon
    {
        return self::primerDia($get)?->copy()->endOfMonth();
    }
}
