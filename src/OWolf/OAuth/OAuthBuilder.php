<?php

namespace OWolf\OAuth;

use Illuminate\Support\Facades\App;
use OWolf\OAuth\Contracts\OAuthDriver;
use OWolf\OAuth\Contracts\OAuthManager;
use OWolf\OAuth\Exceptions\InvalidOAuthDriverException;

class OAuthBuilder
{
    /**
     * @var \OWolf\OAuth\OAuthConfig
     */
    protected $config;

    /**
     * @var array
     */
    protected $factories = [];

    /**
     * OAuthBuilder constructor.
     * @param \OWolf\OAuth\OAuthConfig $config
     */
    public function __construct(OAuthConfig $config)
    {
        $this->config = $config;
    }

    /**
     * @param  string  $provider
     * @return \OWolf\OAuth\Contracts\OAuthSessionManager
     */
    public function make($provider)
    {
        $config         = $this->config->provider($provider);
        $factory        = $this->makeFactory($config->get('driver'));

        $driver         = $this->buildDriver($provider, $config->toArray(), $factory->driver());
        $manager        = $this->buildManager($driver, $factory->manager());
        $sessionManager = $this->buildSessionManager($manager, $factory->sessionManager());

        return $sessionManager;
    }

    /**
     * @param  string  $driver
     * @param  mixed  $factory
     * @return $this
     */
    public function extend($driver, $factory)
    {
        $this->factories[$driver] = $factory;
        return $this;
    }

    /**
     * @param  string  $driver
     * @return mixed
     *
     * @throws \OWolf\OAuth\Exceptions\InvalidOAuthDriverException
     */
    protected function makeFactory($driver)
    {
        if (! isset($this->factories[$driver])) {
            throw new InvalidOAuthDriverException('Invalid OAuth driver: ' . $driver);
        }
        return App::make($this->factories[$driver]);
    }

    /**
     * @param  string  $name
     * @param  array   $config
     * @param  string  $class
     * @return \OWolf\OAuth\Contracts\OAuthDriver
     */
    protected function buildDriver($name, array $config, $class)
    {
        return new $class($name, $config);
    }

    /**
     * @param  \OWolf\OAuth\Contracts\OAuthDriver  $driver
     * @param  string  $class
     * @return \OWolf\OAuth\Contracts\OAuthManager
     */
    protected function buildManager(OAuthDriver $driver, $class)
    {
        return new $class($driver);
    }

    /**
     * @param  \OWolf\OAuth\Contracts\OAuthManager  $manager
     * @param  string  $class
     * @param  bool    $autoInitialize
     * @return \OWolf\OAuth\Contracts\OAuthSessionManager
     */
    protected function buildSessionManager(OAuthManager $manager, $class, $autoInitialize = true)
    {
        return new $class($manager, App::make(OAuthSessionLoader::class), $autoInitialize);
    }
}