<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\AdminController;

// Storefront Routes (Client Website, Dedicated Gallery, and 12-Step Cake Builder)
Route::get('/', [StorefrontController::class, 'index'])->name('storefront.index');
Route::get('/gallery', [StorefrontController::class, 'gallery'])->name('storefront.gallery');

// Bakesy Mobile Admin Dashboard
Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.dashboard');
