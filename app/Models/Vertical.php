<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Vertical extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'is_active',
        'track_commission',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'track_commission' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Vertical $vertical) {
            if (empty($vertical->slug)) {
                $vertical->slug = Str::slug($vertical->name);
            }
        });
    }

    public function stages(): HasMany
    {
        return $this->hasMany(Stage::class)->orderBy('position');
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
