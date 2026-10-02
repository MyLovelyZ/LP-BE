<?php

namespace App\Http\Resources;

use App\Models\Facility;
use App\Models\FacilityImage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Keys match the `Facility` type in the frontend (src/data/facilities.ts).
 *
 * @mixin Facility
 */
class FacilityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'icon' => $this->icon,
            'category' => $this->category->value,
            'features' => $this->features,
            'majors' => $this->majors,
            'images' => $this->whenLoaded('images', fn () => $this->images->map(fn (FacilityImage $image): array => [
                'id' => $image->id,
                'url' => $image->url,
            ])),
            'sort_order' => $this->sort_order,
        ];
    }
}
