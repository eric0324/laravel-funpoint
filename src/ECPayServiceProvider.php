<?php
namespace TsaiYiHua\FunPoint;

use Illuminate\Support\ServiceProvider;

class FunPointServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->registerConfigs();
        }
        $this->registerResources();
        if (FunPoint::$useFunPointRoute) {
            $this->loadRoutesFrom(__DIR__ . '/routes.php');
        }
    }

    protected function registerConfigs()
    {
        $this->publishes([
            __DIR__ . '/../config/ecpay.php' => config_path('ecpay.php')
        ], 'ecpay');
    }

    protected function registerResources()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'ecpay');
    }
}