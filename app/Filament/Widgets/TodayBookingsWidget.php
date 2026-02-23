<?php

namespace App\Filament\Widgets;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Carbon\Carbon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TodayBookingsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function getHeading(): ?string
    {
        return 'Jadwal Booking Hari Ini';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Booking::query()
                    ->with(['user', 'package', 'photographer', 'studio'])
                    ->whereDate('scheduled_at', Carbon::today())
                    ->orderBy('scheduled_at', 'asc')
            )
            ->heading($this->getHeading())
            ->columns([
                TextColumn::make('scheduled_at')
                    ->label('Jam')
                    ->dateTime('H:i')
                    ->sortable(),
                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable(),
                TextColumn::make('package.name')
                    ->label('Paket'),
                TextColumn::make('studio.name')
                    ->label('Studio'),
                TextColumn::make('photographer.name')
                    ->label('Fotografer'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (BookingStatus $state): string => match ($state) {
                        BookingStatus::Pending => 'warning',
                        BookingStatus::Confirmed => 'info',
                        BookingStatus::Completed => 'success',
                        BookingStatus::Cancelled => 'danger',
                    }),
                TextColumn::make('readiness_confirmed_at')
                    ->label('Siap')
                    ->dateTime('H:i')
                    ->placeholder('-')
                    ->badge()
                    ->color(fn ($record) => $record->readiness_confirmed_at ? 'success' : 'gray'),
            ])
            ->defaultSort('scheduled_at', 'asc')
            ->emptyStateHeading('Tidak ada booking hari ini')
            ->emptyStateDescription('Belum ada jadwal booking untuk hari ini.')
            ->emptyStateIcon('heroicon-o-calendar');
    }
}
