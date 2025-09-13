<?php

namespace App\Providers\Filament;

use App\Settings\AparenciaSettings;
use App\Settings\ConfiguracoesGerais;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\DB;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use pxlrbt\FilamentSpotlight\SpotlightPlugin;

class PainelMarokahProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $ui  = array_replace(
            \App\Settings\AparenciaSettings::defaults(),
            json_decode(DB::table('settings')->where('group','aparencia')->where('name','default')->value('payload') ?? '[]', true) ?: []
        );
        $geral = array_replace(
            \App\Settings\ConfiguracoesGerais::defaults(),
            json_decode(DB::table('settings')->where('group','geral')->where('name','default')->value('payload') ?? '[]', true) ?: []
        );

        $logoUrl  = !empty($ui['logo_header']) ? asset('storage/'.$ui['logo_header']) : null;
        $favicon  = !empty($ui['favicon'])      ? asset('storage/'.$ui['favicon'])      : null;
        $primary  = $ui['cor_primaria'] ?? '#16a34a';
        $siteName = $geral['nome_do_site'] ?? 'Marokah';

        // agora SEMPRE definimos brandName para aparecer no título da aba:
        $brandName = $siteName;

        $panel = $panel
            // passamos "hasLogo" para esconder o texto do cabeçalho quando houver logo
            ->renderHook('panels::head.start', fn () => view('components.theme-styles', [
                'ui'      => $ui,
                'hasLogo' => (bool) $logoUrl,
            ]))
            ->id('marokah')
            ->path('marokah')
            ->brandName($brandName)
            ->brandLogo($logoUrl)              // se null, mostra só o nome
            ->brandLogoHeight('28px')
            ->colors(['primary' => $primary])
            ->login()
            ->viteTheme('resources/css/filament/marokah/theme.css')
            ->plugins([ \pxlrbt\FilamentSpotlight\SpotlightPlugin::make() ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\Filament\Clusters')
            ->pages([Dashboard::class])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([AccountWidget::class, FilamentInfoWidget::class])
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
            ->authMiddleware([Authenticate::class])
            ->navigationGroups([
                'Plataforma',
                'Clientes',
                'Faturamento',
                'Usuários',
                'Integrações',
                'Relatórios',
                'Configurações',
            ]);

        if ($favicon) {
            $panel = $panel->favicon($favicon);
        }

        if (method_exists($panel, 'defaultThemeMode')) {
            $panel = $panel->defaultThemeMode(
                ($ui['tema_escuro_default'] ?? true)
                    ? \Filament\Enums\ThemeMode::Dark
                    : \Filament\Enums\ThemeMode::Light
            );
        } elseif (method_exists($panel, 'darkMode')) {
            $panel = $panel->darkMode((bool) ($ui['tema_escuro_default'] ?? true));
        }

        return $panel;
    }
}
