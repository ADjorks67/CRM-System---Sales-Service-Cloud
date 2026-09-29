<?php

namespace App\Models;

use App\Enums\SharingAccessLevel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['object_type', 'access_level'])]
class SharingDefault extends Model
{
    protected function casts(): array
    {
        return [
            'access_level' => SharingAccessLevel::class,
        ];
    }

    public static function accessLevelFor(string $objectType): SharingAccessLevel
    {
        $default = static::query()->where('object_type', $objectType)->first();

        return $default?->access_level ?? SharingAccessLevel::Private;
    }
}
