<?php

use App\Enums\NewsCategory;
use App\Models\News;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * A complete, valid admin news form as the frontend sends it (body as a JSON string).
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function adminNewsForm(array $overrides = []): array
{
    return [
        'title' => 'Siswa RPL Juara Lomba Web',
        'slug' => '',
        'category' => 'Prestasi',
        'excerpt' => 'Siswa kelas XII meraih juara pertama.',
        'body' => json_encode([
            'Lomba digelar selama dua hari.',
            ['heading' => 'Persiapan'],
            ['quote' => 'Kami bangga.', 'by' => 'Kepala Sekolah'],
            ['list' => ['Latihan rutin', 'Simulasi lomba']],
        ]),
        'author' => '',
        'is_published' => '1',
        'published_at' => '2026-09-22T10:15',
        ...$overrides,
    ];
}

describe('index', function () {
    it('lists drafts and scheduled news too, newest first', function () {
        actingAsAdmin();
        $draft = News::factory()->draft()->create(['published_at' => now()->subDay()]);
        $published = News::factory()->create(['published_at' => now()->subDays(2)]);

        $this->getJson('/api/admin/news')
            ->assertOk()
            ->assertJsonPath('data.*.slug', [$draft->slug, $published->slug])
            ->assertJsonPath('data.0.status', 'draft')
            ->assertJsonPath('meta.total', 2);
    });

    it('filters by status', function (string $status) {
        actingAsAdmin();
        $news = [
            'published' => News::factory()->create(),
            'scheduled' => News::factory()->scheduled()->create(),
            'draft' => News::factory()->draft()->create(),
        ];

        $this->getJson("/api/admin/news?status={$status}")
            ->assertOk()
            ->assertJsonPath('data.*.slug', [$news[$status]->slug]);
    })->with(['published', 'scheduled', 'draft']);

    it('searches titles and filters by category', function () {
        actingAsAdmin();
        $match = News::factory()->create(['title' => 'Pelepasan Siswa PKL', 'category' => NewsCategory::KegiatanSekolah]);
        News::factory()->create(['title' => 'Pelepasan Balon', 'category' => NewsCategory::Alumni]);
        News::factory()->create(['title' => 'Workshop Orang Tua', 'category' => NewsCategory::KegiatanSekolah]);

        $this->getJson('/api/admin/news?search=Pelepasan&category='.urlencode('Kegiatan Sekolah'))
            ->assertOk()
            ->assertJsonPath('data.*.slug', [$match->slug]);
    });
});

describe('store', function () {
    it('creates news with its photo and a slug generated from the title', function () {
        Storage::fake('public');
        actingAsAdmin();

        $response = $this->post('/api/admin/news', adminNewsForm([
            'image' => UploadedFile::fake()->image('juara.jpg'),
        ]));

        $response->assertCreated()->assertJsonPath('data.slug', 'siswa-rpl-juara-lomba-web');

        $news = News::query()->sole();
        expect($news->body)->toBe(json_decode(adminNewsForm()['body'], true))
            ->and($news->category)->toBe(NewsCategory::Prestasi)
            ->and($news->published_at->format('Y-m-d H:i'))->toBe('2026-09-22 10:15');
        Storage::disk('public')->assertExists($news->image);
    });

    it('adds a number to a generated slug that is already taken', function () {
        actingAsAdmin();
        News::factory()->create(['slug' => 'siswa-rpl-juara-lomba-web']);

        $this->post('/api/admin/news', adminNewsForm())
            ->assertCreated()
            ->assertJsonPath('data.slug', 'siswa-rpl-juara-lomba-web-2');
    });

    it('rejects a typed slug that is already taken', function () {
        actingAsAdmin();
        News::factory()->create(['slug' => 'berita-lama']);

        $this->post('/api/admin/news', adminNewsForm(['slug' => 'Berita Lama']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['slug' => 'Alamat (slug) sudah dipakai.']);
    });

    it('reports every required field when the form is empty', function () {
        actingAsAdmin();

        $this->postJson('/api/admin/news', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'title' => 'Judul wajib diisi.',
                'category' => 'Kategori wajib diisi.',
                'excerpt' => 'Paragraf pembuka wajib diisi.',
                'body' => 'Isi wajib diisi.',
                'is_published' => 'Status terbit wajib diisi.',
                'published_at' => 'Tanggal terbit wajib diisi.',
            ]);

        expect(News::query()->count())->toBe(0);
    });

    it('rejects an invalid body block', function () {
        actingAsAdmin();

        $this->post('/api/admin/news', adminNewsForm(['body' => json_encode(['Paragraf', ['quote' => '']])]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['body.1' => 'Blok isi ke-2: kutipan tidak boleh kosong.']);
    });

    it('rejects a file that is not an image', function () {
        actingAsAdmin();

        $this->post('/api/admin/news', adminNewsForm(['image' => UploadedFile::fake()->create('dokumen.pdf', 10, 'application/pdf')]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['image' => 'Foto harus berupa gambar.']);
    });
});

describe('update', function () {
    it('replaces the photo and deletes the old file', function () {
        Storage::fake('public');
        actingAsAdmin();
        $oldImage = UploadedFile::fake()->image('lama.jpg')->store('news', 'public');
        $news = News::factory()->create(['image' => $oldImage]);

        $this->put("/api/admin/news/{$news->id}", adminNewsForm([
            'slug' => $news->slug,
            'image' => UploadedFile::fake()->image('baru.jpg'),
        ]))->assertOk();

        Storage::disk('public')->assertMissing($oldImage);
        Storage::disk('public')->assertExists($news->refresh()->image);
    });

    it('removes the photo when asked', function () {
        Storage::fake('public');
        actingAsAdmin();
        $oldImage = UploadedFile::fake()->image('lama.jpg')->store('news', 'public');
        $news = News::factory()->create(['image' => $oldImage]);

        $this->put("/api/admin/news/{$news->id}", adminNewsForm(['slug' => $news->slug, 'remove_image' => '1']))
            ->assertOk()
            ->assertJsonPath('data.image', null);

        Storage::disk('public')->assertMissing($oldImage);
    });

    it('keeps its own slug without reporting it as taken', function () {
        actingAsAdmin();
        $news = News::factory()->create(['slug' => 'berita-saya']);

        $this->put("/api/admin/news/{$news->id}", adminNewsForm(['slug' => 'berita-saya', 'is_published' => '0']))
            ->assertOk()
            ->assertJsonPath('data.slug', 'berita-saya')
            ->assertJsonPath('data.status', 'draft');
    });
});

describe('destroy', function () {
    it('deletes the news and its photo file', function () {
        Storage::fake('public');
        actingAsAdmin();
        $image = UploadedFile::fake()->image('foto.jpg')->store('news', 'public');
        $news = News::factory()->create(['image' => $image]);

        $this->deleteJson("/api/admin/news/{$news->id}")->assertNoContent();

        $this->assertModelMissing($news);
        Storage::disk('public')->assertMissing($image);
    });
});
