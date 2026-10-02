<?php

use App\Http\Controllers\Api\Admin;
use App\Http\Controllers\Api\FacilityController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\ProgramController;
use Illuminate\Support\Facades\Route;

/*
| Public endpoints read by the landing page.
*/
Route::get('news', [NewsController::class, 'index'])->name('news.index');
Route::get('news/{slug}', [NewsController::class, 'show'])->name('news.show');
Route::get('programs', [ProgramController::class, 'index'])->name('programs.index');
Route::get('programs/{slug}', [ProgramController::class, 'show'])->name('programs.show');
Route::get('facilities', [FacilityController::class, 'index'])->name('facilities.index');

/*
| Admin panel endpoints, authenticated with Sanctum API tokens.
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::post('login', [Admin\AuthController::class, 'login'])
        ->middleware('throttle:admin-login')
        ->name('login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [Admin\AuthController::class, 'me'])->name('me');
        Route::post('logout', [Admin\AuthController::class, 'logout'])->name('logout');
        Route::put('profile', [Admin\ProfileController::class, 'update'])->name('profile.update');

        Route::get('dashboard', Admin\DashboardController::class)->name('dashboard');

        Route::apiResource('news', Admin\NewsController::class);

        Route::post('programs/reorder', [Admin\ProgramController::class, 'reorder'])->name('programs.reorder');
        Route::apiResource('programs', Admin\ProgramController::class);

        Route::post('facilities/reorder', [Admin\FacilityController::class, 'reorder'])->name('facilities.reorder');
        Route::apiResource('facilities', Admin\FacilityController::class);
    });
});
