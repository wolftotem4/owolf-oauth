<?php

namespace OWolf\OAuth\Providers;

use Illuminate\Support\ServiceProvider;
use OWolf\OAuth\Factory\GoogleFactory;
use OWolf\OAuth\OAuthBuilder;

class GoogleServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->make(OAuthBuilder::class)->extend('google', GoogleFactory::class);
    }
}