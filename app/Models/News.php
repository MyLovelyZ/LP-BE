<?php

namespace App\Models;

use App\Enums\NewsCategory;
use App\Models\Concerns\HasPublicImage;
use App\Models\Concerns\HasUniqueSlug;
use Database\Factories\NewsFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $slug
 * @property string $title
 * @property NewsCategory $category
 * @property string $excerpt
 * @property list<string|array<string, mixed>> $body
 * @property string|null $image
 * @property string|null $author
 * @property bool $is_published
 * @property Carbon $published_at
 * @property-read string|null $image_url
 * @property-read string $status
 */
#[Fillable(['slug', 'title', 'category', 'excerpt', 'body', 'image', 'author', 'is_published', 'published_at'])]
class News extends Model
{
    /** @use HasFactory<NewsFactory> */
    use HasFactory, HasPublicImage, HasUniqueSlug;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => NewsCategory::class,
            'body' => 'array',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    /**
     * News that visitors may see: marked as published and whose publish time has arrived.
     */
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('is_published', true)->where('published_at', '<=', now());
    }

    /**
     * "draft", "scheduled" (published but dated in the future), or "published".
     *
     * @return Attribute<string, never>
     */
    protected function status(): Attribute
    {
        return Attribute::get(fn (): string => match (true) {
            ! $this->is_published => 'draft',
            $this->published_at->isFuture() => 'scheduled',
            default => 'published',
        });
    }
}
