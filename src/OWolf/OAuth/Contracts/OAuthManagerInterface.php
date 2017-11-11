<?php

namespace OWolf\OAuth\Contracts;

use League\OAuth2\Client\Token\AccessToken;

interface OAuthManagerInterface
{
    /**
     * @return string
     */
    public function name();

    /**
     * @return \OWolf\OAuth\Contracts\OAuthDriver
     */
    public function driver();

    /**
     * @return \League\OAuth2\Client\Provider\AbstractProvider
     */
    public function provider();

    /**
     * @return \League\OAuth2\Client\Token\AccessToken|null
     */
    public function token();

    /**
     * @return \League\OAuth2\Client\Token\AccessToken|null
     */
    public function getToken();

    /**
     * @return string|null
     */
    public function ownerId();

    /**
     * @param  \League\OAuth2\Client\Token\AccessToken  $token
     * @return $this
     */
    public function setToken(AccessToken $token);

    /**
     * @param  array  $options
     * @return string
     */
    public function getAuthorizationUrl(array $options = []);

    /**
     * @return \League\OAuth2\Client\Token\AccessToken
     */
    public function refreshToken();

    /**
     * @return \OWolf\OAuth\Contracts\ResourceOwnerWrapper
     */
    public function getOwnerInfo();
}