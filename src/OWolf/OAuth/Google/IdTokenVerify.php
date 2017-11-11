<?php

namespace OWolf\OAuth\Google;

use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\SignatureInvalidException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use phpseclib\Crypt\RSA;
use phpseclib\Math\BigInteger;

class IdTokenVerify
{
    const CERT_URL = 'https://www.googleapis.com/oauth2/v3/certs';
    const OAUTH2_ISSUER = 'accounts.google.com';
    const OAUTH2_ISSUER_HTTPS = 'https://accounts.google.com';

    /**
     * @var array|null
     */
    protected static $cacheCerts = null;

    /**
     * @var \Firebase\JWT\JWT
     */
    protected $jwt;

    public function __construct(JWT $jwt = null)
    {
        $this->jwt = $jwt ?: $this->jwtService();
    }

    /**
     * @param  string       $idToken
     * @param  string|null  $audience
     * @return \OWolf\OAuth\Google\IdToken|bool
     */
    public function verify($idToken, $audience = null)
    {
        $certs = static::getCerts();
        foreach ($certs as $cert) {
            $modulus = new BigInteger($this->jwt->urlsafeB64Decode($cert['n']), 256);
            $exponent = new BigInteger($this->jwt->urlsafeB64Decode($cert['e']), 256);

            $rsa = new RSA();
            $rsa->loadKey(['n' => $modulus, 'e' => $exponent]);

            try {
                $payload = (array) $this->jwt->decode($idToken, $rsa->getPublicKey(), array('RS256'));

                if ($audience && ! array_get($payload, 'aud') === $audience) {
                    return false;
                }

                $issuers = array(self::OAUTH2_ISSUER, self::OAUTH2_ISSUER_HTTPS);
                if (! in_array(array_get($payload, 'iss', []), $issuers)) {
                    return false;
                }

                return new IdToken($payload);
            } catch (ExpiredException $e) {
                return false;
            } catch (SignatureInvalidException $e) {
                // continue
            } catch (\DomainException $e) {
                // continue
            }
        }

        return false;
    }

    /**
     * @return array
     */
    protected static function getCerts()
    {
        if (static::$cacheCerts === null) {
            try {
                $client = new Client();
                $response = $client->request('GET', static::CERT_URL);
                static::$cacheCerts = array_get(json_decode($response->getBody(), true), 'keys', []);
            } catch (RequestException $e) {
                static::$cacheCerts = [];
            }
        }
        return static::$cacheCerts;
    }

    /**
     * @return \Firebase\JWT\JWT
     */
    protected function jwtService()
    {
        if (property_exists(JWT::class, 'leeway')) {
            // adds 1 second to JWT leeway
            // @see https://github.com/google/google-api-php-client/issues/827
            JWT::$leeway = 1;
        }
        return new JWT();
    }
}