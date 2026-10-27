<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * One outreach email per address. Not tenant-scoped: this is the site
 * owner's own marketing list, not company data.
 */
class SendEmail extends Model
{
    protected $table = 'send_email';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'subject',
        'body',
        'search_query',
        'is_send',
        'sent_at',
        'error',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_send' => 'boolean',
            'sent_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
        ];
    }

    /** Waiting to go out: not sent, not failed, not unsubscribed. */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('is_send', false)
            ->whereNull('error')
            ->whereNull('unsubscribed_at');
    }

    public static function normalize(string $email): string
    {
        return strtolower(trim($email));
    }
}
