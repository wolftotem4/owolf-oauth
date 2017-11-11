<?php

namespace OWolf\OAuth\Facades;

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Route;
use OWolf\OAuth\OAuthFactory;

class OAuth extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return OAuthFactory::class;
    }

    public static function routes()
    {
        Route::get('/oauth/{provider}', 'Oauth\\LoginController@login')->name('oauth.login');
        Route::get('/oauth/{provider}/callback', 'Oauth\\LoginController@callback')->name('oauth.callback');
        Route::get('/oauth/{provider}/link', 'Oauth\\LoginController@link')->name('oauth.link');
        Route::get('/oauth/connect', 'Oauth\\OAuthConnection@index')->name('oauth.connect.index');
        Route::get('/oauth/{provider}/revoke', 'Oauth\\OAuthConnection@showRevokeConfirm')->name('oauth.connect.revoke');
        Route::post('/oauth/{provider}/revoke', 'Oauth\\OAuthConnection@revoke');
    }
}