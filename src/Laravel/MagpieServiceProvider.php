<?php

declare(strict_types=1);

namespace Magpie\Laravel;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Magpie\Http\Config;
use Magpie\Magpie;

/**
 * Laravel service provider for the Magpie SDK.
 *
 * This service provider registers the Magpie SDK with Laravel's service container
 * and publishes configuration files for easy integration with Laravel applications.
 */
class MagpieServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        // Merge default configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/magpie.php',
            'magpie'
        );

        // Register the Magpie client as a singleton
        $this->app->singleton(Magpie::class, function ($app) {
            $config = $app->make('config')->get('magpie');

            // Validate required configuration
            if (empty($config['secret_key'])) {
                throw new \InvalidArgumentException(
                    'Magpie secret key is required. Please set MAGPIE_SECRET_KEY in your .env file or publish the magpie config.'
                );
            }

            $httpConfig = new Config([
                'baseUrl' => $config['base_url'],
                'apiVersion' => $config['api_version'],
                'timeout' => $config['timeout'],
                'connectTimeout' => $config['connect_timeout'],
                'maxRetries' => $config['max_retries'],
                'retryDelay' => $config['retry_delay'],
                'debug' => $config['debug'],
                'verifySsl' => $config['verify_ssl'],
                'defaultHeaders' => $config['default_headers'] ?? [],
            ]);

            $logger = $config['logging']['enabled'] && $app->bound('log') 
                ? $app->make('log')->channel($config['logging']['channel'])
                : null;

            return new Magpie($config['secret_key'], $httpConfig, $logger);
        });

        // Register alias for easier access
        $this->app->alias(Magpie::class, 'magpie');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Publish configuration file
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/magpie.php' => config_path('magpie.php'),
            ], 'magpie-config');

            // Publish migrations if they exist
            // $this->publishes([
            //     __DIR__ . '/../../database/migrations' => database_path('migrations'),
            // ], 'magpie-migrations');
        }

        // Register artisan commands if running in console
        if ($this->app->runningInConsole()) {
            $this->commands([
                // Add custom Artisan commands here
                // Commands\InstallCommand::class,
            ]);
        }

        // Register event listeners
        $this->registerEventListeners();

        // Register view composers if needed
        // view()->composer('magpie::*', MagpieViewComposer::class);
    }

    /**
     * Register event listeners for Magpie operations.
     *
     * @return void
     */
    protected function registerEventListeners(): void
    {
        // You can register event listeners for webhook processing, etc.
        // Event::listen(WebhookReceived::class, ProcessWebhookListener::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [
            Magpie::class,
            'magpie',
        ];
    }
}
