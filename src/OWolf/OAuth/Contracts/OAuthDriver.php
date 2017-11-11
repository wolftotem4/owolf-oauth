<?php

namespace OWolf\OAuth\Contracts;

use League\OAuth2\Client\Token\AccessToken;

abstract class OAuthDriver
{
    /**
     * @param  string $name
     * @param  array $config
     * @return static
     */
    abstract public static function make($name, array $config);

    /**
     * @return \League\OAuth2\Client\Provider\AbstractProvider
     */
    abstract public function provider();

    /**
     * @return string
     */
    abstract public function name();

    /**
     * @param  string|null  $key
     * @param  mixed        $default
     * @return array
     */
    abstract public function config($key = null, $default = null);

    /**
     * @param  string  $code
     * @return \League\OAuth2\Client\Token\AccessToken
     *
     * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     */
    public function getAccessTokenByCode($code)
    {
        return $this->provider()->getAccessToken('authorization_code', compact('code'));
    }

    /**
     * @param  \League\OAuth2\Client\Token\AccessToken  $token
     * @return \League\OAuth2\Client\Token\AccessToken
     */
    public function refreshToken(AccessToken $token)
    {
        return $this->provider()->getAccessToken('refresh_token', [
            'refresh_token' => $token->getRefreshToken(),
        ]);
    }

    /**
     * @param  \League\OAuth2\Client\Token\AccessToken  $token
     * @param  string  $ownerId
     * @return bool
     */
    public function revokeToken(AccessToken $token, $ownerId)
    {
        return true;
    }

    /**
     * @param  array  $options
     * @return string
     */
    abstract public function getAuthorizationUrl(array $options = []);

    /**
     * @param  \League\OAuth2\Client\Token\AccessToken  $token
     * @return \OWolf\OAuth\Contracts\ResourceOwnerWrapper
     */
    abstract public function getResourceOwnerWrapper(AccessToken $token);
}