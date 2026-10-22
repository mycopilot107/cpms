# Social Login (Google & Facebook)

"Sign in with Google" and "Sign in with Facebook" for the Best CPMS auth pages, plus
the social-media icons in the public footer.

## How it works

A package-free OAuth 2.0 *authorization code* flow, implemented with Laravel's
HTTP client — the same no-new-dependency approach used for Razorpay billing.
There is **no Socialite** and no extra Composer package.

```
Login/Register page
  └─ "Continue with Google/Facebook"  ──▶  GET /auth/{provider}/redirect
                                              stores anti-CSRF state in session
                                              redirects to provider consent screen
Provider consent screen
  └─ user approves  ──▶  GET /auth/{provider}/callback?code=…&state=…
                            verifies state, exchanges code → access token,
                            fetches profile, then signs the user in
```

### Sign-in policy (first-time social user)

The callback resolves the profile to a user in this order:

1. **Returning social user** — matched on `provider` + `provider_id` → log in.
2. **Existing email account** — matched on `email` → the provider is *linked*
   to that account (`provider`/`provider_id`/`avatar` filled in) and the user is
   logged in. To prevent account takeover, linking only happens when the
   provider reports the email as **verified** (Google's `email_verified`;
   Facebook only returns an email when it is confirmed). If it is not verified,
   the user is asked to sign in with their password first.
3. **Brand-new visitor** — a fresh tenant is provisioned: a `Company` + an
   `owner` `User` + a 15-day trial `Subscription`, created atomically. This
   mirrors `RegisteredUserController` exactly. The company is named
   "<Name>'s Company" (editable later); the owner has no password until they set
   one.

Disabled accounts (`status !== 'active'`) are refused, exactly like password
login.

## Files

| File | Purpose |
|------|---------|
| `app/Services/SocialAuthService.php` | OAuth client: builds the authorize URL, swaps code→token, fetches + normalizes the profile. Google and Facebook endpoints/scopes live here. |
| `app/Http/Controllers/Auth/SocialAuthController.php` | `redirect()` + `callback()`; the find/link/provision logic and the tenant provisioning transaction. |
| `resources/views/auth/partials/social-buttons.blade.php` | Branded Google/Facebook buttons; included on login + register. |
| `database/migrations/2026_09_30_100000_add_social_login_to_users_table.php` | Adds `provider`, `provider_id`, `avatar`; unique `(provider, provider_id)`; makes `password` nullable. |
| `config/services.php` | `google` + `facebook` credential config (env-backed). |
| `routes/web.php` | `social.redirect` / `social.callback` in the `guest` group. |

## Routes

| Method | URI | Name |
|--------|-----|------|
| GET | `/auth/{provider}/redirect` | `social.redirect` |
| GET | `/auth/{provider}/callback` | `social.callback` |

`{provider}` is constrained to `google` or `facebook`. Both sit in the `guest`
middleware group.

## Security notes

- **Anti-CSRF state:** a random 40-char `state` is stored in the session before
  the redirect and re-checked with `hash_equals` on the callback (along with the
  provider it was issued for). A mismatch aborts sign-in.
- **Guarded columns:** `company_id`, `role`, `status`, `provider`, `provider_id`
  are never mass-assigned — they are set explicitly in `SocialAuthController`,
  consistent with the rest of the multi-tenant codebase.
- **Takeover protection:** auto-linking to an existing email account requires a
  provider-verified email (see policy above).
- **No secrets in source:** client IDs/secrets live only in `.env`. `config/services.php`
  and `.env.example` carry blank placeholders. A provider with no credentials is
  treated as disabled and its button fails gracefully with a notice.

## Configuration

1. **Create OAuth apps** and copy the client ID + secret:
   - Google: <https://console.cloud.google.com/apis/credentials> → *Create
     credentials* → *OAuth client ID* → *Web application*.
   - Facebook: <https://developers.facebook.com/apps> → create an app → add the
     *Facebook Login* product.
2. **Register the redirect URIs** in each console (must match exactly):
   - `http://localhost:8000/auth/google/callback`
   - `http://localhost:8000/auth/facebook/callback`
   (swap in your real domain in production).
3. **Fill `.env`:**
   ```
   GOOGLE_CLIENT_ID=…
   GOOGLE_CLIENT_SECRET=…
   GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"

   FACEBOOK_CLIENT_ID=…
   FACEBOOK_CLIENT_SECRET=…
   FACEBOOK_REDIRECT_URI="${APP_URL}/auth/facebook/callback"
   ```
4. **Run the migration:** `php artisan migrate` (adds the social columns and
   makes `password` nullable).
5. `php artisan config:clear` after editing `.env`.

Until credentials are set, the buttons still render; clicking one returns the
user to the login page with a "not configured yet" notice.

## Footer social icons

The public footer shows LinkedIn, X, Facebook, and Instagram icons, driven by
`config/social.php` (env-backed via `SOCIAL_*_URL`). Set your real profile URLs
in `.env`; unset variables fall back to each platform's home page, and a blank
value hides that icon. These are **marketing links only** — separate from the
OAuth login providers above.
