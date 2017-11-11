<?php

namespace OWolf\OAuth\Facebook;

use Illuminate\Database\Eloquent\Model;
use League\OAuth2\Client\Token\AccessToken;

class FacebookAppCredentials extends Model
{
    /**
     * @var string
     */
    protected $table = 'oauth_fb_app_credentials';

    /**
     * @var string
     */
    protected $primaryKey = 'name';

    /**
     * @var string
     */
    protected $keyType = 'string';

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'access_token',
    ];

    /**
     * @return \League\OAuth2\Client\Token\AccessToken
     */
    public function toAccessToken()
    {
        return new AccessToken([
            'access_token'      => $this->access_token,
        ]);
    }
}