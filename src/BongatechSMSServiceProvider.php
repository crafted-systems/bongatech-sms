<?php

namespace CraftedSystems\Bongatech;

use Illuminate\Support\ServiceProvider;

class BongatechSMSServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {

        $this->publishes([
            __DIR__ . '/Config/bongatech.php' => config_path('bongatech.php'),
        ], 'bongatech_sms_config');

        $this->app->singleton(BongatechSMS::class, function () {
            return new BongatechSMS(config('bongatech'));
        });

        $this->app->alias(BongatechSMS::class, 'bongatech-sms');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/Config/bongatech.php', 'bongatech'
        );
    }
}
