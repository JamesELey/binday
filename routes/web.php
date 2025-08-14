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

// Authentication routes (accessible to guests)
Route::get('/login', [\App\Http\Controllers\Auth\AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [\App\Http\Controllers\Auth\AuthController::class, 'login']);
Route::get('/register', [\App\Http\Controllers\Auth\AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [\App\Http\Controllers\Auth\AuthController::class, 'register']);

// Logout route (for authenticated users only)
Route::post('/logout', [\App\Http\Controllers\Auth\AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Redirect root to login for guests, or dashboard for authenticated users
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('bins.index');
    }
    return redirect()->route('login');
});

// All other routes require authentication
Route::middleware(['auth'])->group(function () {
    // Homepage and main navigation
    Route::get('/home', [BinScheduleController::class, 'index'])->name('bins.index');
    Route::get('/bins/map', [BinScheduleController::class, 'map'])->name('bins.map');
    Route::get('/bins/map-by-date', [BinScheduleController::class, 'mapByDate'])->name('bins.mapByDate');
    
    // API endpoints
    Route::get('/api/bins', [BinScheduleController::class, 'apiAll'])->name('api.bins');
    Route::get('/api/areas', [AllowedAreaController::class, 'apiList'])->name('api.areas');
    Route::get('/api/lookup', [BinScheduleController::class, 'lookup'])->name('api.lookup');
    
    // Bin management routes
    Route::get('/bins/create', [BinScheduleController::class, 'create'])->name('bins.create');
    Route::post('/bins', [BinScheduleController::class, 'store'])->name('bins.store');
    Route::delete('/bins/{bin}', [BinScheduleController::class, 'destroy'])->name('bins.destroy');
    Route::get('/bins/{bin}/edit', [BinScheduleController::class, 'edit'])->name('bins.edit');
    Route::put('/bins/{bin}', [BinScheduleController::class, 'update'])->name('bins.update');
    Route::post('/bins/geocode-all', [BinScheduleController::class, 'geocodeAll'])->name('bins.geocodeAll');
    Route::get('/bins/geocode-all', [BinScheduleController::class, 'geocodeAll'])->name('bins.geocodeAll.get');
    
    // Collection Management Routes
    Route::get('/collections', [CollectionController::class, 'index'])->name('collections.index');
    Route::get('/collections/create', [CollectionController::class, 'create'])->name('collections.create');
    Route::post('/collections', [CollectionController::class, 'store'])->name('collections.store');
    Route::get('/collections/manage', [CollectionController::class, 'manage'])->name('collections.manage');
    Route::get('/collections/{id}/edit', [CollectionController::class, 'edit'])->name('collections.edit');
    Route::put('/collections/{id}', [CollectionController::class, 'update'])->name('collections.update');
    Route::delete('/collections/{id}', [CollectionController::class, 'destroy'])->name('collections.destroy');
    
    // Enquiry
    Route::get('/enquiry', [EnquiryController::class, 'create'])->name('enquiry.create');
    Route::post('/enquiry', [EnquiryController::class, 'store'])->name('enquiry.store');
});

// Role-based dashboards (requires authentication)
Route::middleware(['role'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Auth\AuthController::class, 'dashboard'])->name('dashboard');
    Route::get('/admin/dashboard', [\App\Http\Controllers\Auth\AuthController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/worker/dashboard', [\App\Http\Controllers\Auth\AuthController::class, 'dashboard'])->name('worker.dashboard');
    Route::get('/customer/dashboard', [\App\Http\Controllers\Auth\AuthController::class, 'dashboard'])->name('customer.dashboard');
});

// Areas management (Admin/Worker only)
Route::middleware(['role:admin,worker'])->group(function () {
    Route::get('/areas', [AllowedAreaController::class, 'index'])->name('areas.index');
    Route::get('/areas/create-map', [AllowedAreaController::class, 'createMap'])->name('areas.createMap');
    Route::post('/areas', [AllowedAreaController::class, 'store'])->name('areas.store');
    Route::get('/areas/{area}/edit', [AllowedAreaController::class, 'edit'])->name('areas.edit');
    Route::put('/areas/{area}', [AllowedAreaController::class, 'update'])->name('areas.update');
    Route::delete('/areas/{area}', [AllowedAreaController::class, 'destroy'])->name('areas.destroy');
    Route::post('/api/geocode', [AllowedAreaController::class, 'geocodePostcode'])->name('api.geocode');
    
    // Bin type management for areas
    Route::get('/areas/{area}/bin-types', [AllowedAreaController::class, 'manageBinTypes'])->name('areas.manageBinTypes');
    Route::put('/areas/{area}/bin-types', [AllowedAreaController::class, 'updateBinTypes'])->name('areas.updateBinTypes');
    Route::post('/areas/{area}/bin-types/add', [AllowedAreaController::class, 'addBinType'])->name('areas.addBinType');
    Route::delete('/areas/{area}/bin-types/remove', [AllowedAreaController::class, 'removeBinType'])->name('areas.removeBinType');
    
    // Postcode to polygon conversion
    Route::post('/areas/{area}/convert-to-polygon', [AllowedAreaController::class, 'convertToPolygon'])->name('areas.convertToPolygon');
    Route::get('/areas/{area}/polygon-preview', [AllowedAreaController::class, 'getPolygonPreview'])->name('areas.polygonPreview');
    Route::post('/areas/convert-all-postcodes', [AllowedAreaController::class, 'convertAllPostcodeAreas'])->name('areas.convertAllPostcodes');
});

// Admin-only Routes
Route::middleware(['role:admin'])->group(function () {
    // User Management Routes
    Route::get('/admin/users', [\App\Http\Controllers\Admin\AdminController::class, 'manageUsers'])->name('admin.users.index');
    Route::get('/admin/users/create', [\App\Http\Controllers\Admin\AdminController::class, 'createUser'])->name('admin.users.create');
    Route::post('/admin/users', [\App\Http\Controllers\Admin\AdminController::class, 'storeUser'])->name('admin.users.store');
    Route::get('/admin/users/{user}/edit', [\App\Http\Controllers\Admin\AdminController::class, 'editUser'])->name('admin.users.edit');
    Route::put('/admin/users/{user}', [\App\Http\Controllers\Admin\AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::patch('/admin/users/{user}/toggle', [\App\Http\Controllers\Admin\AdminController::class, 'toggleUserStatus'])->name('admin.users.toggle');
    Route::delete('/admin/users/{user}', [\App\Http\Controllers\Admin\AdminController::class, 'destroyUser'])->name('admin.users.destroy');

    // Worker Assignment Routes
    Route::get('/admin/workers/assign', [\App\Http\Controllers\Admin\AdminController::class, 'assignWorkers'])->name('admin.workers.assign');
    Route::post('/admin/workers/update', [\App\Http\Controllers\Admin\AdminController::class, 'updateWorkerAssignments'])->name('admin.workers.update');
    Route::get('/admin/workers/{worker}/assignments', [\App\Http\Controllers\Admin\AdminController::class, 'getWorkerAssignments'])->name('admin.workers.assignments');
    Route::post('/admin/workers/bulk-update', [\App\Http\Controllers\Admin\AdminController::class, 'bulkUpdateWorkerAssignments'])->name('admin.workers.bulk-update');

    // Data Seeding Routes
    Route::get('/admin/seed', [DataSeederController::class, 'index'])->name('seed.index');
    Route::post('/admin/seed/all', [DataSeederController::class, 'seedAll'])->name('seed.all');
    Route::delete('/admin/seed/delete', [DataSeederController::class, 'deleteAll'])->name('seed.delete');
    Route::post('/admin/seed/eccleshall', [DataSeederController::class, 'seedEccleshallAreaOnly'])->name('seed.eccleshall');
    Route::post('/admin/seed/collections', [DataSeederController::class, 'seedCollectionsOnly'])->name('seed.collections');
    Route::get('/admin/seed/status', [DataSeederController::class, 'getDataSummary'])->name('seed.status');
});



// Legacy admin routes (keeping for compatibility)
Route::get('/admin/login', [AuthController::class, 'loginForm'])->name('admin.loginForm');
Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login');
Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

Route::middleware(['admin'])->group(function () {
    Route::get('/admin/settings', [SettingsController::class, 'edit'])->name('admin.settings');
    Route::put('/admin/settings', [SettingsController::class, 'update'])->name('admin.settings.update');
    Route::post('/admin/seed-demo', [SettingsController::class, 'seedDemo'])->name('admin.seedDemo');
    Route::post('/admin/clear-schedules', [SettingsController::class, 'clearSchedules'])->name('admin.clearSchedules');
});