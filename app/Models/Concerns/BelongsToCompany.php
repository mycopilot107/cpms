<?php

namespace App\Models\Concerns;

use App\Models\Company;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * Applied to every tenant-owned model (Project, Task, ...).
 *
 * It does two things automatically:
 *   1. Adds a global query scope so a logged-in user only ever sees rows
 *      belonging to their own company. This is the backbone of tenant
 *      isolation — even a forgotten where() clause can't leak another
 *      company's data.
 *   2. Stamps company_id on newly created rows from the current user's
 *      company, so callers never set it by hand (and can't spoof it).
 */
trait BelongsToCompany
{
    public static function bootBelongsToCompany(): void
    {
        static::addGlobalScope('company', function (Builder $builder): void {
            if (Auth::check()) {
                $model = $builder->getModel();
                $builder->where(
                    $model->getTable().'.'.'company_id',
                    Auth::user()->company_id
                );
            }
        });

        static::creating(function ($model): void {
            if (empty($model->company_id) && Auth::check()) {
                $model->company_id = Auth::user()->company_id;
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
