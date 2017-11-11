<?php

namespace OWolf\OAuth\OAuthSessionManagers;

use OWolf\OAuth\Contracts\OAuthSessionManager;
use OWolf\OAuth\OAuthManagers\GoogleOAuthManager;
use OWolf\OAuth\OAuthSessionLoader;

class GoogleOAuthSessionManager extends OAuthSessionManager
{
    public function __construct(GoogleOAuthManager $manager, OAuthSessionLoader $loader, $autoInitialize = true)
    {
        parent::__construct($manager, $loader, $autoInitialize);
    }
}