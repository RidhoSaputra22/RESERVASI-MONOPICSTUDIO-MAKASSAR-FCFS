<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class MonthlyRevenueChartWidget extends ChartWidget
{
    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 1;

    public function getHeading(): ?string
    {
        return 'Pendapatan 6 Bulan Terakhir';
    }

    protected function getData(): array
    {
        $data = [];
        $labels = [];

        // Get data for last 6 months
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $labels[] = $date->format('M Y');

            $revenue = Booking::where('status', 'completed')
                ->whereMonth('scheduled_at', $date->month)
                ->whereYear('scheduled_at', $date->year)
                ->join('packages', 'bookings.package_id', '=', 'packages.id')
                ->sum('packages.price');

            $data[] = $revenue / 1000000; // Convert to millions for better display
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pendapatan (Juta Rp)',
                    'data' => $data,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.2)',
                    'borderColor' => 'rgb(34, 197, 94)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
