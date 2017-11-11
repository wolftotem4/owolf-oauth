<?php

namespace OWolf\OAuth;

class OAuthFactory
{
    /**
     * @var \OWolf\OAuth\OAuthBuilder
     */
    protected $builder;

    /**
     * @var array
     */
    protected $instances = [];

    /**
     * OAuthFactory constructor.
     * @param \OWolf\OAuth\OAuthBuilder $builder
     */
    public function __construct(OAuthBuilder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * @param  string  $provider
     * @return \OWolf\OAuth\Contracts\OAuthSessionManager
     */
    public function make($provider)
    {
        if (! isset($this->instances[$provider])) {
            $this->instances[$provider] = $this->builder->make($provider);
        }
        return $this->instances[$provider];
    }
}