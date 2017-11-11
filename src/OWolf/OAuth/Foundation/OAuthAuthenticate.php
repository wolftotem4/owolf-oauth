<?php

namespace OWolf\OAuth\Foundation;

use Illuminate\Support\Facades\View;
use OWolf\OAuth\Contracts\OAuthSessionManager;
use OWolf\OAuth\Contracts\OAuthUserCredentialsStore;
use OWolf\OAuth\Facades\OAuth;
use OWolf\OAuth\OAuthCredentials\OAuthUserCredentialsSession;
use OWolf\OAuth\OAuthUserCredentialsRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;

trait OAuthAuthenticate
{
    /**
     * @param  string  $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login($provider)
    {
        $manager = $this->oauthSessionManager($provider);

        $authUrl = $this->getAuthorizationUrl($manager);

        return Redirect::to($authUrl);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function callback(Request $request, $provider)
    {
        $manager = $this->oauthSessionManager($provider);

        if (! $manager->validateState($request->query('state'))) {
            App::abort(401, 'Invalid state parameter.');
        }

        $credentials = $manager->generateStoreUsingCode($request->query('code'))->save();

        if ($credentials->userId() === null) {

            return Redirect::to($this->oauthLinkRedirectTo($provider));

        } else {

            return $this->attemptLogin($credentials);
        }
    }

    /**
     * @param  string  $provider
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function link($provider)
    {
        $manager = $this->oauthSessionManager($provider);

        $credentials = $manager->store();

        if (! $credentials || $credentials->userId() !== null) {

            // 忽略空認證
            // 忽略已綁定的 AccessToken
            return Redirect::intended($this->redirectPath());

        } elseif ($credentials instanceof OAuthUserCredentialsSession) {

            return $this->registerOAuth($manager, $credentials);

        } else {

            return $this->attemptLogin($credentials);
        }
    }

    /**
     * @param  \OWolf\OAuth\Contracts\OAuthSessionManager  $manager
     * @return string
     */
    protected function getAuthorizationUrl(OAuthSessionManager $manager)
    {
        return $manager->getAuthorizationUrl($this->getAuthorizationParams($manager));
    }

    /**
     * @param  \OWolf\OAuth\Contracts\OAuthSessionManager  $manager
     * @return array
     */
    protected function getAuthorizationParams(OAuthSessionManager $manager)
    {
        $repository = App::make(OAuthUserCredentialsRepository::class);
        $params     = [];

        switch ($manager->driver()->config('driver')) {
            case 'google':
                if ($this->guard()->check()) {
                    $credentials = $repository->provider($manager->name())->user($this->guard()->id())->first();
                    if ($credentials) {
                        $params['login_hint']       = $credentials->owner_id;
                    } else {
                        $params['approval_prompt']  = null;
                        $params['prompt']           = 'consent';
                    }
                }
                break;
        }

        return $params;
    }

    /**
     * @param  \OWolf\OAuth\Contracts\OAuthUserCredentialsStore  $credentials
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function attemptLogin(OAuthUserCredentialsStore $credentials)
    {
        $this->guard()->loginUsingId($credentials->userId());

        return Redirect::intended($this->redirectPath());
    }

    /**
     * @param  \OWolf\OAuth\Contracts\OAuthSessionManager  $manager
     * @param  \OWolf\OAuth\OAuthCredentials\OAuthUserCredentialsSession  $credentials
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     *
     * @throws \OWolf\OAuth\Exceptions\OAuthOwnerHasTakenException
     */
    protected function registerOAuth(OAuthSessionManager $manager, OAuthUserCredentialsSession $credentials)
    {
        $owner = $manager->getOwnerInfo();

        if (! $this->guard()->check()) {

            if (method_exists($this, 'emailExists') && $this->emailExists($owner->getEmail())) {

                return $this->showEmailExists();

            } else {
                $user = $this->registerNewUser($owner);

                $this->guard()->login($user);
            }
        }

        $credentials->toStore($this->guard()->id())->save();

        return Redirect::intended($this->redirectPath());
    }

    /**
     * @return \Illuminate\Http\Response
     */
    protected function showEmailExists()
    {
        return View::make('oauth.email_exists');
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

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard();
    }

    /**
     * @param  string  $provider
     * @return string
     */
    protected function oauthLinkRedirectTo($provider)
    {
        return URL::route('oauth.link', compact('provider'));
    }
}