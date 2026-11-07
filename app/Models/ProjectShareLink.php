<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * A no-login link to one project's weekly update, for the client.
 */
class ProjectShareLink extends Model
{
    use BelongsToCompany;

    /**
     * company_id, project_id, created_by and the token are set by the server.
     *
     * @var list<string>
     */
    protected $fillable = [
        'week_start',
        'week_end',
        'message',
        'include_photos',
        'include_labour',
        'include_notes',
        'expires_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'token' => 'encrypted',
            'week_start' => 'date',
            'week_end' => 'date',
            'include_photos' => 'boolean',
            'include_labour' => 'boolean',
            'include_notes' => 'boolean',
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
            'last_viewed_at' => 'datetime',
        ];
    }

    public static function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    /** A new link with a fresh random token (not saved). */
    public static function generate(Project $project, User $creator): self
    {
        $token = Str::random(40);

        $link = new self;
        $link->forceFill([
            'company_id' => $project->company_id,
            'project_id' => $project->id,
            'created_by' => $creator->id,
            'token' => $token,
            'token_hash' => self::hashToken($token),
        ]);

        return $link;
    }

    /** Look up a usable link from the URL, outside any signed-in tenant. */
    public static function findUsable(string $token): ?self
    {
        $link = static::query()
            ->withoutGlobalScopes()
            ->where('token_hash', self::hashToken($token))
            ->first();

        return $link?->isUsable() ? $link : null;
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isUsable(): bool
    {
        return $this->revoked_at === null
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }

    public function url(): string
    {
        return route('weekly-update.show', $this->token);
    }

    /** "3–9 Nov 2026" */
    public function weekLabel(): string
    {
        $start = $this->week_start;
        $end = $this->week_end;

        if ($start->month === $end->month) {
            return $start->format('j').'–'.$end->format('j M Y');
        }

        return $start->format('j M').' – '.$end->format('j M Y');
    }
}
