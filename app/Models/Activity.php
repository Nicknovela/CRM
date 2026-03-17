<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    protected $fillable = [
        'deal_id',
        'user_id',
        'type',
        'description',
        'old_stage_id',
        'new_stage_id',
    ];

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class)->withTrashed();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function oldStage(): BelongsTo
    {
        return $this->belongsTo(Stage::class, 'old_stage_id');
    }

    public function newStage(): BelongsTo
    {
        return $this->belongsTo(Stage::class, 'new_stage_id');
    }

    public function getTypeIconAttribute(): string
    {
        return match ($this->type) {
            'note' => '📝',
            'stage_change' => '🔄',
            'call' => '📞',
            'email' => '✉️',
            'meeting' => '📅',
            default => '📌',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'note' => 'Nota',
            'stage_change' => 'Cambio de etapa',
            'call' => 'Llamada',
            'email' => 'Correo',
            'meeting' => 'Reunión',
            default => 'Otro',
        };
    }
}
