<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Tag extends Model
{
    protected $fillable = ['name', 'color'];

    public function taggables(): HasMany
    {
        return $this->hasMany(Taggable::class);
    }

    public function nodes(): MorphToMany
    {
        return $this->morphedByMany(Node::class, 'taggable');
    }
}
