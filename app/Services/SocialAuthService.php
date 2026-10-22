<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Minimal OAuth 2.0 "authorization code" client for Google and Facebook.
 *
 * Deliberately package-free — it uses Laravel's HTTP client directly, the same
 * approach as App\Services\RazorpayService, to avoid pulling in a new Composer
 * dependency. It only does what social sign-in needs: build the consent URL,
 * swap the returned code for an access token, and fetch the user's basic
 * profile. Credentials are read from config/services.php (env-backed).
 */
class SocialAuthService
{
    /**
     * Per-provider endpoints and requested scopes.
     *
     * @var array<string, array<string, string>>
     */
    protected array $providers = [
        'google' => [
            'authorize' => 'https://accounts.google.com/o/oauth2/v2/auth',
            'token' => 'https://oauth2.googleapis.com/token',
            'userinfo' => 'https://openidconnect.googleapis.com/v1/userinfo',
            'scope' => 'openid profile email',
        ],
        'facebook' => [
            'authorize' => 'https://www.facebook.com/v19.0/dialog/oauth',
            'token' => 'https://graph.facebook.com/v19.0/oauth/access_token',
            'userinfo' => 'https://graph.facebook.com/v19.0/me',
            'scope' => 'email',
        ],
    ];

    /** Is this a provider we know how to talk to? */
    public function supports(string $provider): bool
    {
        return array_key_exists($provider, $this->providers);
    }

    /** True only when this provider has client credentials configured in .env. */
    public function isEnabled(string $provider): bool
    {
        return $this->supports($provider)
            && filled(config("services.$provider.client_id"))
            && filled(config("services.$provider.client_secret"));
    }

    /**
     * The provider's consent-screen URL to redirect the user to. The `state`
     * value is an anti-CSRF token the caller stores in the session and
     * re-checks on the callback.
     */
    public function authorizeUrl(string $provider, string $state): string
    {
        $config = $this->providers[$provider];

        $params = [
            'client_id' => config("services.$provider.client_id"),
            'redirect_uri' => $this->redirectUri($provider),
            'response_type' => 'code',
            'scope' => $config['scope'],
            'state' => $state,
        ];

        // Google-specific hints: keep the token short-lived (we don't store it)
        // and let the user pick which account to use.
        if ($provider === 'google') {
            $params['access_type'] = 'online';
            $params['prompt'] = 'select_account';
        }

        return $config['authorize'].'?'.http_build_query($params);
    }

    /**
     * Exchange an authorization code for the signed-in user's basic profile.
     *
     * @return array{id:string,name:?string,email:?string,avatar:?string,email_verified:bool}
     *
     * @throws RuntimeException when any step with the provider fails
     */
    public function userFromCode(string $provider, string $code): array
    {
        $accessToken = $this->exchangeCodeForToken($provider, $code);

        return $this->fetchUser($provider, $accessToken);
    }

    /** The callback URL the provider will send the user back to. */
    protected function redirectUri(string $provider): string
    {
        return config("services.$provider.redirect") ?: url("/auth/$provider/callback");
    }

    /** @throws RuntimeException */
    protected function exchangeCodeForToken(string $provider, string $code): string
    {
        $config = $this->providers[$provider];

        $params = [
            'client_id' => config("services.$provider.client_id"),
            'client_secret' => config("services.$provider.client_secret"),
            'redirect_uri' => $this->redirectUri($provider),
            'code' => $code,
            'grant_type' => 'authorization_code',
        ];

        // Google expects a POST form body; Facebook accepts GET query params.
        $response = $provider === 'google'
            ? Http::asForm()->post($config['token'], $params)
            : Http::get($config['token'], $params);

        $accessToken = $response->json('access_token');

        if ($response->failed() || ! $accessToken) {
            throw new RuntimeException("Could not obtain an access token from {$provider}.");
        }

        return (string) $accessToken;
    }

    /**
     * @return array{id:string,name:?string,email:?string,avatar:?string,email_verified:bool}
     *
     * @throws RuntimeException
     */
    protected function fetchUser(string $provider, string $accessToken): array
    {
        $config = $this->providers[$provider];

        if ($provider === 'facebook') {
            $response = Http::get($config['userinfo'], [
                'fields' => 'id,name,email,picture.type(large)',
                'access_token' => $accessToken,
            ]);
        } else {
            $response = Http::withToken($accessToken)->get($config['userinfo']);
        }

        if ($response->failed()) {
            throw new RuntimeException("Could not fetch your profile from {$provider}.");
        }

        return $provider === 'facebook'
            ? $this->normalizeFacebook($response->json())
            : $this->normalizeGoogle($response->json());
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{id:string,name:?string,email:?string,avatar:?string,email_verified:bool}
     */
    protected function normalizeGoogle(array $data): array
    {
        return [
            'id' => (string) ($data['sub'] ?? ''),
            'name' => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
            'avatar' => $data['picture'] ?? null,
            'email_verified' => (bool) ($data['email_verified'] ?? false),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{id:string,name:?string,email:?string,avatar:?string,email_verified:bool}
     */
    protected function normalizeFacebook(array $data): array
    {
        return [
            'id' => (string) ($data['id'] ?? ''),
            'name' => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
            'avatar' => $data['picture']['data']['url'] ?? null,
            // Facebook only returns an email once it is confirmed on the account,
            // so the presence of an email implies it is verified.
            'email_verified' => ! empty($data['email']),
        ];
    }
}
