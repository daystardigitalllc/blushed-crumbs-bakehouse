<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\AdminController;

// Storefront Routes (Client Website, Dedicated Pages, and 12-Step Cake Builder)
Route::get('/', [StorefrontController::class, 'index'])->name('storefront.index');
Route::get('/about', [StorefrontController::class, 'about'])->name('storefront.about');
Route::get('/gallery', [StorefrontController::class, 'gallery'])->name('storefront.gallery');
Route::post('/order', [StorefrontController::class, 'submitOrder'])->name('storefront.order.submit');

// Bakesy Mobile Admin Dashboard
Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.dashboard');
Route::post('/admin/gallery', [AdminController::class, 'storeGallery'])->name('admin.gallery.store');
Route::delete('/admin/gallery/{id}', [AdminController::class, 'destroyGallery'])->name('admin.gallery.destroy');
Route::post('/admin/form-builder', [AdminController::class, 'saveFormSchema'])->name('admin.form.schema.save');
Route::post('/admin/settings/booking', [AdminController::class, 'saveBookingSettings'])->name('admin.settings.booking.save');
Route::post('/admin/settings/email', [AdminController::class, 'saveEmailRouting'])->name('admin.settings.email.save');
Route::post('/admin/theme', [AdminController::class, 'saveTheme'])->name('admin.theme.save');
Route::post('/admin/content', [AdminController::class, 'saveContent'])->name('admin.content.save');
Route::post('/admin/sections', [AdminController::class, 'saveSectionSettings'])->name('admin.sections.save');
Route::post('/admin/upload-media', [AdminController::class, 'uploadMedia'])->name('admin.media.upload');


