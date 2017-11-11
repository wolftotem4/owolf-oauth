<?php

namespace OWolf\OAuth\OAuthManagers;

use OWolf\OAuth\Contracts\OAuthManager;
use OWolf\OAuth\Drivers\FacebookOAuthDriver;

class FacebookOAuthManager extends OAuthManager
{
    /**
     * FacebookOAuthManager constructor.
     * @param \OWolf\OAuth\Drivers\FacebookOAuthDriver $driver
     */
    public function __construct(FacebookOAuthDriver $driver)
    {
        parent::__construct($driver);
    }
}