<?php

namespace App\Filament\Widgets;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestBookingsWidget extends BaseWidget
{
    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 1;

    public function getHeading(): ?string
    {
        return 'Booking Terbaru';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Booking::query()
                    ->with(['user', 'package'])
                    ->latest()
                    ->limit(5)
            )
            ->heading($this->getHeading())
            ->columns([
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M H:i')
                    ->description(fn ($record) => $record->created_at->diffForHumans()),
                TextColumn::make('user.name')
                    ->label('Customer')
                    ->limit(15),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (BookingStatus $state): string => match ($state) {
                        BookingStatus::Pending => 'warning',
                        BookingStatus::Confirmed => 'info',
                        BookingStatus::Completed => 'success',
                        BookingStatus::Cancelled => 'danger',
                    }),
            ])
            ->paginated(false)
            ->emptyStateHeading('Belum ada booking')
            ->emptyStateIcon('heroicon-o-inbox');
    }
}
