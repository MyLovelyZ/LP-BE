<?php

use App\Enums\FacilityCategory;
use App\Models\Facility;
use App\Models\FacilityImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * A complete, valid admin facility form as the frontend sends it (lists as JSON strings).
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function adminFacilityForm(array $overrides = []): array
{
    return [
        'title' => 'Laboratorium RPL',
        'description' => 'Laboratorium komputer untuk praktik pemrograman.',
        'icon' => 'code',
        'category' => 'praktik',
        'features' => json_encode(['Komputer untuk praktik', 'Akses internet']),
        'majors' => json_encode(['RPL']),
        ...$overrides,
    ];
}

/**
 * A facility photo whose file really exists on the (faked) public disk.
 */
function storedFacilityImage(Facility $facility, int $sortOrder): FacilityImage
{
    return FacilityImage::factory()->for($facility)->create([
        'path' => UploadedFile::fake()->image("foto-{$sortOrder}.jpg")->store('facilities', 'public'),
        'sort_order' => $sortOrder,
    ]);
}

describe('store', function () {
    it('creates the facility at the end of the list with its photos in upload order', function () {
        Storage::fake('public');
        actingAsAdmin();
        Facility::factory()->create(['sort_order' => 7]);

        $response = $this->post('/api/admin/facilities', adminFacilityForm([
            'images' => [UploadedFile::fake()->image('sampul.jpg'), UploadedFile::fake()->image('kedua.jpg')],
        ]));

        $response->assertCreated()
            ->assertJsonPath('data.sort_order', 8)
            ->assertJsonPath('data.features', ['Komputer untuk praktik', 'Akses internet'])
            ->assertJsonPath('data.majors', ['RPL'])
            ->assertJsonCount(2, 'data.images');

        $facility = Facility::query()->findOrFail($response->json('data.id'));
        expect($facility->images->pluck('sort_order')->all())->toBe([0, 1]);
        $facility->images->each(fn (FacilityImage $image) => Storage::disk('public')->assertExists($image->path));
    });

    it('clears the majors of a support facility', function () {
        actingAsAdmin();

        $this->post('/api/admin/facilities', adminFacilityForm(['category' => 'penunjang']))
            ->assertCreated()
            ->assertJsonPath('data.category', 'penunjang')
            ->assertJsonPath('data.majors', []);
    });

    it('reports every required field when the form is empty', function () {
        actingAsAdmin();

        $this->postJson('/api/admin/facilities', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'title' => 'Judul wajib diisi.',
                'description' => 'Deskripsi wajib diisi.',
                'icon' => 'Ikon wajib diisi.',
                'category' => 'Kategori wajib diisi.',
                'features' => 'Daftar isi ruangan wajib diisi.',
                'majors' => 'Jurusan wajib ada.',
            ]);
    });

    it('rejects an unknown major code', function () {
        actingAsAdmin();

        $this->post('/api/admin/facilities', adminFacilityForm(['majors' => json_encode(['XYZ'])]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['majors.0' => 'Jurusan yang dipilih tidak valid.']);
    });
});

describe('update', function () {
    it('keeps the listed photos in the new order, deletes the rest, and appends new uploads', function () {
        Storage::fake('public');
        actingAsAdmin();
        $facility = Facility::factory()->practiceRoom()->create();
        $first = storedFacilityImage($facility, 0);
        $removed = storedFacilityImage($facility, 1);
        $third = storedFacilityImage($facility, 2);

        $response = $this->put("/api/admin/facilities/{$facility->id}", adminFacilityForm([
            'image_ids' => json_encode([$third->id, $first->id]),
            'images' => [UploadedFile::fake()->image('baru.jpg')],
        ]));

        $response->assertOk()->assertJsonCount(3, 'data.images');
        expect(array_slice($response->json('data.images.*.id'), 0, 2))->toBe([$third->id, $first->id]);

        $this->assertModelMissing($removed);
        Storage::disk('public')->assertMissing($removed->path);
        Storage::disk('public')->assertExists($first->path);
    });

    it('leaves the photos untouched when no photo list is sent', function () {
        Storage::fake('public');
        actingAsAdmin();
        $facility = Facility::factory()->create();
        $photo = storedFacilityImage($facility, 0);

        $this->put("/api/admin/facilities/{$facility->id}", adminFacilityForm())
            ->assertOk()
            ->assertJsonPath('data.images.*.id', [$photo->id]);
    });

    it('rejects a photo that belongs to another facility', function () {
        actingAsAdmin();
        $facility = Facility::factory()->create();
        $otherFacilityPhoto = FacilityImage::factory()->create();

        $this->put("/api/admin/facilities/{$facility->id}", adminFacilityForm(['image_ids' => json_encode([$otherFacilityPhoto->id])]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['image_ids.0' => 'Foto yang dipilih tidak valid.']);

        expect($otherFacilityPhoto->refresh()->facility_id)->not->toBe($facility->id);
    });
});

describe('destroy', function () {
    it('deletes the facility with every photo file', function () {
        Storage::fake('public');
        actingAsAdmin();
        $facility = Facility::factory()->create();
        $photos = [storedFacilityImage($facility, 0), storedFacilityImage($facility, 1)];

        $this->deleteJson("/api/admin/facilities/{$facility->id}")->assertNoContent();

        $this->assertModelMissing($facility);
        foreach ($photos as $photo) {
            $this->assertModelMissing($photo);
            Storage::disk('public')->assertMissing($photo->path);
        }
    });
});

describe('reorder', function () {
    it('saves the new display order', function () {
        actingAsAdmin();
        $practice = Facility::factory()->create(['category' => FacilityCategory::Praktik, 'sort_order' => 0]);
        $support = Facility::factory()->create(['sort_order' => 1]);

        $this->postJson('/api/admin/facilities/reorder', ['ids' => [$support->id, $practice->id]])
            ->assertOk()
            ->assertJsonPath('data.*.id', [$support->id, $practice->id]);
    });
});
