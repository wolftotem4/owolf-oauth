<?php

namespace OWolf\OAuth\OAuthSessionManagers;

use Illuminate\Support\Facades\App;
use OWolf\OAuth\Contracts\OAuthSessionManager;
use OWolf\OAuth\Facebook\FacebookAppCredentialsLoader;
use OWolf\OAuth\OAuthManagers\FacebookOAuthManager;
use OWolf\OAuth\OAuthSessionLoader;

class FacebookOAuthSessionManager extends OAuthSessionManager
{
    /**
     * FacebookOAuthSessionManager constructor.
     * @param \OWolf\OAuth\OAuthManagers\FacebookOAuthManager $manager
     * @param \OWolf\OAuth\OAuthSessionLoader $loader
     * @param bool $autoInitialize
     */
    public function __construct(FacebookOAuthManager $manager, OAuthSessionLoader $loader, $autoInitialize = true)
    {
        parent::__construct($manager, $loader, $autoInitialize);
    }

    /**
     * @return \OWolf\OAuth\Facebook\FacebookAppCredentialsLoader
     */
    public function appCredentialsLoader()
    {
        return App::make(FacebookAppCredentialsLoader::class);
    }

    /**
     * @return \League\OAuth2\Client\Token\AccessToken
     */
    public function appAccessToken()
    {
        return $this->appCredentialsLoader()->getToken();
    }

    /**
     * @return bool
     */
    protected function revokeToken()
    {
        return $this->manager()->revokeToken($this->ownerId(), $this->appAccessToken());
    }
}