<?php

namespace App\Providers;

use Illuminate\Console\Events\CommandFinished;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Spatie\Activitylog\ActivityLogStatus;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Comandos que escriben datos en masa: mientras corren no dejan Actividad (kit,
     * spec 38). Laravel solo dispara estos eventos desde `php artisan`, no con
     * `Artisan::call()` ni en los tests: por eso los seeders y los comandos propios
     * usan además App\Support\SinActividad.
     */
    private const COMANDOS_SIN_ACTIVIDAD = ['db:seed', 'migrate', 'migrate:fresh', 'migrate:refresh'];

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
        Schema::defaultStringLength(191);

        Event::listen(CommandStarting::class, function (CommandStarting $evento) {
            if (in_array($evento->command, self::COMANDOS_SIN_ACTIVIDAD, true)) {
                app(ActivityLogStatus::class)->disable();
            }
        });
        Event::listen(CommandFinished::class, function (CommandFinished $evento) {
            if (in_array($evento->command, self::COMANDOS_SIN_ACTIVIDAD, true)) {
                app(ActivityLogStatus::class)->enable();
            }
        });
    }
}
