<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['category', 'value', 'label', 'sort_order', 'meta_int', 'is_active'])]
class Picklist extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'meta_int' => 'integer',
        ];
    }

    public function scopeCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category)->where('is_active', true)->orderBy('sort_order');
    }
}
