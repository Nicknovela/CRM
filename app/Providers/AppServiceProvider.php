<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Prevent lazy loading in local environment
        Model::preventLazyLoading(app()->isLocal());

        // Set Carbon locale for Spanish date formatting
        Carbon::setLocale('es');

        // Blade directive: @currency($amount, $currency)
        Blade::directive('currency', function ($expression) {
            return "<?php echo format_currency($expression); ?>";
        });

        // Blade directive: @userdate($date)
        Blade::directive('userdate', function ($expression) {
            return "<?php echo format_date($expression); ?>";
        });

        // Blade directive: @userdatetime($datetime)
        Blade::directive('userdatetime', function ($expression) {
            return "<?php echo format_datetime($expression); ?>";
        });
    }
}
