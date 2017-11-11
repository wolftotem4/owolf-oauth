<?php

namespace OWolf\OAuth\Foundation;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use OWolf\OAuth\OAuthUserCredentialsRepository;
use Illuminate\Support\Facades\Auth;

trait OAuthList
{
    /**
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $assign['oauthList']    = $this->oauthList();
        $assign['connectTo']    = function ($provider) { return $this->connectTo($provider); };
        $assign['revokeLink']   = function ($provider) { return $this->revokeLink($provider); };
        return View::make('oauth.connect.index', $assign);
    }

    /**
     * @param  string  $provider
     * @return string
     */
    protected function connectTo($provider)
    {
        return URL::route('oauth.login', compact('provider'));
    }

    /**
     * @param  string  $provider
     * @return string
     */
    protected function revokeLink($provider)
    {
        return URL::route('oauth.connect.revoke', compact('provider'));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function oauthList()
    {
        $repository = $this->oauthUserCredentialsRepository();
        return $repository->user($this->guard()->id())->all()->keyBy('name');
    }

    /**
     * @return \OWolf\OAuth\OAuthUserCredentialsRepository
     */
    protected function oauthUserCredentialsRepository()
    {
        return App::make(OAuthUserCredentialsRepository::class);
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard();
    }
}