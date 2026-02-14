<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CaseItem extends Model
{
    protected $fillable = [
        'case_id', 'itemable_type', 'itemable_id',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(CaseModel::class, 'case_id');
    }

    public function itemable(): MorphTo
    {
        return $this->morphTo();
    }
}
