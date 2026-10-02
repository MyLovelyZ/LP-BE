<?php

use App\Models\Facility;
use App\Models\FacilityImage;

it('lists facilities in order with their photos in order', function () {
    $second = Facility::factory()->create(['sort_order' => 2]);
    $first = Facility::factory()->practiceRoom()->create(['sort_order' => 1]);
    $cover = FacilityImage::factory()->for($first)->create(['sort_order' => 0]);
    $secondPhoto = FacilityImage::factory()->for($first)->create(['sort_order' => 1]);

    $this->getJson('/api/facilities')
        ->assertOk()
        ->assertJsonPath('data.*.id', [$first->id, $second->id])
        ->assertJsonPath('data.0.category', 'praktik')
        ->assertJsonPath('data.0.majors', ['RPL'])
        ->assertJsonPath('data.0.images.*.id', [$cover->id, $secondPhoto->id])
        ->assertJsonPath('data.0.images.0.url', $cover->url)
        ->assertJsonPath('data.1.images', []);
});
