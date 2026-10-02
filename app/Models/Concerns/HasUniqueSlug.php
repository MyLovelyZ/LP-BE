<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Generates URL slugs that are unique within the model's table by appending "-2", "-3", and so on.
 */
trait HasUniqueSlug
{
    public static function uniqueSlug(string $source, ?int $ignoreId = null): string
    {
        $baseSlug = Str::limit(Str::slug($source), 200, '') ?: 'konten';
        $slug = $baseSlug;
        $suffix = 2;

        while (static::slugExists($slug, $ignoreId)) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    protected static function slugExists(string $slug, ?int $ignoreId): bool
    {
        return static::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists();
    }
}
