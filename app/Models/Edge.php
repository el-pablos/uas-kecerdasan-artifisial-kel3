<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Edge extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'type', 'from_node_id', 'to_node_id', 'confidence',
        'start_time', 'stop_time', 'description', 'raw', 'created_by',
    ];

    protected $casts = [
        'raw' => 'array',
        'confidence' => 'integer',
        'start_time' => 'datetime',
        'stop_time' => 'datetime',
    ];

    public function fromNode(): BelongsTo
    {
        return $this->belongsTo(Node::class, 'from_node_id');
    }

    public function toNode(): BelongsTo
    {
        return $this->belongsTo(Node::class, 'to_node_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Available relationship types (STIX-inspired).
     */
    public static function relationshipTypes(): array
    {
        return [
            'uses' => 'Uses',
            'targets' => 'Targets',
            'attributed-to' => 'Attributed To',
            'indicates' => 'Indicates',
            'related-to' => 'Related To',
            'sighting-of' => 'Sighting Of',
            'mitigates' => 'Mitigates',
            'located-at' => 'Located At',
            'derived-from' => 'Derived From',
            'part-of' => 'Part Of',
            'communicates-with' => 'Communicates With',
            'delivers' => 'Delivers',
            'drops' => 'Drops',
            'exploits' => 'Exploits',
            'variant-of' => 'Variant Of',
            'impersonates' => 'Impersonates',
            'sub-technique-of' => 'Sub-Technique Of',
        ];
    }
}
