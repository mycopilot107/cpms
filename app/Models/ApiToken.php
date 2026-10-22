<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * A hashed API token used to authenticate the mobile app.
 *
 * Only the SHA-256 hash of the token is persisted; the plaintext is shown to
 * the client exactly once, at creation. This model is intentionally NOT
 * tenant-scoped (no BelongsToCompany) — it hangs off a single user and the
 * guard resolves the user first, before any tenant scope is active.
 */
class ApiToken extends Model
{
    use HasFactory;

    /**
     * Only the human label is mass-assignable. user_id / token are set
     * explicitly by the issuer so a request body can never forge them.
     *
     * @var list<string>
     */
    protected $fillable = ['name'];

    protected $hidden = ['token'];

    protected $casts = [
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Hash a plaintext token the way it is stored in the database.
     */
    public static function hashToken(string $plain): string
    {
        return hash('sha256', $plain);
    }

    /**
     * Issue a fresh token for a user and return [model, plaintext].
     * The plaintext is only ever available here — never re-derivable.
     *
     * @return array{0: self, 1: string}
     */
    public static function issue(User $user, string $name): array
    {
        $plain = Str::random(64);

        $token = new self(['name' => $name]);
        $token->user_id = $user->id;
        $token->token = self::hashToken($plain);
        $token->expires_at = now()->addDays(30);
        $token->save();

        return [$token, $plain];
    }
}
