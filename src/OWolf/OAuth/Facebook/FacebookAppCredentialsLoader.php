<?php

namespace OWolf\OAuth\Facebook;

use League\OAuth2\Client\Provider\Facebook;

class FacebookAppCredentialsLoader
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var \League\OAuth2\Client\Provider\Facebook
     */
    protected $provider;

    /**
     * FacebookAppCredentialsLoader constructor.
     * @param  \League\OAuth2\Client\Provider\Facebook  $provider
     * @param  string  $name
     */
    public function __construct(Facebook $provider, $name)
    {
        $this->name     = $name;
        $this->provider = $provider;
    }

    /**
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * @return \League\OAuth2\Client\Provider\Facebook
     */
    public function provider()
    {
        return $this->provider;
    }

    /**
     * @return \League\OAuth2\Client\Token\AccessToken
     */
    public function getToken()
    {
        $oauth = FacebookAppCredentials::where('name', $this->name)->first();
        return ($oauth) ? $oauth->toAccessToken() : $this->regenerateToken();
    }

    /**
     * @return \League\OAuth2\Client\Token\AccessToken
     */
    public function regenerateToken()
    {
        $token = $this->provider->getAccessToken('client_credentials');
        FacebookAppCredentials::updateOrCreate([
            'name' => $this->name,
        ], [
            'access_token' => $token->getToken(),
        ]);
        return $token;
    }
}