<?php

use App\Models\Facility;
use App\Models\News;
use App\Models\Program;

it('counts content and news by status', function () {
    actingAsAdmin();
    News::factory()->count(3)->create();
    News::factory()->scheduled()->create();
    News::factory()->count(2)->draft()->create();
    Program::factory()->count(2)->create();
    Facility::factory()->create();

    $this->getJson('/api/admin/dashboard')
        ->assertOk()
        ->assertJsonPath('data.news', ['total' => 6, 'published' => 3, 'scheduled' => 1, 'draft' => 2])
        ->assertJsonPath('data.programs', 2)
        ->assertJsonPath('data.facilities', 1)
        ->assertJsonCount(5, 'data.recent_news');
});
