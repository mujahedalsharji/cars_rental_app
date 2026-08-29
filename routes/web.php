<?php

use App\Http\Controllers\Public\AboutController;
use App\Http\Controllers\Public\BookingController;
use App\Http\Controllers\Public\CarController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\FaqController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\ServicesController;
use App\Http\Controllers\PublicMediaController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/media/{path}', PublicMediaController::class)
    ->where('path', '.*')
    ->name('media.show');
Route::get('/cars', [CarController::class, 'index'])->name('cars.index');
Route::get('/cars/{slug}', [CarController::class, 'show'])->name('cars.show');
Route::get('/services', [ServicesController::class, 'index'])->name('services');
Route::get('/about', [AboutController::class, 'index'])->name('about');
Route::get('/faq', [FaqController::class, 'index'])->name('faq.index');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');
Route::get('/booking', [BookingController::class, 'show'])->name('booking');
