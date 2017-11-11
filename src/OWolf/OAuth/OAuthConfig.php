<?php

namespace OWolf\OAuth;

use OWolf\OAuth\Exceptions\InvalidOAuthProviderException;
use Illuminate\Config\Repository;

class OAuthConfig
{
    /**
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    /**
     * OAuthConfig constructor.
     * @param \Illuminate\Config\Repository $config
     */
    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    /**
     * @param  string  $name
     * @return \Illuminate\Support\Collection
     *
     * @throws \OWolf\OAuth\Exceptions\InvalidOAuthProviderException
     */
    public function provider($name)
    {
        $config = $this->get($name);

        if ($config && is_array($config)) {
            return collect($config);
        } else {
            throw new InvalidOAuthProviderException('Invalid OAuth provider: ' . $name);
        }
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function providers()
    {
        return collect($this->config->get('oauth.providers', []));
    }

    /**
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return $this->config->get("oauth.providers.$key", $default);
    }

    /**
     * @param  string  $provider
     * @return string
     *
     * @throws \Exception
     */
    public function oauthManager($provider)
    {
        $className = $this->config->get("oauth.oauth_manager.$provider");

        if ($className && class_exists($className)) {
            return $className;
        } else {
            throw new \Exception('Invalid OAuthManager class.');
        }
    }

    /**
     * @param  string  $provider
     * @return string
     *
     * @throws \Exception
     */
    public function oauthSessionManager($provider)
    {
        $className = $this->config->get("oauth.oauth_session_manager.$provider");

        if ($className && class_exists($className)) {
            return $className;
        } else {
            throw new \Exception('Invalid OAuthSessionManager class.');
        }
    }
}