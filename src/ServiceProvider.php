<?php
namespace Vioms\Cities;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Vioms\Cities\Console\Commands\PopulateCities;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // The publication files to publish
        $this->publishes([
            __DIR__ . '/../resources/config' => config_path()
        ], 'config');

        $this->publishes([
            __DIR__ . '/../resources/database/migrations/' => database_path('migrations')
        ], 'migrations');



        // Append the country settings
        $this->mergeConfigFrom(
            __DIR__ . '/../resources/config/cities.php', 'cities'
        );
    }

    /**
     * Register everything.
     *
     * @return void
     */
    public function register()
    {
        $this->registerCities();
        $this->registerCommands();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function registerCities()
    {
        $this->app->bind('cities', function($app)
        {
            return new \Vioms\Cities\Models\City();
        });
    }

    /**
     * Register the artisan commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        $this->commands([
            PopulateCities::class
        ]);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['cities'];
    }

}
