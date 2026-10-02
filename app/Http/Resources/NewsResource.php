<?php

namespace App\Http\Resources;

use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * News card data for lists. Keys match the `News` type in the frontend (src/data/news.ts).
 *
 * @mixin News
 */
class NewsResource extends JsonResource
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
            'category' => $this->category->value,
            'date' => $this->published_at->toIso8601String(),
            'image' => $this->image_url,
            'author' => $this->author,
            'excerpt' => $this->excerpt,
            'is_published' => $this->is_published,
            'status' => $this->status,
        ];
    }
}
