<?php

namespace OWolf\OAuth;

use App\User;
use Illuminate\Database\Eloquent\Model;
use League\OAuth2\Client\Token\AccessToken;

class OAuthUserCredentials extends Model
{
    /**
     * @var string
     */
    protected $table = 'oauth_user_credentials';

    /**
     * @var array
     */
    protected $fillable = [
        'user_id',
        'name',
        'owner_id',
        'access_token',
        'refresh_token',
        'expires_at',
    ];

    /**
     * @var array
     */
    protected $dates = ['expires_at', 'created_at', 'updated_at'];

    /**
     * @var array
     */
    protected $casts = [
        'user_id' => 'integer',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * @return \League\OAuth2\Client\Token\AccessToken
     */
    public function toAccessToken()
    {
        return new AccessToken([
            'access_token'      => $this->access_token,
            'resource_owner_id' => $this->owner_id,
            'refresh_token'     => $this->refresh_token,
            'expires'           => $this->expires_at->getTimestamp(),
        ]);
    }
}