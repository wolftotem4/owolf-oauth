<?php

namespace OWolf\OAuth\Providers;

use Illuminate\Support\ServiceProvider;
use OWolf\OAuth\Factory\FacebookFactory;
use OWolf\OAuth\OAuthBuilder;

class FacebookServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->make(OAuthBuilder::class)->extend('facebook', FacebookFactory::class);
    }
}