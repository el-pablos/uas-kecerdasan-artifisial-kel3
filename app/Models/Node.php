<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Node extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'type', 'name', 'description', 'confidence', 'severity',
        'first_seen', 'last_seen', 'source_ref', 'stix_id', 'raw', 'created_by',
    ];

    protected $casts = [
        'raw' => 'array',
        'confidence' => 'integer',
        'first_seen' => 'datetime',
        'last_seen' => 'datetime',
    ];

    // ===== Relationships =====

    public function outgoingEdges(): HasMany
    {
        return $this->hasMany(Edge::class, 'from_node_id');
    }

    public function incomingEdges(): HasMany
    {
        return $this->hasMany(Edge::class, 'to_node_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    // ===== Scopes =====

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeHighConfidence($query, int $min = 70)
    {
        return $query->where('confidence', '>=', $min);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%");
        });
    }

    // ===== Helpers =====

    public function getColorAttribute(): string
    {
        return match ($this->type) {
            'threat-actor' => '#ef4444',
            'malware' => '#f97316',
            'campaign' => '#eab308',
            'intrusion-set' => '#a855f7',
            'vulnerability' => '#06b6d4',
            'observable' => '#3b82f6',
            'technique' => '#8b5cf6',
            'tool' => '#64748b',
            'identity' => '#10b981',
            'indicator' => '#f59e0b',
            'sighting' => '#ec4899',
            default => '#94a3b8',
        };
    }

    public function getIconAttribute(): string
    {
        return match ($this->type) {
            'threat-actor' => 'ri-skull-line',
            'malware' => 'ri-bug-line',
            'campaign' => 'ri-flag-line',
            'intrusion-set' => 'ri-spy-line',
            'vulnerability' => 'ri-error-warning-line',
            'observable' => 'ri-eye-line',
            'technique' => 'ri-tools-line',
            'tool' => 'ri-hammer-line',
            'identity' => 'ri-user-line',
            'indicator' => 'ri-alarm-warning-line',
            'sighting' => 'ri-focus-3-line',
            default => 'ri-node-tree',
        };
    }

    public function getNeighborCount(): int
    {
        return $this->outgoingEdges()->count() + $this->incomingEdges()->count();
    }
}
