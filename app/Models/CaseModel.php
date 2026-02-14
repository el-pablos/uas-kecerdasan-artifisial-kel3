<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CaseModel extends Model
{
    use HasUuids;

    protected $table = 'cases';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'title', 'type', 'severity', 'status', 'description',
        'owner_id', 'due_date',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(CaseTask::class, 'case_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CaseItem::class, 'case_id');
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    // Helpers
    public function getProgressAttribute(): int
    {
        $total = $this->tasks()->count();
        if ($total === 0) return 0;
        $done = $this->tasks()->where('status', 'done')->count();
        return intval(($done / $total) * 100);
    }

    public function getSeverityColorAttribute(): string
    {
        return match ($this->severity) {
            'critical' => '#ef4444',
            'high' => '#f59e0b',
            'medium' => '#6366f1',
            'low' => '#10b981',
            default => '#94a3b8',
        };
    }
}
