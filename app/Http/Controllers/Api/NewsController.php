<?php

namespace App\Http\Controllers\Api;

use App\Enums\NewsCategory;
use App\Http\Controllers\Controller;
use App\Http\Resources\NewsDetailResource;
use App\Http\Resources\NewsResource;
use App\Models\News;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class NewsController extends Controller
{
    private const LATEST_COUNT = 4;

    private const RELATED_COUNT = 3;

    /**
     * Published news, newest first.
     *
     * Paged with offset & limit instead of page numbers because the frontend shows the newest article
     * as a large card on the first page, so the first page holds one more item than the others.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'category' => ['nullable', Rule::enum(NewsCategory::class)],
            'offset' => ['nullable', 'integer', 'min:0'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $query = News::query()
            ->published()
            ->when($validated['category'] ?? null, fn (Builder $query, string $category) => $query->where('category', $category));

        $total = $query->count();

        $news = $query
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->offset($validated['offset'] ?? 0)
            ->limit($validated['limit'] ?? 12)
            ->get();

        return NewsResource::collection($news)->additional(['meta' => ['total' => $total]]);
    }

    /**
     * One published article, plus the newest other articles for the sidebar and
     * related articles (same category first) for the bottom of the page.
     */
    public function show(Request $request, string $slug): NewsDetailResource
    {
        $news = News::query()->published()->where('slug', $slug)->firstOrFail();

        $latest = News::query()
            ->published()
            ->whereKeyNot($news->id)
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(self::LATEST_COUNT)
            ->get();

        $related = News::query()
            ->published()
            ->whereKeyNot([$news->id, ...$latest->modelKeys()])
            ->orderByRaw('CASE WHEN category = ? THEN 0 ELSE 1 END', [$news->category->value])
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(self::RELATED_COUNT)
            ->get();

        return (new NewsDetailResource($news))->additional([
            'latest' => NewsResource::collection($latest)->resolve($request),
            'related' => NewsResource::collection($related)->resolve($request),
        ]);
    }
}
