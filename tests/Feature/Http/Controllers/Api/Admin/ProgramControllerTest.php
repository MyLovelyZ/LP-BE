<?php

use App\Models\Program;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * A complete, valid admin program form as the frontend sends it (body as a JSON string).
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function adminProgramForm(array $overrides = []): array
{
    return [
        'title' => 'Kelas Industri',
        'slug' => '',
        'description' => 'Kelas yang kurikulumnya disusun bersama perusahaan mitra.',
        'icon' => 'briefcase',
        'audience' => 'Siswa kelas XI & XII terpilih',
        'schedule' => 'Sepanjang tahun ajaran',
        'body' => json_encode(['Materi disusun bersama mitra.', ['list' => ['Kelas tamu', 'Prioritas PKL']]]),
        ...$overrides,
    ];
}

describe('store', function () {
    it('creates the program at the end of the carousel with its photo', function () {
        Storage::fake('public');
        actingAsAdmin();
        Program::factory()->create(['sort_order' => 4]);

        $this->post('/api/admin/programs', adminProgramForm(['image' => UploadedFile::fake()->image('kelas.jpg')]))
            ->assertCreated()
            ->assertJsonPath('data.slug', 'kelas-industri')
            ->assertJsonPath('data.sort_order', 5);

        $program = Program::query()->where('slug', 'kelas-industri')->sole();
        expect($program->body)->toBe(['Materi disusun bersama mitra.', ['list' => ['Kelas tamu', 'Prioritas PKL']]]);
        Storage::disk('public')->assertExists($program->image);
    });

    it('reports every required field when the form is empty', function () {
        actingAsAdmin();

        $this->postJson('/api/admin/programs', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'title' => 'Judul wajib diisi.',
                'description' => 'Deskripsi wajib diisi.',
                'icon' => 'Ikon wajib diisi.',
                'audience' => 'Peserta wajib diisi.',
                'schedule' => 'Jadwal wajib diisi.',
                'body' => 'Isi wajib diisi.',
            ]);
    });
});

describe('update', function () {
    it('saves the changes without moving the program', function () {
        actingAsAdmin();
        $program = Program::factory()->create(['sort_order' => 3]);

        $this->put("/api/admin/programs/{$program->id}", adminProgramForm(['slug' => 'kelas-industri-baru']))
            ->assertOk()
            ->assertJsonPath('data.slug', 'kelas-industri-baru')
            ->assertJsonPath('data.sort_order', 3);

        expect($program->refresh()->title)->toBe('Kelas Industri');
    });
});

describe('destroy', function () {
    it('deletes the program and its photo file', function () {
        Storage::fake('public');
        actingAsAdmin();
        $image = UploadedFile::fake()->image('foto.jpg')->store('programs', 'public');
        $program = Program::factory()->create(['image' => $image]);

        $this->deleteJson("/api/admin/programs/{$program->id}")->assertNoContent();

        $this->assertModelMissing($program);
        Storage::disk('public')->assertMissing($image);
    });
});

describe('reorder', function () {
    it('saves the new carousel order', function () {
        actingAsAdmin();
        [$first, $second, $third] = Program::factory()->count(3)->sequence(fn ($sequence) => ['sort_order' => $sequence->index])->create();

        $this->postJson('/api/admin/programs/reorder', ['ids' => [$third->id, $first->id, $second->id]])
            ->assertOk()
            ->assertJsonPath('data.*.id', [$third->id, $first->id, $second->id]);

        $this->getJson('/api/programs')->assertJsonPath('data.*.id', [$third->id, $first->id, $second->id]);
    });

    it('returns 422 when a program is missing from the list', function () {
        actingAsAdmin();
        [$first, $second] = Program::factory()->count(2)->create();

        $this->postJson('/api/admin/programs/reorder', ['ids' => [$second->id]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['ids' => 'Urutan harus berisi 2 item.']);
    });
});
