<?php

namespace OWolf\OAuth\OAuthManagers;

use OWolf\OAuth\Contracts\OAuthManager;
use OWolf\OAuth\Drivers\GoogleOAuthDriver;

class GoogleOAuthManager extends OAuthManager
{
    /**
     * GoogleOAuthManager constructor.
     * @param \OWolf\OAuth\Drivers\GoogleOAuthDriver $driver
     */
    public function __construct(GoogleOAuthDriver $driver)
    {
        parent::__construct($driver);
    }
}