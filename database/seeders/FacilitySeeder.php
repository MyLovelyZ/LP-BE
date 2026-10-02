<?php

namespace Database\Seeders;

use App\Models\Facility;
use Database\Seeders\Concerns\ReadsSeedContent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class FacilitySeeder extends Seeder
{
    use ReadsSeedContent;

    /**
     * Seeds the original facilities with their photos, in their original order. Skipped when facilities already exist.
     */
    public function run(): void
    {
        if (Facility::query()->exists()) {
            $this->command?->warn('Facilities already exist, skipping.');

            return;
        }

        foreach ($this->readContent('facilities') as $position => $facility) {
            $model = Facility::query()->create([
                ...Arr::except($facility, 'images'),
                'sort_order' => $position,
            ]);

            foreach ($facility['images'] as $imagePosition => $image) {
                $model->images()->create([
                    'path' => $this->storeImage($image, 'facilities'),
                    'sort_order' => $imagePosition,
                ]);
            }
        }
    }
}
