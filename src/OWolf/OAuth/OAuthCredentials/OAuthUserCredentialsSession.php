<?php

namespace OWolf\OAuth\OAuthCredentials;

use Illuminate\Support\Facades\App;
use OWolf\OAuth\OAuthUserCredentialsRepository;
use Illuminate\Support\Facades\DB;
use JsonSerializable;
use OWolf\OAuth\Contracts\OAuthUserCredentialsStore as BaseOAuthUserCredentialsStore;
use League\OAuth2\Client\Token\AccessToken;

class OAuthUserCredentialsSession implements BaseOAuthUserCredentialsStore, JsonSerializable
{
    /**
     * @var \Illuminate\Session\Store|\Illuminate\Session\SessionManager
     */
    protected $session;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $ownerId;

    /**
     * @var \League\OAuth2\Client\Token\AccessToken
     */
    protected $token;

    /**
     * OAuthUserCredentialsSession constructor.
     * @param \Illuminate\Session\Store|\Illuminate\Session\SessionManager $session
     * @param string $name
     * @param string $ownerId
     * @param \League\OAuth2\Client\Token\AccessToken $token
     */
    public function __construct($session, $name, $ownerId, AccessToken $token)
    {
        $this->session = $session;
        $this->name    = $name;
        $this->ownerId = $ownerId;
        $this->token   = $token;
    }

    /**
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * @return null
     */
    public function userId()
    {
        return null;
    }

    /**
     * @param  bool  $returnSelf
     * @return \OWolf\OAuth\Contracts\OAuthUserCredentialsStore|null
     */
    public function tryStore($returnSelf = true)
    {
        $repository = App::make(OAuthUserCredentialsRepository::class);
        $store      = $repository->provider($this->name())->owner($this->ownerId())->first();
        $fallback   = ($returnSelf) ? $this : null;
        return ($store) ? $this->toStore($store->user_id) : $fallback;
    }

    /**
     * @param  int  $userId
     * @return \OWolf\OAuth\OAuthCredentials\OAuthUserCredentialsStore
     */
    public function toStore($userId)
    {
        return new OAuthUserCredentialsStore(DB::connection(), $this->name, $userId, $this->ownerId(), $this->token);
    }

    /**
     * @return \League\OAuth2\Client\Token\AccessToken
     */
    public function get()
    {
        return $this->token;
    }

    /**
     * @param  \League\OAuth2\Client\Token\AccessToken  $token
     * @return $this
     */
    public function set(AccessToken $token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @return string
     */
    public function ownerId()
    {
        return $this->ownerId;
    }

    /**
     * @return $this
     */
    public function save()
    {
        $this->session->put('oauth_session', json_decode(json_encode($this), true));

        return $this;
    }

    /**
     * @return $this
     */
    public function destroy()
    {
        $this->session->forget('oauth_session');
        return $this;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'name'      => $this->name,
            'owner_id'  => $this->ownerId,
            'token'     => $this->token,
        ];
    }
}