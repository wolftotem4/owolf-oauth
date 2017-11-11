<?php

namespace OWolf\OAuth\Contracts;

interface ResourceOwnerWrapper
{
    /**
     * @return string|null
     */
    public function getId();

    /**
     * @return string|null
     */
    public function getEmail();

    /**
     * @return string|null
     */
    public function getName();

    /**
     * @return array
     */
    public function toArray();
}