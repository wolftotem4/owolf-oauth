<?php

namespace OWolf\OAuth;

use OWolf\OAuth\Contracts\OAuthUserCredentialsStore;
use OWolf\OAuth\OAuthCredentials\OAuthUserCredentialsStore as OAuthUserCredentialsDatabase;
use OWolf\OAuth\OAuthCredentials\OAuthUserCredentialsSession;
use League\OAuth2\Client\Token\AccessToken;

class OAuthSessionLoader
{
    /**
     * @var \OWolf\OAuth\Contracts\OAuthUserCredentialsStore[]
     */
    protected $stores = [];

    /**
     * @var int|null
     */
    protected $userId = null;

    /**
     * @var \Illuminate\Session\Store|\Illuminate\Session\SessionManager
     */
    protected $session;

    /**
     * OAuthSessionLoader constructor.
     * @param \Illuminate\Session\Store|\Illuminate\Session\SessionManager|null $session
     */
    public function __construct($session = null)
    {
        $this->session = $session ?: session();
    }

    /**
     * @return int|null
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param  int|null $userId
     * @return $this
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @return \OWolf\OAuth\Contracts\OAuthUserCredentialsStore[]
     */
    public function stores()
    {
        return $this->stores;
    }

    /**
     * @param  string  $name
     * @return \OWolf\OAuth\Contracts\OAuthUserCredentialsStore|null
     */
    public function store($name)
    {
        return $this->getStore($name);
    }

    /**
     * @param  string  $name
     * @return \OWolf\OAuth\Contracts\OAuthUserCredentialsStore|null
     */
    public function getStore($name)
    {
        return array_get($this->stores, $name);
    }

    /**
     * @param  \OWolf\OAuth\Contracts\OAuthUserCredentialsStore  $store
     * @return $this
     */
    public function setStore(OAuthUserCredentialsStore $store)
    {
        $this->stores[$store->name()] = $store;
        return $this;
    }

    /**
     * @return $this
     */
    public function clearStores()
    {
        $this->stores = [];
        return $this;
    }

    /**
     * @return $this
     */
    public function load()
    {
        $this->clearStores();
        $this->loadFromSession();
        $this->loadFromDatabase();

        return $this;
    }

    /**
     * 從 Session 載入 Access Token
     */
    protected function loadFromSession()
    {
        $store = $this->sessionStore();
        if ($store) {
            $this->setStore($store);
        }
    }

    /**
     * 從資料庫載入 Access Token
     */
    protected function loadFromDatabase()
    {
        if (is_null($this->getUserId())) {
            return;
        }

        $credentials = $this->repository()->user($this->getUserId())->all();

        $credentials->each(function (OAuthUserCredentials $store) {
            $store = new OAuthUserCredentialsDatabase($store->getConnection(), $store->name, $store->user_id, $store->owner_id, $store->toAccessToken());
            $this->setStore($store);
        });
    }

    /**
     * @return \OWolf\OAuth\OAuthUserCredentialsRepository
     */
    protected function repository()
    {
        return app()->make(OAuthUserCredentialsRepository::class);
    }

    /**
     * @return \Illuminate\Session\Store|\Illuminate\Session\SessionManager
     */
    public function session()
    {
        return $this->session;
    }

    /**
     * @return \OWolf\OAuth\OAuthCredentials\OAuthUserCredentialsSession|null
     */
    protected function sessionStore()
    {
        try {
            $session = $this->session();
            if ($session->has('oauth_session')) {
                $data       = $session->get('oauth_session');
                $name       = array_get($data, 'name');
                $ownerId    = array_get($data, 'owner_id');
                $token      = new AccessToken(array_get($data, 'token', []));
                return ($name && $ownerId) ? new OAuthUserCredentialsSession($session, $name, $ownerId, $token) : null;
            }
            return null;
        } catch (\InvalidArgumentException $e) {
            return null;
        }
    }
}