<?php

namespace App\Filament\Widgets;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Filament\Widgets\ChartWidget;

class BookingStatusChartWidget extends ChartWidget
{
    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 1;

    public function getHeading(): ?string
    {
        return 'Distribusi Status Booking';
    }

    protected function getData(): array
    {
        $pending = Booking::where('status', BookingStatus::Pending)->count();
        $confirmed = Booking::where('status', BookingStatus::Confirmed)->count();
        $completed = Booking::where('status', BookingStatus::Completed)->count();
        $cancelled = Booking::where('status', BookingStatus::Cancelled)->count();

        return [
            'datasets' => [
                [
                    'data' => [$pending, $confirmed, $completed, $cancelled],
                    'backgroundColor' => [
                        'rgb(251, 191, 36)',  // Amber - Pending
                        'rgb(59, 130, 246)',  // Blue - Confirmed
                        'rgb(34, 197, 94)',   // Green - Completed
                        'rgb(239, 68, 68)',   // Red - Cancelled
                    ],
                ],
            ],
            'labels' => ['Pending', 'Terkonfirmasi', 'Selesai', 'Dibatalkan'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
