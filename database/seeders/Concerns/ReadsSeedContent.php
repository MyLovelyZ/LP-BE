<?php

namespace Database\Seeders\Concerns;

use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

/**
 * The initial website content lives in database/seeders/content: one JSON file per content type
 * plus the photos it references (taken from the landing page's original static data).
 */
trait ReadsSeedContent
{
    /**
     * @return list<array<string, mixed>>
     */
    protected function readContent(string $name): array
    {
        return json_decode(file_get_contents(database_path("seeders/content/{$name}.json")), true, flags: JSON_THROW_ON_ERROR);
    }

    /**
     * Copies a photo onto the public disk and returns its stored path. Every record gets its own copy,
     * so replacing or deleting one record's photo in the admin panel never affects another record.
     */
    protected function storeImage(?string $relativePath, string $folder): ?string
    {
        if ($relativePath === null) {
            return null;
        }

        return Storage::disk('public')->putFile($folder, new File(database_path("seeders/content/images/{$relativePath}")));
    }
}
