<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Subscription;
use App\Models\User;
use App\Services\SocialAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * "Sign in with Google / Facebook" (OAuth 2.0).
 *
 * Sign-in policy for a first-time social user:
 *   1. Match on provider + provider_id  -> returning social user, just log in.
 *   2. Else match on email              -> LINK the provider to that existing
 *      account (only if the provider verified the email, to prevent takeover).
 *   3. Else                             -> provision a brand-new tenant
 *      (company + owner + 15-day trial), mirroring RegisteredUserController.
 *
 * Guarded columns (company_id / role / status / provider*) are always set in
 * this server-side code, never from request input.
 */
class SocialAuthController extends Controller
{
    public function __construct(protected SocialAuthService $social)
    {
    }

    /** Redirect the user to the provider's consent screen. */
    public function redirect(Request $request, string $provider): RedirectResponse
    {
        if (! $this->social->isEnabled($provider)) {
            return $this->backToLogin(ucfirst($provider).' sign-in is not configured yet.');
        }

        // Anti-CSRF for the OAuth round-trip: remember a random state (and which
        // provider it was for) in the session and re-check both on callback.
        $state = Str::random(40);
        $request->session()->put('oauth.state', $state);
        $request->session()->put('oauth.provider', $provider);

        return redirect()->away($this->social->authorizeUrl($provider, $state));
    }

    /** Handle the provider redirecting back to us with an authorization code. */
    public function callback(Request $request, string $provider): RedirectResponse
    {
        if (! $this->social->isEnabled($provider)) {
            return $this->backToLogin(ucfirst($provider).' sign-in is not configured yet.');
        }

        // The user denied consent or the provider reported an error.
        if ($request->filled('error') || ! $request->filled('code')) {
            return $this->backToLogin('We could not sign you in with '.ucfirst($provider).'. Please try again.');
        }

        // Validate the anti-CSRF state and that it matches this provider.
        $expectedState = $request->session()->pull('oauth.state');
        $expectedProvider = $request->session()->pull('oauth.provider');

        if (! $expectedState
            || ! hash_equals($expectedState, (string) $request->input('state'))
            || $expectedProvider !== $provider) {
            return $this->backToLogin('Your sign-in session expired. Please try again.');
        }

        try {
            $profile = $this->social->userFromCode($provider, (string) $request->input('code'));
        } catch (\Throwable $e) {
            report($e);

            return $this->backToLogin('We could not complete '.ucfirst($provider).' sign-in. Please try again.');
        }

        if ($profile['id'] === '' || empty($profile['email'])) {
            return $this->backToLogin(ucfirst($provider).' did not share an email address, which we need to sign you in.');
        }

        $email = strtolower(trim($profile['email']));

        $user = $this->resolveUser($provider, $profile, $email);

        if ($user === null) {
            // An account exists for this email but the provider has not verified
            // ownership — refuse to auto-link (prevents account takeover).
            return $this->backToLogin('An account with this email already exists. Please sign in with your password first to link '.ucfirst($provider).'.');
        }

        // Never let a disabled account back in (mirrors password login).
        if (! $user->isActive()) {
            return $this->backToLogin(__('auth.failed'));
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Find the user this social profile belongs to, linking or provisioning as
     * needed. Returns null when an existing email account cannot be safely
     * linked (unverified provider email).
     */
    protected function resolveUser(string $provider, array $profile, string $email): ?User
    {
        // 1) Returning social user.
        $user = User::where('provider', $provider)
            ->where('provider_id', $profile['id'])
            ->first();

        if ($user) {
            return $user;
        }

        // 2) Existing account with the same email — link the provider to it.
        $existing = User::where('email', $email)->first();

        if ($existing) {
            if (! $profile['email_verified']) {
                return null;
            }

            $existing->forceFill([
                'provider' => $provider,
                'provider_id' => $profile['id'],
                'avatar' => $existing->avatar ?: $profile['avatar'],
            ])->save();

            return $existing;
        }

        // 3) Brand-new visitor — provision a fresh tenant.
        return $this->provisionCompany($provider, $profile, $email);
    }

    /**
     * Create a company + owner user + trial subscription for a first-time
     * social sign-up, atomically, mirroring RegisteredUserController.
     */
    protected function provisionCompany(string $provider, array $profile, string $email): User
    {
        return DB::transaction(function () use ($provider, $profile, $email): User {
            $now = now();
            $trialEndsAt = now()->addDays(Company::TRIAL_DAYS);
            $name = $profile['name'] ?: Str::before($email, '@');

            $company = Company::create([
                'name' => $name."'s Company",
                'email' => $email,
                'status' => Company::STATUS_ACTIVE,
                'trial_started_at' => $now,
                'trial_ends_at' => $trialEndsAt,
            ]);

            // company_id / role / status / provider* are guarded, so set them
            // explicitly here rather than through mass assignment.
            $user = new User();
            $user->company_id = $company->id;
            $user->name = $name;
            $user->email = $email;
            $user->password = null; // social-only account, no local password
            $user->provider = $provider;
            $user->provider_id = $profile['id'];
            $user->avatar = $profile['avatar'];
            $user->role = User::ROLE_OWNER;
            $user->status = 'active';
            $user->email_verified_at = $now;
            $user->save();

            Subscription::create([
                'company_id' => $company->id,
                'plan' => Subscription::PLAN_TRIAL,
                'status' => Subscription::STATUS_TRIAL,
                'trial_starts_at' => $now,
                'trial_ends_at' => $trialEndsAt,
            ]);

            return $user;
        });
    }

    /** Bounce back to the login screen with a message shown on the email field. */
    protected function backToLogin(string $message): RedirectResponse
    {
        return redirect()->route('login')->withErrors(['email' => $message]);
    }
}
