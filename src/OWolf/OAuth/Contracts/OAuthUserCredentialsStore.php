<?php

namespace OWolf\OAuth\Contracts;

use League\OAuth2\Client\Token\AccessToken;

interface OAuthUserCredentialsStore
{
    /**
     * @return string
     */
    public function name();

    /**
     * @return int|null
     */
    public function userId();

    /**
     * @return \League\OAuth2\Client\Token\AccessToken
     */
    public function get();

    /**
     * @param  \League\OAuth2\Client\Token\AccessToken  $token
     * @return $this
     */
    public function set(AccessToken $token);

    /**
     * @return string
     */
    public function ownerId();

    /**
     * @return $this
     */
    public function save();

    /**
     * @return $this
     */
    public function destroy();
}