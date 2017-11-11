<?php

namespace OWolf\OAuth\Factory;

use OWolf\OAuth\Drivers\FacebookOAuthDriver;
use OWolf\OAuth\OAuthManagers\FacebookOAuthManager;
use OWolf\OAuth\OAuthSessionManagers\FacebookOAuthSessionManager;

class FacebookFactory
{
    /**
     * @return string
     */
    public function driver()
    {
        return FacebookOAuthDriver::class;
    }

    /**
     * @return string
     */
    public function manager()
    {
        return FacebookOAuthManager::class;
    }

    /**
     * @return string
     */
    public function sessionManager()
    {
        return FacebookOAuthSessionManager::class;
    }
}