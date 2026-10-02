<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProgramDetailResource;
use App\Http\Resources\ProgramResource;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProgramController extends Controller
{
    private const OTHERS_COUNT = 3;

    /**
     * All featured programs in the order set by the admin.
     */
    public function index(): AnonymousResourceCollection
    {
        return ProgramResource::collection(Program::query()->ordered()->get());
    }

    /**
     * One program, plus the programs that come after it (wrapping around to the first)
     * for the "Program Lainnya" section.
     */
    public function show(Request $request, string $slug): ProgramDetailResource
    {
        $programs = Program::query()->ordered()->get();
        $index = $programs->search(fn (Program $program): bool => $program->slug === $slug);

        abort_if($index === false, 404);

        $others = $programs->slice($index + 1)
            ->concat($programs->take($index))
            ->take(self::OTHERS_COUNT)
            ->values();

        return (new ProgramDetailResource($programs[$index]))->additional([
            'others' => ProgramResource::collection($others)->resolve($request),
        ]);
    }
}
