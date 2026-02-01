<?php

namespace App\Filament\Widgets;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        // Total Booking Hari Ini
        $todayBookings = Booking::whereDate('scheduled_at', $today)->count();

        // Total Booking Bulan Ini
        $monthlyBookings = Booking::whereMonth('scheduled_at', Carbon::now()->month)
            ->whereYear('scheduled_at', Carbon::now()->year)
            ->count();

        // Booking Menunggu Konfirmasi (Pending)
        $pendingBookings = Booking::where('status', BookingStatus::Pending)->count();

        // Booking Terkonfirmasi Hari Ini
        $confirmedToday = Booking::where('status', BookingStatus::Confirmed)
            ->whereDate('scheduled_at', $today)
            ->count();

        // Total User
        $totalUsers = User::count();

        // Booking Selesai Bulan Ini
        $completedThisMonth = Booking::where('status', BookingStatus::Completed)
            ->whereMonth('scheduled_at', Carbon::now()->month)
            ->whereYear('scheduled_at', Carbon::now()->year)
            ->count();

        // Booking dengan Konfirmasi Kesiapan
        $readinessConfirmed = Booking::whereNotNull('readiness_confirmed_at')
            ->whereDate('scheduled_at', $today)
            ->count();

        // Pendapatan Bulan Ini (dari booking completed)
        $monthlyRevenue = Booking::where('status', BookingStatus::Completed)
            ->whereMonth('scheduled_at', Carbon::now()->month)
            ->whereYear('scheduled_at', Carbon::now()->year)
            ->join('packages', 'bookings.package_id', '=', 'packages.id')
            ->sum('packages.price');

        return [
            Stat::make('Booking Hari Ini', $todayBookings)
                ->description('Total sesi foto hari ini')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('primary')
                ->chart([7, 3, 4, 5, 6, $todayBookings]),

            Stat::make('Booking Bulan Ini', $monthlyBookings)
                ->description(Carbon::now()->format('F Y'))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('success'),

            Stat::make('Menunggu Konfirmasi', $pendingBookings)
                ->description('Booking pending')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingBookings > 0 ? 'warning' : 'success'),

            Stat::make('Jadwal Hari Ini', $confirmedToday)
                ->description('Booking confirmed hari ini')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('info'),

            Stat::make('Konfirmasi Kesiapan', $readinessConfirmed)
                ->description('User siap hari ini')
                ->descriptionIcon('heroicon-m-hand-thumb-up')
                ->color($readinessConfirmed > 0 ? 'success' : 'gray'),

            Stat::make('Pendapatan Bulan Ini', 'Rp ' . number_format($monthlyRevenue, 0, ',', '.'))
                ->description($completedThisMonth . ' booking selesai')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }
}
