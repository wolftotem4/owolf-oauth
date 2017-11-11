<?php

namespace OWolf\OAuth\Factory;

use OWolf\OAuth\Drivers\GoogleOAuthDriver;
use OWolf\OAuth\OAuthManagers\GoogleOAuthManager;
use OWolf\OAuth\OAuthSessionManagers\GoogleOAuthSessionManager;

class GoogleFactory
{
    /**
     * @return string
     */
    public function driver()
    {
        return GoogleOAuthDriver::class;
    }

    /**
     * @return string
     */
    public function manager()
    {
        return GoogleOAuthManager::class;
    }

    /**
     * @return string
     */
    public function sessionManager()
    {
        return GoogleOAuthSessionManager::class;
    }
}