<?php

namespace App\Providers;

use Darryldecode\Cart\Cart;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        // Il faudrait le retirer et arranger le probleme avec mysql
        //  SQLSTATE[42000]: Syntax error or access violation: 1071
        Schema::defaultStringLength(191);

        View::composer(['layouts.app', 'products.show'], function ($view) {
            $view->with([
                'cartCount' => \Cart::getTotalQuantity(),
                'cartTotal' => \Cart::getTotal(),
            ]);
        });
    }
}
