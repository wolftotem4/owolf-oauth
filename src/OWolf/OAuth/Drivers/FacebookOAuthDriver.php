<?php

namespace OWolf\OAuth\Drivers;

use Illuminate\Support\Facades\URL;
use OWolf\OAuth\Contracts\OAuthDriver;
use OWolf\OAuth\ResourceOwnerWrappers\FacebookUserWrapper;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\Facebook;
use League\OAuth2\Client\Token\AccessToken;

class FacebookOAuthDriver extends OAuthDriver
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var \League\OAuth2\Client\Provider\Facebook
     */
    protected $provider;

    /**
     * FacebookOAuthDriver constructor.
     * @param  string  $name
     * @param  array   $config
     * @param  \League\OAuth2\Client\Provider\Facebook|null  $provider
     */
    public function __construct($name, array $config, Facebook $provider = null)
    {
        if (! array_has($config, 'params.redirectUri')) {
            array_set($config, 'params.redirectUri', URL::route('oauth.callback', ['provider' => $name]));
        }

        $this->name     = $name;
        $this->config   = $config;
        $this->provider = $provider ?: new Facebook(array_get($config, 'params', []));
    }

    /**
     * @param  string  $name
     * @param  array   $config
     * @return static
     */
    public static function make($name, array $config)
    {
        return new static($name, $config);
    }

    /**
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * @return \League\OAuth2\Client\Provider\Facebook
     */
    public function provider()
    {
        return $this->provider;
    }

    /**
     * @param  string|null  $key
     * @param  mixed        $default
     * @return array
     */
    public function config($key = null, $default = null)
    {
        return array_get($this->config, $key, $default);
    }

    /**
     * @param  array  $options
     * @return string
     */
    public function getAuthorizationUrl(array $options = [])
    {
        if (array_has($this->config, 'scope')) {
            $scope      = $this->config['scope'];
            $options    = compact('scope') + $options;
        }
        return $this->provider()->getAuthorizationUrl($options);
    }

    /**
     * @param  \League\OAuth2\Client\Token\AccessToken  $token
     * @return \OWolf\OAuth\Contracts\ResourceOwnerWrapper
     */
    public function getResourceOwnerWrapper(AccessToken $token)
    {
        return new FacebookUserWrapper($this->provider()->getResourceOwner($token));
    }

    /**
     * @return string
     *
     * @throws \Exception
     */
    protected function getBaseGraphUrl()
    {
        if (preg_match('#^(.*)/oauth/access_token$#', $this->provider()->getBaseAccessTokenUrl([]), $match)) {
            return $match[1];
        } else {
            throw new \Exception('Failed to get Facebook Graph Url');
        }
    }

    /**
     * @param  \League\OAuth2\Client\Token\AccessToken  $token
     * @param  string  $ownerId
     * @return bool
     */
    public function revokeToken(AccessToken $token, $ownerId)
    {
        try {
            $method     = 'DELETE';
            $url        = $this->getBaseGraphUrl() . '/' . $ownerId . '/permissions?access_token=' . $token->getToken();
            $request    = $this->provider()->getAuthenticatedRequest($method, $url, $token);
            $response   = $this->provider()->getParsedResponse($request);

            return array_get($response, 'success', false);
        } catch (IdentityProviderException $e) {
            return false;
        }
    }
}