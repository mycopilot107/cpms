# Forgot / Reset Password

Self-service password reset for the login page, built on Laravel's framework
password broker (no extra packages). A user who forgets their password requests
a reset link by email, clicks it, and sets a new password.

## User flow

1. On the **login page** the user clicks **"Forgot your password?"**.
2. They enter their email on `/forgot-password` and submit.
3. If an account matches, Best CPMS emails a one-time reset link (valid for
   **60 minutes**).
4. The link opens `/reset-password/{token}` with the email pre-filled.
5. They choose a new password (same strength rules as registration) and are
   redirected to the login page to sign in.

## Routes

All are guest-only (a signed-in user is redirected away) and live in the guest
group in `routes/web.php`.

| Method | URI | Name | Purpose |
| --- | --- | --- | --- |
| GET | `/forgot-password` | `password.request` | Show the "enter your email" form |
| POST | `/forgot-password` | `password.email` | Email the reset link (throttled 6/min) |
| GET | `/reset-password/{token}` | `password.reset` | Show the "choose a new password" form |
| POST | `/reset-password` | `password.store` | Save the new password (throttled 6/min) |

The GET reset route is named `password.reset` on purpose: the framework's reset
notification builds its link from that exact route name.

## Files

- `app/Http/Controllers/Auth/PasswordResetLinkController.php` — request a link.
- `app/Http/Controllers/Auth/NewPasswordController.php` — set the new password.
- `app/Http/Requests/Auth/PasswordResetLinkRequest.php` — email validation + lowercasing.
- `app/Http/Requests/Auth/NewPasswordRequest.php` — token/email/password rules (`Password::defaults()`, `confirmed`).
- `resources/views/auth/forgot-password.blade.php`, `resources/views/auth/reset-password.blade.php` — the two screens.
- `resources/views/auth/login.blade.php` — the "Forgot your password?" link.
- `lang/en/passwords.php` — broker status messages (added; was missing).
- `app/Providers/AppServiceProvider.php` — brands the reset email as "Best CPMS".

No migration is needed — the `password_reset_tokens` table ships with the
default Laravel migration and is already present.

## Email / SMTP configuration

The reset email is sent with your configured mailer. Everything is read from
`.env`; there are **no credentials in source code**.

**Local testing (no SMTP needed).** With the default `MAIL_MAILER=log`, the
email is written to `storage/logs/laravel.log` instead of being sent — open that
file and copy the reset link to test the whole flow.

**Sending real email.** Set these in `.env` (a commented example is included in
`.env` and `.env.example`):

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.yourprovider.com
MAIL_PORT=587
MAIL_USERNAME=your-smtp-username
MAIL_PASSWORD=your-smtp-password
MAIL_SCHEME=smtp            # use "smtps" for implicit TLS on port 465
MAIL_FROM_ADDRESS="no-reply@bestcpms.com"
MAIL_FROM_NAME="${APP_NAME}"
```

After editing `.env`, run `php artisan config:clear` (and re-cache in production
with `php artisan config:cache`). The reset link uses `APP_URL`, so make sure
that is set to your real domain (`https://bestcpms.com`) in production — the link
in the email is only as correct as `APP_URL`.

## Security notes

- **No account enumeration.** `/forgot-password` always shows the same neutral
  confirmation whether or not the email is registered, so it can't be used to
  discover which addresses have accounts. Both POST endpoints are also
  rate-limited (6/min per IP) on top of the broker's own per-user throttle.
- **Short-lived, single-use tokens.** Reset tokens expire after 60 minutes
  (`config/auth.php` → `passwords.users.expire`) and are consumed on use.
- **Sessions invalidated.** Resetting the password issues a fresh
  `remember_token`, so any "remember me" sessions on other devices stop working.
- **No double-hashing.** The new password is stored via the model's `hashed`
  cast, which skips values that are already hashed.

## Social-login users

A user who signed up with Google/Facebook and never set a password can use this
flow to **add** a password to their account (the `password` column is nullable).
Afterwards they can sign in with either method.
