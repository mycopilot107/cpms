<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A message left through the public Contact or Customer-feedback form.
 *
 * Public / un-tenanted on purpose: anonymous visitors submit these before any
 * company exists, so there is no `company_id` and no BelongsToCompany scope.
 */
class ContactMessage extends Model
{
    use HasFactory;

    public const TYPE_CONTACT = 'contact';
    public const TYPE_FEEDBACK = 'feedback';

    protected $fillable = [
        'type',
        'name',
        'email',
        'phone',
        'company',
        'subject',
        'message',
        'rating',
        'ip_address',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function isFeedback(): bool
    {
        return $this->type === self::TYPE_FEEDBACK;
    }
}
