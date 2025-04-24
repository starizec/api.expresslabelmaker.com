<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Opcodes\LogViewer\Facades\LogViewer;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        LogViewer::auth(function ($request) {
            return $request->user()
                && in_array($request->user()->email, [
                    'davor@emedia.hr',
                    'h.javorovic@gmail.com',
                    'davorzec.emedia@gmail.com'
                ]);
        });
    }
}
