<?php

namespace Doki1k\Parameters;

use Illuminate\Support\ServiceProvider;

class LmpParametersServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        $this->registerPublishables();
    }

    protected function registerPublishables(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        if (empty(glob(app_path('Models/Parameter.php')))) {
            $this->publishes([
                __DIR__.'/../app/Models/Parameter.php' => app_path('Models/Parameter.php'),
            ], 'models');
        }

        $this->publishes([
            __DIR__.'/../app/Helpers/helpers-common.php' => app_path('Helpers/helpers-common.php'),
            __DIR__.'/../app/Helpers/helpers-log.php' => app_path('Helpers/helpers-log.php'),
            __DIR__.'/../app/Helpers/helpers-parameters.php' => app_path('Helpers/helpers-parameters.php'),
            __DIR__.'/../app/Helpers/helpers-useragent.php' => app_path('Helpers/helpers-useragent.php'),
        ], 'helpers');


        if (empty(glob(database_path('migrations/*_create_parameters_table.php')))) {
            $this->publishes([
                __DIR__.'/../database/migrations/create_parameters_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_parameters_table.php'),
            ], 'migrations');
        }
    }
}
