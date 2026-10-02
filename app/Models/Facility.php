<?php

namespace App\Models;

use App\Enums\FacilityCategory;
use Database\Factories\FacilityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $title
 * @property string $description
 * @property string $icon
 * @property FacilityCategory $category
 * @property list<string> $features
 * @property list<string> $majors
 * @property int $sort_order
 */
#[Fillable(['title', 'description', 'icon', 'category', 'features', 'majors', 'sort_order'])]
class Facility extends Model
{
    /** @use HasFactory<FacilityFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        // Deleted one by one (instead of relying on the foreign key cascade) so each photo file is removed too
        static::deleting(function (Facility $facility): void {
            $facility->images()->get()->each->delete();
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => FacilityCategory::class,
            'features' => 'array',
            'majors' => 'array',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Photos in display order; the first one is the cover.
     *
     * @return HasMany<FacilityImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(FacilityImage::class)->orderBy('sort_order')->orderBy('id');
    }

    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }
}
