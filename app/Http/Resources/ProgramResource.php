<?php

namespace App\Http\Resources;

use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Featured program card data. Keys match the `Program` type in the frontend (src/data/programs.ts).
 *
 * @mixin Program
 */
class ProgramResource extends JsonResource
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
            'slug' => $this->slug,
            'title' => $this->title,
            'description' => $this->description,
            'icon' => $this->icon,
            'image' => $this->image_url,
            'audience' => $this->audience,
            'schedule' => $this->schedule,
            'sort_order' => $this->sort_order,
        ];
    }
}
