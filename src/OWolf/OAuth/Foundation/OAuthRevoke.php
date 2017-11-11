<?php

namespace OWolf\OAuth\Foundation;

use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use OWolf\OAuth\Facades\OAuth;

trait OAuthRevoke
{
    /**
     * @param  string  $provider
     * @return \Illuminate\Http\Response
     */
    public function showRevokeConfirm($provider)
    {
        return View::make('oauth.connect.revoke_confirm', compact('provider'));
    }

    /**
     * @param  string  $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function revoke($provider)
    {
        $manager = $this->oauthSessionManager($provider);
        $manager->revoke();

        return Redirect::intended($this->afterRevocationTo());
    }

    /**
     * @return string
     */
    protected function afterRevocationTo()
    {
        return URL::route('oauth.connect.index');
    }

    /**
     * @param  string  $provider
     * @return \OWolf\OAuth\Contracts\OAuthSessionManager
     *
     * @throws \OWolf\OAuth\Exceptions\InvalidOAuthProviderException
     */
    protected function oauthSessionManager($provider)
    {
        return OAuth::make($provider);
    }
}