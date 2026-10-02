<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FacilityResource;
use App\Models\Facility;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FacilityController extends Controller
{
    /**
     * All facilities with their photos, in the order set by the admin.
     * The frontend splits them into practice rooms and support facilities.
     */
    public function index(): AnonymousResourceCollection
    {
        return FacilityResource::collection(Facility::query()->ordered()->with('images')->get());
    }
}
