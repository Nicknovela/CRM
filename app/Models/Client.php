<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Client extends Model
{
    protected $fillable = [
        'name',
        'company_name',
        'email',
        'phone',
        'industry',
        'website',
        'address',
        'notes',
    ];

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    public function activities(): HasManyThrough
    {
        return $this->hasManyThrough(Activity::class, Deal::class);
    }

    public function getPipelineValueAttribute(): float
    {
        return $this->deals()
            ->whereNull('deleted_at')
            ->whereHas('stage', fn ($q) => $q->where('is_won', false)->where('is_lost', false))
            ->sum('amount');
    }
}
