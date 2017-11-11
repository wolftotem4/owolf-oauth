<?php

namespace OWolf\OAuth\Contracts;

use League\OAuth2\Client\Token\AccessToken;

abstract class OAuthManager implements OAuthManagerInterface
{
    /**
     * @var \OWolf\OAuth\Contracts\OAuthDriver
     */
    protected $driver;

    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var \League\OAuth2\Client\Token\AccessToken|null
     */
    protected $token = null;

    /**
     * @var \OWolf\OAuth\Contracts\ResourceOwnerWrapper|null
     */
    protected $owner = null;

    /**
     * OAuthManager constructor.
     * @param \OWolf\OAuth\Contracts\OAuthDriver $driver
     */
    public function __construct(OAuthDriver $driver)
    {
        $this->driver   = $driver;
    }

    /**
     * @return string
     */
    public function name()
    {
        return $this->driver()->name();
    }

    /**
     * @return \OWolf\OAuth\Contracts\OAuthDriver
     */
    public function driver()
    {
        return $this->driver;
    }

    /**
     * @return \League\OAuth2\Client\Provider\AbstractProvider
     */
    public function provider()
    {
        return $this->driver()->provider();
    }

    /**
     * @param  array  $options
     * @return string
     */
    public function getAuthorizationUrl(array $options = [])
    {
        return $this->driver()->getAuthorizationUrl($options);
    }

    /**
     * @return \League\OAuth2\Client\Token\AccessToken|null
     */
    public function token()
    {
        return $this->getToken();
    }

    /**
     * @return \League\OAuth2\Client\Token\AccessToken|null
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param  \League\OAuth2\Client\Token\AccessToken  $token
     * @return $this
     */
    public function setToken(AccessToken $token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @return \League\OAuth2\Client\Token\AccessToken
     */
    public function refreshToken()
    {
        $newToken = $this->driver()->refreshToken($this->getToken());
        $this->setToken($newToken);
        return $newToken;
    }

    /**
     * @param  string  $ownerId
     * @param  \League\OAuth2\Client\Token\AccessToken|null  $token
     * @return bool
     */
    public function revokeToken($ownerId, AccessToken $token = null)
    {
        $token = $token ?: $this->getToken();
        if (! $token) {
            return false;
        } elseif ($this->driver()->revokeToken($token, $ownerId)) {
            $this->token = null;

            return true;
        } else {
            return false;
        }
    }

    /**
     * @return string|null
     */
    public function ownerId()
    {
        return $this->getToken()->getResourceOwnerId() ?: $this->getResourceOwnerWrapper()->getId();
    }

    /**
     * @return \OWolf\OAuth\Contracts\ResourceOwnerWrapper|null
     */
    protected function getResourceOwnerWrapper()
    {
        if (! $this->getToken()) {
            return null;
        } else {
            $owner = $this->driver()->getResourceOwnerWrapper($this->getToken());
            $this->setResourceOwnerWrapper($owner);
            return $owner;
        }
    }

    /**
     * @param  \OWolf\OAuth\Contracts\ResourceOwnerWrapper  $owner
     * @return $this
     */
    protected function setResourceOwnerWrapper(ResourceOwnerWrapper $owner)
    {
        $this->owner = $owner;
        return $this;
    }

    /**
     * @return \OWolf\OAuth\Contracts\ResourceOwnerWrapper
     */
    public function getOwnerInfo()
    {
        return $this->getResourceOwnerWrapper();
    }
}