<?php

namespace Database\Seeders;

use App\Models\News;
use Database\Seeders\Concerns\ReadsSeedContent;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    use ReadsSeedContent;

    /**
     * Seeds the original landing page news. Skipped when news already exists, so running
     * the seeders again never duplicates content or overwrites the admin's edits.
     */
    public function run(): void
    {
        if (News::query()->exists()) {
            $this->command?->warn('News already exist, skipping.');

            return;
        }

        foreach ($this->readContent('news') as $news) {
            News::query()->create([
                ...$news,
                'image' => $this->storeImage($news['image'], 'news'),
                'is_published' => true,
            ]);
        }
    }
}
