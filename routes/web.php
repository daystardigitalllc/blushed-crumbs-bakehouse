<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\AdminController;

// Storefront Routes (Client Website, Dedicated Pages, and 12-Step Cake Builder)
Route::get('/', [StorefrontController::class, 'index'])->name('storefront.index');
Route::get('/about', [StorefrontController::class, 'about'])->name('storefront.about');
Route::get('/gallery', [StorefrontController::class, 'gallery'])->name('storefront.gallery');

// Bakesy Mobile Admin Dashboard
Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.dashboard');
Route::post('/admin/gallery', [AdminController::class, 'storeGallery'])->name('admin.gallery.store');
Route::delete('/admin/gallery/{id}', [AdminController::class, 'destroyGallery'])->name('admin.gallery.destroy');

