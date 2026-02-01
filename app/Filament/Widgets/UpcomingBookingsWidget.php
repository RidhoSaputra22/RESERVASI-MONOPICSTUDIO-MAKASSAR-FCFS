<?php

namespace App\Filament\Widgets;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Carbon\Carbon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class UpcomingBookingsWidget extends BaseWidget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 1;

    public function getHeading(): ?string
    {
        return 'Booking Mendatang (7 Hari)';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Booking::query()
                    ->with(['user', 'package'])
                    ->where('status', BookingStatus::Confirmed)
                    ->whereDate('scheduled_at', '>', Carbon::today())
                    ->whereDate('scheduled_at', '<=', Carbon::today()->addDays(7))
                    ->orderBy('scheduled_at', 'asc')
                    ->limit(5)
            )
            ->heading($this->getHeading())
            ->columns([
                TextColumn::make('scheduled_at')
                    ->label('Jadwal')
                    ->dateTime('d M H:i')
                    ->description(fn ($record) => $record->scheduled_at->diffForHumans()),
                TextColumn::make('user.name')
                    ->label('Customer')
                    ->limit(15),
                TextColumn::make('package.name')
                    ->label('Paket')
                    ->limit(15),
            ])
            ->paginated(false)
            ->emptyStateHeading('Tidak ada booking')
            ->emptyStateDescription('Belum ada booking mendatang.')
            ->emptyStateIcon('heroicon-o-calendar');
    }
}
