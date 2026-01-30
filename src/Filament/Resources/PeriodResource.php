<?php

namespace NexusPlugin\DoubleColorBall\Filament\Resources;

use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use NexusPlugin\DoubleColorBall\Models\Period;
use NexusPlugin\DoubleColorBall\Filament\Resources\PeriodResource\Pages;
use Filament\Actions\Action;
use NexusPlugin\DoubleColorBall\Repositories\DrawRepository;
use Illuminate\Support\Facades\Log;

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
        return nexus_trans('dcb::dcb.admin.periods');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('period_code')
                    ->label(nexus_trans('dcb::dcb.labels.period_code'))
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(20),
                
                Forms\Components\Select::make('status')
                    ->label(nexus_trans('dcb::dcb.labels.status'))
                    ->options([
                        Period::STATUS_OPEN => nexus_trans('dcb::dcb.status.open'),
                        Period::STATUS_CLOSED => nexus_trans('dcb::dcb.status.closed'),
                        Period::STATUS_DRAWN => nexus_trans('dcb::dcb.status.drawn'),
                    ])
                    ->required(),

                Forms\Components\TextInput::make('prize_pool')
                    ->label(nexus_trans('dcb::dcb.labels.prize_pool'))
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
                    ->label(nexus_trans('dcb::dcb.labels.period_code'))
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->label(nexus_trans('dcb::dcb.labels.status'))
                    ->formatStateUsing(fn ($state) => match($state) {
                        Period::STATUS_OPEN => nexus_trans('dcb::dcb.status.open'),
                        Period::STATUS_CLOSED => nexus_trans('dcb::dcb.status.closed'),
                        Period::STATUS_DRAWN => nexus_trans('dcb::dcb.status.drawn'),
                        default => nexus_trans('dcb::dcb.status.unknown'),
                    })
                    ->colors([
                        'success' => Period::STATUS_OPEN,
                        'warning' => Period::STATUS_CLOSED,
                        'primary' => Period::STATUS_DRAWN,
                    ]),
                
                Tables\Columns\TextColumn::make('prize_pool')
                    ->label(nexus_trans('dcb::dcb.labels.prize_pool'))
                    ->money('CNY')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('formatted_winning_numbers')
                    ->label(nexus_trans('dcb::dcb.labels.winning_numbers'))
                    ->limit(50),
                
                Tables\Columns\TextColumn::make('opened_at')
                    ->label(nexus_trans('dcb::dcb.labels.draw_time'))
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
                        Period::STATUS_OPEN => nexus_trans('dcb::dcb.status.open'),
                        Period::STATUS_CLOSED => nexus_trans('dcb::dcb.status.closed'),
                        Period::STATUS_DRAWN => nexus_trans('dcb::dcb.status.drawn'),
                    ]),
            ])
            ->recordActions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Action::make('draw')
                    ->label(nexus_trans('dcb::dcb.admin.manual_draw'))
                    ->icon('heroicon-o-play')
                    ->requiresConfirmation()
                    ->visible(fn (Period $record) => $record->status !== Period::STATUS_DRAWN)
                    ->action(function (Period $record) {
                        try {
                            $drawRepo = new DrawRepository();
                            $result = $drawRepo->executeDraw($record->id);
                            
                            \Filament\Notifications\Notification::make()
                                ->title(nexus_trans('dcb::dcb.messages.draw_success'))
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Log::error('Manual draw failed', ['error' => $e->getMessage()]);
                            
                            \Filament\Notifications\Notification::make()
                                ->title(nexus_trans('dcb::dcb.messages.draw_failed', ['reason' => $e->getMessage()]))
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->toolbarActions([
                Tables\Actions\CreateAction::make(),
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
