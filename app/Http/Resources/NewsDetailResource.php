<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

/**
 * A full news article, including its body blocks.
 */
class NewsDetailResource extends NewsResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...parent::toArray($request),
            'body' => $this->body,
        ];
    }
}
