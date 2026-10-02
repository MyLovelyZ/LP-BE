<?php

use App\Models\Facility;
use App\Models\FacilityImage;
use App\Models\News;
use App\Models\Program;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Storage;

it('seeds the admin and the original landing page content with photos', function () {
    Storage::fake('public');

    $this->seed(DatabaseSeeder::class);

    expect(User::query()->where('email', config('admin.email'))->exists())->toBeTrue()
        ->and(News::query()->published()->count())->toBe(14)
        ->and(Program::query()->ordered()->first()->slug)->toBe('kelas-industri')
        ->and(Facility::query()->count())->toBe(20)
        ->and(FacilityImage::query()->count())->toBe(30);

    Storage::disk('public')->assertExists(News::query()->first()->image);
    Storage::disk('public')->assertExists(FacilityImage::query()->first()->path);
});

it('does not duplicate content when seeding again', function () {
    Storage::fake('public');

    $this->seed(DatabaseSeeder::class);
    $this->seed(DatabaseSeeder::class);

    expect(User::query()->count())->toBe(1)
        ->and(News::query()->count())->toBe(14)
        ->and(Program::query()->count())->toBe(6)
        ->and(Facility::query()->count())->toBe(20);
});
