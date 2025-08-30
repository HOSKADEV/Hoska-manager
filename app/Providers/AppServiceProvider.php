<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Http\Controllers\Controller;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Create a blade directive for currency conversion
        Blade::directive('convertCurrency', function ($expression) {
            return "<?php echo \App\Helpers\CurrencyHelper::convert($expression); ?>";
        });
    }
}
