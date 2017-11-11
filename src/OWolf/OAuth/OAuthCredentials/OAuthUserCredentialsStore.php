<?php

namespace OWolf\OAuth\OAuthCredentials;

use OWolf\OAuth\Exceptions\OAuthOwnerHasTakenException;
use OWolf\OAuth\OAuthUserCredentials;
use Illuminate\Support\Carbon;
use JsonSerializable;
use OWolf\OAuth\Contracts\OAuthUserCredentialsStore as BaseOAuthUserCredentialsStore;
use Illuminate\Database\Connection;
use League\OAuth2\Client\Token\AccessToken;

class OAuthUserCredentialsStore implements BaseOAuthUserCredentialsStore, JsonSerializable
{
    /**
     * @var \Illuminate\Database\Connection
     */
    protected $connection;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var int
     */
    protected $userId;

    /**
     * @var string
     */
    protected $ownerId;

    /**
     * @var \League\OAuth2\Client\Token\AccessToken
     */
    protected $token;

    /**
     * @var \OWolf\OAuth\OAuthUserCredentials
     */
    protected static $resolver;

    /**
     * OAuthAccessTokenStore constructor.
     * @param  \Illuminate\Database\Connection  $connection
     * @param  string  $name
     * @param  int     $userId
     * @param  string  $ownerId
     * @param  \League\OAuth2\Client\Token\AccessToken  $token
     */
    public function __construct(Connection $connection, $name, $userId, $ownerId, AccessToken $token)
    {
        $this->connection   = $connection;
        $this->name         = $name;
        $this->userId       = $userId;
        $this->ownerId      = $ownerId;
        $this->token        = $token;
    }

    /**
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function userId()
    {
        return $this->userId;
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
     *
     * @throws \OWolf\OAuth\Exceptions\OAuthOwnerHasTakenException
     */
    public function save()
    {
        $token = $this->get();

        $credentials = OAuthUserCredentials::firstOrNew([
            'name'          => $this->name(),
            'owner_id'      => $this->ownerId(),
        ], [
            'user_id'       => $this->userId(),
        ])->fill([
            'access_token'  => $token->getToken(),
            'expires_at'    => Carbon::createFromTimestamp($token->getExpires()),
        ]);

        // 只有在有新的 Refresh Token 時寫入
        // 避免清除 Refresh Token
        if ($refreshToken = $token->getRefreshToken()) {
            $credentials->refresh_token = $token->getRefreshToken();
        }

        if ($credentials->user_id !== $this->userId()) {
            throw new OAuthOwnerHasTakenException('The owner id has been Taken.');
        }

        $credentials->save();

        return $this;
    }

    /**
     * @return $this
     */
    public function destroy()
    {
        OAuthUserCredentials::where('name', $this->name())->where('owner_id', $this->ownerId())->delete();
        return $this;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'name'      => $this->name,
            'user_id'   => $this->userId,
            'owner_id'  => $this->ownerId,
            'token'     => $this->token
        ];
    }

    /**
     * @param  string  $name
     * @param  array   $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->token, $name], $arguments);
    }
}