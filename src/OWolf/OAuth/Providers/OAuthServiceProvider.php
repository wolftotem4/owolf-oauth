<?php

namespace OWolf\OAuth\Providers;

use Illuminate\Support\ServiceProvider;
use OWolf\OAuth\OAuthConfig;
use OWolf\OAuth\OAuthBuilder;
use OWolf\OAuth\OAuthFactory;
use OWolf\OAuth\OAuthSessionLoader;

class OAuthServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(OAuthConfig::class);
        $this->app->singleton(OAuthBuilder::class);
        $this->app->singleton(OAuthFactory::class);

        $this->app->singleton(OAuthSessionLoader::class, function ($app) {
            $auth = $app->make('auth');
            return (new OAuthSessionLoader())->setUserId($auth->id())->load();
        });
    }
}
