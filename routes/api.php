<?php

use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\CarController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\SettingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Car Rental V1
|--------------------------------------------------------------------------
|
| All public REST API endpoints are registered here.
| No authentication is required for V1 public routes.
| Controllers will be added here as each domain is implemented.
|
*/

Route::middleware(['throttle:api'])->group(function () {
    Route::get('/cars', [CarController::class,      'index']);
    Route::get('/cars/{slug}', [CarController::class,      'show']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/faqs', [FaqController::class,      'index']);
    Route::get('/banners', [BannerController::class,   'index']);
    Route::get('/settings', [SettingController::class,  'index']);
    Route::get('/settings/contact', [SettingController::class,  'contact']);
});
