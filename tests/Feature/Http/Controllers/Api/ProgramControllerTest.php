<?php

use App\Models\Program;

describe('index', function () {
    it('lists programs in the order set by the admin', function () {
        $second = Program::factory()->create(['sort_order' => 2]);
        $first = Program::factory()->create(['sort_order' => 1]);

        $this->getJson('/api/programs')
            ->assertOk()
            ->assertJsonPath('data.*.slug', [$first->slug, $second->slug])
            ->assertJsonMissingPath('data.0.body');
    });
});

describe('show', function () {
    it('returns the program body with the next programs, wrapping around to the first', function () {
        $programs = Program::factory()->count(5)->sequence(fn ($sequence) => ['sort_order' => $sequence->index])->create();

        $this->getJson("/api/programs/{$programs[3]->slug}")
            ->assertOk()
            ->assertJsonPath('data.slug', $programs[3]->slug)
            ->assertJsonPath('data.body', $programs[3]->body)
            ->assertJsonPath('others.*.slug', [$programs[4]->slug, $programs[0]->slug, $programs[1]->slug]);
    });

    it('returns 404 for an unknown program', function () {
        Program::factory()->create();

        $this->getJson('/api/programs/tidak-ada')->assertNotFound();
    });
});
