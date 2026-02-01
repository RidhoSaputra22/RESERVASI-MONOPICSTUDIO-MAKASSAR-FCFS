<?php

namespace App\Filament\Widgets;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class BookingChartWidget extends ChartWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    // ini yang ngatur “tinggi” chart via rasio (lebar : tinggi)
    protected ?array $options = [
        'aspectRatio' => 3, // makin besar => chart makin pendek
    ];

    public function getHeading(): ?string
    {
        return 'Statistik Booking 7 Hari Terakhir';
    }

    protected function getData(): array
    {
        $data = [];
        $labels = [];

        // Get data for last 7 days
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $labels[] = $date->format('d M');

            $data['completed'][] = Booking::where('status', BookingStatus::Completed)
                ->whereDate('scheduled_at', $date)
                ->count();

            $data['confirmed'][] = Booking::where('status', BookingStatus::Confirmed)
                ->whereDate('scheduled_at', $date)
                ->count();

            $data['cancelled'][] = Booking::where('status', BookingStatus::Cancelled)
                ->whereDate('scheduled_at', $date)
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Selesai',
                    'data' => $data['completed'],
                    'backgroundColor' => 'rgba(34, 197, 94, 0.5)',
                    'borderColor' => 'rgb(34, 197, 94)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Terkonfirmasi',
                    'data' => $data['confirmed'],
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Dibatalkan',
                    'data' => $data['cancelled'],
                    'backgroundColor' => 'rgba(239, 68, 68, 0.5)',
                    'borderColor' => 'rgb(239, 68, 68)',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
