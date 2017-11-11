<?php

namespace OWolf\OAuth\Contracts;

use OWolf\OAuth\OAuthCredentials\OAuthUserCredentialsSession;
use OWolf\OAuth\OAuthSessionLoader;
use League\OAuth2\Client\Token\AccessToken;

abstract class OAuthSessionManager implements OAuthManagerInterface
{
    /**
     * @var \OWolf\OAuth\Contracts\OAuthManager
     */
    protected $manager;

    /**
     * @var \OWolf\OAuth\OAuthSessionLoader
     */
    protected $loader;

    /**
     * OAuthCommander constructor.
     * @param  \OWolf\OAuth\Contracts\OAuthManager  $manager
     * @param  \OWolf\OAuth\OAuthSessionLoader      $loader
     * @param  bool  $autoInitialize
     */
    public function __construct(OAuthManager $manager, OAuthSessionLoader $loader, $autoInitialize = true)
    {
        $this->manager = $manager;
        $this->loader  = $loader;

        ($autoInitialize) and $this->initialize();
    }

    /**
     * @return $this
     */
    public function initialize()
    {
        $store = $this->store();
        if ($store) {
            $this->manager()->setToken($store->get());
            $this->refreshIfExpired();
        }
        return $this;
    }

    /**
     * @return string
     */
    public function name()
    {
        return $this->manager()->name();
    }

    /**
     * @return \OWolf\OAuth\Contracts\OAuthDriver
     */
    public function driver()
    {
        return $this->manager()->driver();
    }

    /**
     * @return \League\OAuth2\Client\Provider\AbstractProvider
     */
    public function provider()
    {
        return $this->manager()->provider();
    }

    /**
     * @return \OWolf\OAuth\Contracts\OAuthUserCredentialsStore|null
     */
    public function store()
    {
        return $this->loader()->store($this->name());
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
        $store = $this->store();
        return ($store) ? $store->get() : null;
    }

    /**
     * @param  \League\OAuth2\Client\Token\AccessToken  $token
     * @return $this
     */
    public function setToken(AccessToken $token)
    {
        $store = $this->store();
        if ($store) {
            $store->set($token);
        }
        $this->manager()->setToken($token);

        return $this;
    }

    /**
     * @return string|null
     */
    public function ownerId()
    {
        $store = $this->store();
        return ($store) ? $store->ownerId() : null;
    }

    /**
     * @return int|null
     */
    public function userId()
    {
        $store = $this->store();
        return ($store) ? $store->userId() : null;
    }

    /**
     * @return \Illuminate\Session\Store|\Illuminate\Session\SessionManager
     */
    public function session()
    {
        return $this->loader()->session();
    }

    /**
     * @param  array  $options
     * @return string
     */
    public function getAuthorizationUrl(array $options = [])
    {
        $url = $this->manager()->getAuthorizationUrl($options);
        $this->session()->put('oauth2state', $this->provider()->getState());
        return $url;
    }

    /**
     * @return bool
     */
    public function revoke()
    {
        if ($this->revokeToken()) {
            $store = $this->store();
            ($store) and $store->destroy();
            return true;
        }
        return false;
    }

    /**
     * @return \OWolf\OAuth\Contracts\OAuthManager
     */
    public function manager()
    {
        return $this->manager;
    }

    /**
     * @return \OWolf\OAuth\OAuthSessionLoader
     */
    public function loader()
    {
        return $this->loader;
    }

    /**
     * @return bool
     */
    protected function revokeToken()
    {
        return $this->manager()->revokeToken($this->ownerId());
    }

    /**
     * @param  string  $code
     * @return \OWolf\OAuth\Contracts\OAuthUserCredentialsStore
     *
     * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     */
    public function generateStoreUsingCode($code)
    {
        $token = $this->driver()->getAccessTokenByCode($code);
        $this->setToken($token);
        $ownerId = $this->manager()->ownerId();

        $store = new OAuthUserCredentialsSession($this->session(), $this->name(), $ownerId, $token);
        $store = $store->tryStore();
        $this->loader()->setStore($store);

        return $store;
    }

    /**
     * @return \League\OAuth2\Client\Token\AccessToken
     */
    public function refreshToken()
    {
        $newToken = $this->manager()->refreshToken();
        $this->store()->set($newToken)->save();
        return $newToken;
    }

    /**
     * @param  string  $state
     * @return bool
     */
    public function validateState($state)
    {
        return ($state === $this->session()->get('oauth2state'));
    }

    /**
     * @return \OWolf\OAuth\Contracts\ResourceOwnerWrapper
     */
    public function getOwnerInfo()
    {
        return $this->manager()->getOwnerInfo();
    }

    /**
     * @return $this
     */
    public function refreshIfExpired()
    {
        if ($store = $this->store()) {
            $refreshable    = ($store && $store->get()->getRefreshToken());

            if ($refreshable && $store->get()->hasExpired()) {
                $this->refreshToken();
            }
        }

        return $this;
    }
}