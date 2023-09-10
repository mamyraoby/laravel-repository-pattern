<?php

namespace Raoby\Providers;

use Illuminate\Support\ServiceProvider;

class ExtensionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/raoby.php' => config_path('raoby.php')
        ]);

        $this->mergeConfigFrom(__DIR__ . '/../../config/raoby.php', 'raoby');

        if ($this->app->runningInConsole()) {
            $this->commands([
                \Raoby\Commands\MakeRepositoryCommand::class,
            ]);
        }
    }
}
