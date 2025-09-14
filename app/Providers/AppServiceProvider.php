<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Support\EmailRuntime;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->runningInConsole()) {
            return;
        }

        // Evita erro em ambientes/migrations iniciais
        if (Schema::hasTable('settings')) {
            try {
                EmailRuntime::apply();
            } catch (\Throwable $e) {
                // opcional: \Log::warning('SMTP runtime não aplicado', ['e' => $e->getMessage()]);
            }
        }
}
}
