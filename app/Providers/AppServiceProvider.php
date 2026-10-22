<?php

namespace App\Providers;

use App\Models\ApiToken;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Stateless bearer-token guard for the mobile API ("api" guard in
        // config/auth.php). We hash the presented token and match it against
        // the api_tokens table, then return the owning user — but only if the
        // account is still active. Returning the User here means the standard
        // tenant machinery (BelongsToCompany global scope, policies) applies to
        // API requests automatically once Auth::check() is true.
        Auth::viaRequest('api-token', function (Request $request) {
            $plain = $request->bearerToken();

            if (! $plain) {
                return null;
            }

            $token = ApiToken::where('token', ApiToken::hashToken($plain))
                ->whereNull('revoked_at')
                ->where('expires_at', '>', now())
                ->first();

            if (! $token) {
                return null;
            }

            $user = $token->user;

            if (! $user || ! $user->isActive()) {
                return null;
            }

            // Best-effort "last seen" stamp; never block the request on it.
            $token->forceFill(['last_used_at' => now()])->saveQuietly();

            return $user;
        });

        // Brand the framework's built-in password-reset email as "Best CPMS"
        // without adding a custom notification class. The reset URL is built
        // from the `password.reset` route, so it follows APP_URL automatically,
        // and the expiry line reads from the password broker's own config.
        ResetPassword::toMailUsing(function (object $notifiable, string $token): MailMessage {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            $broker = config('auth.defaults.passwords');
            $expireMinutes = (int) config("auth.passwords.{$broker}.expire", 60);

            return (new MailMessage())
                ->subject('Reset your Best CPMS password')
                ->greeting('Password reset request')
                ->line('We received a request to reset the password for your Best CPMS account.')
                ->action('Reset password', $url)
                ->line("This link will expire in {$expireMinutes} minutes.")
                ->line('If you did not request a password reset, you can safely ignore this email — your password will not change.')
                ->salutation('— The Best CPMS team');
        });
    }
}
