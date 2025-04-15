<?php

namespace App;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class FeedServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/feed.php' => config_path('feed.php'),
        ], 'config');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'feed');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/feed'),
        ], 'views');

        $this->registerLinksComposer();
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/feed.php', 'feed');

        $this->registerRouteMacro();
    }

    protected function registerRouteMacro()
    {
        $router = $this->app['router'];

        $router->macro('feeds', function ($baseUrl = '') use ($router): void {

            foreach (config('feed.feeds') as $name => $configuration) {
                $url = 'interndepion.nl/feeds/'.$name;


            }
        });
    }

    public function registerLinksComposer()
    {
        View::composer('feed::links', function ($view): void {
            $view->with('feeds', $this->feeds());
        });
    }

    protected function feeds()
    {
        return collect(config('feed.feeds'));
    }
}
