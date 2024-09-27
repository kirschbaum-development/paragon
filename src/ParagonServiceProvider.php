<?php

namespace Kirschbaum\Paragon;

use Illuminate\Support\ServiceProvider;
use Kirschbaum\Paragon\Commands\ClearCacheCommand;
use Kirschbaum\Paragon\Commands\GenerateEnumsCommand;
use Kirschbaum\Paragon\Commands\MakeEnumMethodCommand;

class ParagonServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/paragon.php', 'paragon'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/paragon.php' => config_path('paragon.php'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                ClearCacheCommand::class,
                GenerateEnumsCommand::class,
                MakeEnumMethodCommand::class,
            ]);
        }
    }
}
