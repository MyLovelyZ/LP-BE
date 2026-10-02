<?php

use App\Enums\NewsCategory;
use App\Models\News;

describe('index', function () {
    it('lists only published news, newest first', function () {
        $older = News::factory()->create(['published_at' => now()->subDays(2)]);
        $newer = News::factory()->create(['published_at' => now()->subDay()]);
        News::factory()->draft()->create();
        News::factory()->scheduled()->create();

        $this->getJson('/api/news')
            ->assertOk()
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('data.*.slug', [$newer->slug, $older->slug])
            ->assertJsonMissingPath('data.0.body');
    });

    it('filters by category and counts only that category', function () {
        $achievement = News::factory()->create(['category' => NewsCategory::Prestasi]);
        News::factory()->create(['category' => NewsCategory::Alumni]);

        $this->getJson('/api/news?category='.urlencode('Prestasi'))
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.*.slug', [$achievement->slug]);
    });

    it('pages with offset and limit', function () {
        $news = News::factory()->count(5)->sequence(fn ($sequence) => [
            'published_at' => now()->subDays($sequence->index + 1),
        ])->create();

        $this->getJson('/api/news?offset=1&limit=2')
            ->assertOk()
            ->assertJsonPath('meta.total', 5)
            ->assertJsonPath('data.*.slug', [$news[1]->slug, $news[2]->slug]);
    });

    it('returns 422 for an unknown category', function () {
        $this->getJson('/api/news?category=Gosip')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['category' => 'Kategori yang dipilih tidak valid.']);
    });
});

describe('show', function () {
    it('returns the article body with the newest other news and related news from the same category first', function () {
        $article = News::factory()->create(['category' => NewsCategory::Prestasi, 'published_at' => now()->subDays(10)]);
        $latest = News::factory()->count(4)->sequence(fn ($sequence) => [
            'category' => NewsCategory::Alumni,
            'published_at' => now()->subDays($sequence->index + 1),
        ])->create();
        $otherCategory = News::factory()->create(['category' => NewsCategory::Alumni, 'published_at' => now()->subDays(6)]);
        $sameCategory = News::factory()->create(['category' => NewsCategory::Prestasi, 'published_at' => now()->subDays(20)]);

        $this->getJson("/api/news/{$article->slug}")
            ->assertOk()
            ->assertJsonPath('data.slug', $article->slug)
            ->assertJsonPath('data.body', $article->body)
            ->assertJsonPath('latest.*.slug', $latest->pluck('slug')->all())
            ->assertJsonPath('related.*.slug', [$sameCategory->slug, $otherCategory->slug]);
    });

    it('returns 404 for a draft', function () {
        $draft = News::factory()->draft()->create();

        $this->getJson("/api/news/{$draft->slug}")->assertNotFound();
    });

    it('returns 404 for scheduled news before its publish time', function () {
        $scheduled = News::factory()->scheduled()->create();

        $this->getJson("/api/news/{$scheduled->slug}")->assertNotFound();
    });
});
