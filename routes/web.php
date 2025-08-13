<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BinScheduleController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\AllowedAreaController;
use App\Http\Controllers\DataSeederController;
use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\AuthController as PublicAuthController;

Route::get('/', [BinScheduleController::class, 'index'])->name('bins.index');
Route::get('/bins/create', [BinScheduleController::class, 'create'])->name('bins.create');
Route::post('/bins', [BinScheduleController::class, 'store'])->name('bins.store');
Route::delete('/bins/{bin}', [BinScheduleController::class, 'destroy'])->name('bins.destroy');
Route::get('/bins/{bin}/edit', [BinScheduleController::class, 'edit'])->name('bins.edit');
Route::put('/bins/{bin}', [BinScheduleController::class, 'update'])->name('bins.update');

// Address lookup API
Route::get('/api/lookup', [BinScheduleController::class, 'lookup'])->name('api.lookup');

// Map page + data
Route::get('/bins/map', [BinScheduleController::class, 'map'])->name('bins.map');
Route::get('/bins/map-by-date', [BinScheduleController::class, 'mapByDate'])->name('bins.mapByDate');
Route::get('/api/bins', [BinScheduleController::class, 'apiAll'])->name('api.bins');
Route::post('/bins/geocode-all', [BinScheduleController::class, 'geocodeAll'])->name('bins.geocodeAll');
// Convenience GET route to trigger geocoding (dev-friendly)
Route::get('/bins/geocode-all', [BinScheduleController::class, 'geocodeAll'])->name('bins.geocodeAll.get');

// Allowed areas
Route::get('/areas', [AllowedAreaController::class, 'index'])->name('areas.index');
Route::get('/areas/create-map', [AllowedAreaController::class, 'createMap'])->name('areas.createMap');
Route::post('/areas', [AllowedAreaController::class, 'store'])->name('areas.store');
Route::get('/areas/{area}/edit', [AllowedAreaController::class, 'edit'])->name('areas.edit');
Route::put('/areas/{area}', [AllowedAreaController::class, 'update'])->name('areas.update');
Route::delete('/areas/{area}', [AllowedAreaController::class, 'destroy'])->name('areas.destroy');
Route::get('/api/areas', [AllowedAreaController::class, 'apiList'])->name('api.areas');
Route::post('/api/geocode', [AllowedAreaController::class, 'geocodePostcode'])->name('api.geocode');

// Enquiry
Route::get('/enquiry', [EnquiryController::class, 'create'])->name('enquiry.create');
Route::post('/enquiry', [EnquiryController::class, 'store'])->name('enquiry.store');

// Admin settings
Route::get('/admin/login', [AuthController::class, 'loginForm'])->name('admin.loginForm');
Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login');
Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

Route::middleware([\App\Http\Middleware\AdminOnly::class])->group(function () {
    Route::get('/admin/settings', [SettingsController::class, 'edit'])->name('admin.settings');
    Route::put('/admin/settings', [SettingsController::class, 'update'])->name('admin.settings.update');
    Route::post('/admin/seed-demo', [SettingsController::class, 'seedDemo'])->name('admin.seedDemo');
    Route::post('/admin/clear-schedules', [SettingsController::class, 'clearSchedules'])->name('admin.clearSchedules');
});

// Public auth routes (default all users admin on login/register)
Route::get('/login', [PublicAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [PublicAuthController::class, 'login'])->name('login.post');
Route::get('/register', [PublicAuthController::class, 'showRegister'])->name('register');
Route::post('/register', [PublicAuthController::class, 'register'])->name('register.post');
Route::post('/logout', [PublicAuthController::class, 'logout'])->name('logout');

// Collection Management Routes
Route::get('/collections', [CollectionController::class, 'index'])->name('collections.index');
Route::get('/collections/create', [CollectionController::class, 'create'])->name('collections.create');
Route::post('/collections', [CollectionController::class, 'store'])->name('collections.store');
Route::get('/collections/manage', [CollectionController::class, 'manage'])->name('collections.manage');
Route::get('/collections/{id}/edit', [CollectionController::class, 'edit'])->name('collections.edit');
Route::put('/collections/{id}', [CollectionController::class, 'update'])->name('collections.update');
Route::delete('/collections/{id}', [CollectionController::class, 'destroy'])->name('collections.destroy');

// Data Seeding Routes
Route::get('/admin/seed', [DataSeederController::class, 'index'])->name('seed.index');
Route::post('/admin/seed/all', [DataSeederController::class, 'seedAll'])->name('seed.all');
Route::delete('/admin/seed/delete', [DataSeederController::class, 'deleteAll'])->name('seed.delete');
Route::post('/admin/seed/eccleshall', [DataSeederController::class, 'seedEccleshallAreaOnly'])->name('seed.eccleshall');
Route::post('/admin/seed/collections', [DataSeederController::class, 'seedCollectionsOnly'])->name('seed.collections');
Route::get('/admin/seed/status', [DataSeederController::class, 'getDataSummary'])->name('seed.status');
