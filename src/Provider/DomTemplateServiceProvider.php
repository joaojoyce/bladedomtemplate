<?php namespace Joyce\DomTemplate\Provider;

use Illuminate\Support\ServiceProvider;
use Blade;
use Joyce\DomTemplate\DomTemplateService;

class DomTemplateServiceProvider extends ServiceProvider
{


    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {

        $this->publishes([
            __DIR__.'/../config/domtemplate.php' => config_path('domtemplate.php'),
        ], 'config');


        $directive = config('domtemplate.directive');


        Blade::directive($directive,function ($expression) {
            if (starts_with($expression, '(')) {
                $expression = substr($expression, 1, -1);
            }
            return "<?php echo app()->make('domtemplate')
                ->getTemplate($expression); ?>";

        });

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

        $this->mergeConfigFrom(__DIR__.'/../config/domtemplate.php', 'domtemplate');

        $this->app->singleton(DomTemplateService::class, DomTemplateService::class);
        $this->app->alias(DomTemplateService::class, 'domtemplate');

    }
}
