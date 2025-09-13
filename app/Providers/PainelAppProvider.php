<?php

namespace App\Providers;

use Filament\Panel;
use Filament\PanelProvider;

// Páginas & Widgets básicos
use Filament\Pages\Dashboard;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;

// Mesmos middlewares dos outros painéis
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use pxlrbt\FilamentSpotlight\SpotlightPlugin;

class PainelAppProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('app')
            ->path('app')                            // /app (login: /app/login)
            ->brandName('Marokah • App')
            ->login()
            ->colors(['primary' => '#16a34a'])
            ->default()

            ->viteTheme('resources/css/filament/app/theme.css')
            ->plugins([ SpotlightPlugin::make() ])

            // Descobrir resources/pages/widgets (padrão em PT-BR que você já usa)
            ->discoverResources(in: app_path('Filament/Recursos'), for: 'App\\Filament\\Recursos')
            ->discoverPages(in: app_path('Filament/Paginas'), for: 'App\\Filament\\Paginas')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')

            // Dashboard básico
            ->pages([Dashboard::class])
            ->widgets([AccountWidget::class, FilamentInfoWidget::class])

            // Middlewares Web + Filament (iguais aos demais)
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
            ])

            // Grupos de navegação do painel App
            ->navigationGroups([
            ]);
    }
}
