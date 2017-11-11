<?php

namespace OWolf\OAuth\ResourceOwnerWrappers;

use OWolf\OAuth\Contracts\ResourceOwnerWrapper;
use Illuminate\Contracts\Support\Arrayable;
use League\OAuth2\Client\Provider\GoogleUser;

class GoogleUserWrapper implements ResourceOwnerWrapper, Arrayable, \JsonSerializable
{
    /**
     * @var \League\OAuth2\Client\Provider\GoogleUser
     */
    protected $user;

    /**
     * GoogleUserWrapper constructor.
     * @param \League\OAuth2\Client\Provider\GoogleUser $user
     */
    public function __construct(GoogleUser $user)
    {
        $this->user = $user;
    }

    /**
     * @return \League\OAuth2\Client\Provider\ResourceOwnerInterface
     */
    public function resourceOwner()
    {
        return $this->user;
    }

    /**
     * @return string|null
     */
    public function getId()
    {
        return $this->user->getId();
    }

    /**
     * @return string|null
     */
    public function getEmail()
    {
        return $this->user->getEmail();
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->user->getName();
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->user->toArray();
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}