<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\NewsResource;
use App\Models\Facility;
use App\Models\News;
use App\Models\Program;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private const RECENT_NEWS_COUNT = 5;

    /**
     * Content counts and the most recently edited news for the admin home page.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $recentNews = News::query()->latest('updated_at')->latest('id')->limit(self::RECENT_NEWS_COUNT)->get();

        return response()->json([
            'data' => [
                'news' => [
                    'total' => News::query()->count(),
                    'published' => News::query()->published()->count(),
                    'scheduled' => News::query()->where('is_published', true)->where('published_at', '>', now())->count(),
                    'draft' => News::query()->where('is_published', false)->count(),
                ],
                'programs' => Program::query()->count(),
                'facilities' => Facility::query()->count(),
                'recent_news' => NewsResource::collection($recentNews)->resolve($request),
            ],
        ]);
    }
}
