<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\AdminController;

// Storefront Route (Client Website & 12-Step Cake Builder)
Route::get('/', [StorefrontController::class, 'index'])->name('storefront.index');

// Bakesy Mobile Admin Dashboard
Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.dashboard');
