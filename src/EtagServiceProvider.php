<?php

namespace Divtag\LaravelEtag;

use Illuminate\Support\ServiceProvider;

class EtagServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app->singleton('etag', function () {
            return new EtagMiddleware();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides(): array
    {
        return ['etag'];
    }
}
