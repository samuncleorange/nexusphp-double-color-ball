<?php

namespace NexusPlugin\DoubleColorBall\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use NexusPlugin\DoubleColorBall\Models\Period;
use NexusPlugin\DoubleColorBall\Filament\Resources\PeriodResource\Pages;
use Filament\Tables\Actions\Action;
use NexusPlugin\DoubleColorBall\Repositories\DrawRepository;

/**
 * Period Resource
 * 
 * Filament admin resource for managing lottery periods.
 */
class PeriodResource extends Resource
{
    protected static ?string $model = Period::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationGroup = 'Double Color Ball';

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return 'Periods';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('period_code')
                    ->label('Period Code')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(20),
                
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        Period::STATUS_OPEN => 'Open',
                        Period::STATUS_CLOSED => 'Closed',
                        Period::STATUS_DRAWN => 'Drawn',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('prize_pool')
                    ->label('Prize Pool')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('period_code')
                    ->label('Period Code')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn ($state) => match($state) {
                        Period::STATUS_OPEN => 'Open',
                        Period::STATUS_CLOSED => 'Closed',
                        Period::STATUS_DRAWN => 'Drawn',
                        default => 'Unknown',
                    })
                    ->colors([
                        'success' => Period::STATUS_OPEN,
                        'warning' => Period::STATUS_CLOSED,
                        'primary' => Period::STATUS_DRAWN,
                    ]),
                
                Tables\Columns\TextColumn::make('prize_pool')
                    ->label('Prize Pool')
                    ->money('CNY')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('formatted_winning_numbers')
                    ->label('Winning Numbers')
                    ->limit(50),
                
                Tables\Columns\TextColumn::make('opened_at')
                    ->label('Draw Time')
                    ->dateTime()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        Period::STATUS_OPEN => 'Open',
                        Period::STATUS_CLOSED => 'Closed',
                        Period::STATUS_DRAWN => 'Drawn',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Action::make('draw')
                    ->label('Manual Draw')
                    ->icon('heroicon-o-play')
                    ->requiresConfirmation()
                    ->visible(fn (Period $record) => $record->status !== Period::STATUS_DRAWN)
                    ->action(function (Period $record) {
                        try {
                            $drawRepo = new DrawRepository();
                            $result = $drawRepo->executeDraw($record->id);
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Draw completed successfully')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            do_log('Manual draw failed: ' . $e->getMessage(), 'error');
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Draw failed: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPeriods::route('/'),
            'create' => Pages\CreatePeriod::route('/create'),
            'view' => Pages\ViewPeriod::route('/{record}'),
            'edit' => Pages\EditPeriod::route('/{record}/edit'),
        ];
    }
}
