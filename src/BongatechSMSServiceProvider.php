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
            __DIR__.'/Config/bongatech-sms.php' => config_path('bongatech-sms.php'),
        ], 'bongatech_sms_config');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/Config/bongatech-sms.php', 'bongatech-sms'
        );
    }
}
