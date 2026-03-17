<?php

namespace App\Models;

use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deal extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'client_id',
        'vertical_id',
        'stage_id',
        'assigned_to',
        'amount',
        'currency',
        'probability',
        'commission_rate',
        'expected_close_date',
        'actual_close_date',
        'notes',
        'lost_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'commission_rate' => 'decimal:2',
            'probability' => 'integer',
            'expected_close_date' => 'date',
            'actual_close_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (Deal $deal) {
            if ($deal->isDirty('stage_id') && auth()->check()) {
                $oldStageId = $deal->getOriginal('stage_id');
                $newStageId = $deal->stage_id;

                $oldStage = Stage::find($oldStageId);
                $newStage = Stage::find($newStageId);

                $description = sprintf(
                    'Etapa cambiada de "%s" a "%s"',
                    $oldStage?->name ?? 'Desconocida',
                    $newStage?->name ?? 'Desconocida'
                );

                // Log the stage change after the model saves (via 'updated' event)
                static::updated(function (Deal $updatedDeal) use ($oldStageId, $newStageId, $description) {
                    if ($updatedDeal->id === $updatedDeal->id) {
                        ActivityLogger::log($updatedDeal, 'stage_change', $description, $oldStageId, $newStageId);
                    }
                });
            }
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function vertical(): BelongsTo
    {
        return $this->belongsTo(Vertical::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class)->latest();
    }

    public function scopeForUser($query, User $user)
    {
        if ($user->hasRole('vendedor')) {
            $query->where('assigned_to', $user->id);
        }
        return $query;
    }

    public function scopeActive($query)
    {
        return $query->whereHas('stage', fn ($q) => $q->where('is_won', false)->where('is_lost', false));
    }
}
