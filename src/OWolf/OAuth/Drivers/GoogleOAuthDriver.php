<?php

namespace OWolf\OAuth\Drivers;

use Illuminate\Support\Facades\URL;
use OWolf\OAuth\Contracts\OAuthDriver;
use OWolf\OAuth\Google\IdTokenVerify;
use OWolf\OAuth\ResourceOwnerWrappers\GoogleUserWrapper;
use GuzzleHttp\Exception\BadResponseException;
use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Token\AccessToken;

class GoogleOAuthDriver extends OAuthDriver
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
     * @var \League\OAuth2\Client\Provider\Google
     */
    protected $provider;

    /**
     * GoogleOAuthDriver constructor.
     * @param string  $name
     * @param array   $config
     * @param \League\OAuth2\Client\Provider\Google|null $provider
     */
    public function __construct($name, array $config, Google $provider = null)
    {
        if (! array_has($config, 'params.redirectUri')) {
            array_set($config, 'params.redirectUri', URL::route('oauth.callback', ['provider' => $name]));
        }

        $this->name     = $name;
        $this->config   = $config;
        $this->provider = $provider ?: new Google(array_get($config, 'params', []));
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
     * @return \League\OAuth2\Client\Provider\Google
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
     * @return \OWolf\OAuth\Google\IdToken|null
     */
    public function idToken(AccessToken $token)
    {
        $idToken = array_get($token->getValues(), 'id_token');
        if ($idToken) {
            $verifier = new IdTokenVerify();
            return $verifier->verify($idToken) ?: null;
        }
        return null;
    }

    /**
     * @param  \League\OAuth2\Client\Token\AccessToken  $token
     * @return \OWolf\OAuth\Contracts\ResourceOwnerWrapper
     */
    public function getResourceOwnerWrapper(AccessToken $token)
    {
        return new GoogleUserWrapper($this->provider()->getResourceOwner($token));
    }

    /**
     * @return string
     */
    protected function getRevokeTokenUrl()
    {
        return 'https://accounts.google.com/o/oauth2/revoke';
    }

    /**
     * @param  \League\OAuth2\Client\Token\AccessToken  $token
     * @param  string  $ownerId
     * @return bool
     */
    public function revokeToken(AccessToken $token, $ownerId)
    {
        try {
            $token      = $token->getRefreshToken() ?: $token->getToken();
            $url        = $this->getRevokeTokenUrl() . '?token=' . $token;
            $request    = $this->provider()->getRequestFactory()->getRequest('GET', $url);
            $response   = $this->provider()->getResponse($request);

            return ($response->getStatusCode() == 200);
        } catch (BadResponseException $e) {
            return false;
        }
    }
}