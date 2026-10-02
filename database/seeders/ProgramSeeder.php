<?php

namespace Database\Seeders;

use App\Models\Program;
use Database\Seeders\Concerns\ReadsSeedContent;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    use ReadsSeedContent;

    /**
     * Seeds the original featured programs in their homepage order. Skipped when programs already exist.
     */
    public function run(): void
    {
        if (Program::query()->exists()) {
            $this->command?->warn('Programs already exist, skipping.');

            return;
        }

        foreach ($this->readContent('programs') as $position => $program) {
            Program::query()->create([
                ...$program,
                'image' => $this->storeImage($program['image'], 'programs'),
                'sort_order' => $position,
            ]);
        }
    }
}
