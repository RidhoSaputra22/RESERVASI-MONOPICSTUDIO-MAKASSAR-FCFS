<?php

namespace App\Filament\Widgets;

use App\Models\Package;
use Filament\Widgets\ChartWidget;

class PopularPackagesWidget extends ChartWidget
{
    protected static ?int $sort = 8;

    protected int | string | array $columnSpan = 1;

    public function getHeading(): ?string
    {
        return 'Paket Terpopuler';
    }

    protected function getData(): array
    {
        $packages = Package::withCount('bookings')
            ->orderByDesc('bookings_count')
            ->limit(5)
            ->get();

        return [
            'datasets' => [
                [
                    'data' => $packages->pluck('bookings_count')->toArray(),
                    'backgroundColor' => [
                        'rgb(34, 197, 94)',
                        'rgb(59, 130, 246)',
                        'rgb(168, 85, 247)',
                        'rgb(251, 191, 36)',
                        'rgb(236, 72, 153)',
                    ],
                ],
            ],
            'labels' => $packages->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
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
