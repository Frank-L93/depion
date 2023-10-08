<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('App\Models\Settings', function () {
            if (auth()->guest()) {
                $standard_settings = array();
                $standard_settings = json_encode(["layout" => "app", "language" => "nl"]);
                return $standard_settings;
            } else {
                $user = auth()->user()->id;
                if (User::find($user)->settings == 0) {
                    User::find($user)->update(['settings' => ["layout" => "app"]]);
                }
                return User::find($user)->settings();
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
