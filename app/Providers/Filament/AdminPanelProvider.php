<?php

namespace App\Providers\Filament;

use Filament\Panel;
use Filament\PanelProvider;
use Filament\Pages\Dashboard;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Filament\Widgets\BookingChartWidget;
use App\Filament\Widgets\MonthlyRevenueChartWidget;
use App\Filament\Widgets\BookingStatusChartWidget;
use App\Filament\Widgets\TodayBookingsWidget;
use App\Filament\Widgets\UpcomingBookingsWidget;
use App\Filament\Widgets\PopularPackagesWidget;
use App\Filament\Widgets\LatestBookingsWidget;
use Filament\Forms\Components\RichEditor;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Filament\Http\Middleware\AuthenticateSession;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;

class AdminPanelProvider extends PanelProvider
{



    public function panel(Panel $panel): Panel
    {

        RichEditor::configureUsing(function (RichEditor $component) {
            $component
            ->toolbarButtons([
                'bold',
                'italic',
                'underline',
                'strike',
                'bulletList',
                'orderedList',
                'link',

            ])
            ->json();
        });

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->login()
            ->colors([
                'primary' => Color::Green,
            ])
               ->brandLogo(fn () => view('components.logo'))
            ->databaseNotifications()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                StatsOverviewWidget::class,
                BookingChartWidget::class,
                TodayBookingsWidget::class,
                MonthlyRevenueChartWidget::class,
                BookingStatusChartWidget::class,
                PopularPackagesWidget::class,
                UpcomingBookingsWidget::class,
                LatestBookingsWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
