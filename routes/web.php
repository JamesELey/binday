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

// Bin type management for areas
Route::get('/areas/{area}/bin-types', [AllowedAreaController::class, 'manageBinTypes'])->name('areas.manageBinTypes');
Route::put('/areas/{area}/bin-types', [AllowedAreaController::class, 'updateBinTypes'])->name('areas.updateBinTypes');
Route::post('/areas/{area}/bin-types/add', [AllowedAreaController::class, 'addBinType'])->name('areas.addBinType');
Route::delete('/areas/{area}/bin-types/remove', [AllowedAreaController::class, 'removeBinType'])->name('areas.removeBinType');

// Postcode to polygon conversion
Route::post('/areas/{area}/convert-to-polygon', [AllowedAreaController::class, 'convertToPolygon'])->name('areas.convertToPolygon');
Route::get('/areas/{area}/polygon-preview', [AllowedAreaController::class, 'getPolygonPreview'])->name('areas.polygonPreview');
Route::post('/areas/convert-all-postcodes', [AllowedAreaController::class, 'convertAllPostcodeAreas'])->name('areas.convertAllPostcodes');

// Enquiry
Route::get('/enquiry', [EnquiryController::class, 'create'])->name('enquiry.create');
Route::post('/enquiry', [EnquiryController::class, 'store'])->name('enquiry.store');

// Admin settings
Route::get('/admin/login', [AuthController::class, 'loginForm'])->name('admin.loginForm');
Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login');
Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

Route::middleware(['admin'])->group(function () {
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

// Test route for form postcode area creation
Route::post('/test-postcode-form', function(\Illuminate\Http\Request $request) {
    \Log::info('Test postcode form submission', [
        'all_data' => $request->all(),
        'method' => $request->method(),
        'is_json' => $request->isJson(),
        'content_type' => $request->header('Content-Type')
    ]);
    
    try {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'postcodes' => 'required|string|min:2',
            'active' => 'required|in:0,1',
            'description' => 'nullable|string|max:500'
        ]);
        
        \Log::info('Validation passed', ['validated' => $validated]);
        
        return response()->json(['success' => true, 'validated' => $validated]);
        
    } catch (\Exception $e) {
        \Log::error('Validation failed', ['error' => $e->getMessage()]);
        return response()->json(['error' => $e->getMessage()], 400);
    }
});

// Test route for postcode area creation
Route::get('/test-postcode-area', function() {
    $areas = json_decode(file_get_contents(storage_path('app/allowed_areas.json')), true) ?: [];
    
    $newArea = [
        'id' => count($areas) + 1,
        'name' => 'Test Postcode Area',
        'description' => 'Test area created programmatically',
        'postcodes' => 'SW1, SW2, SW3',
        'active' => true,
        'type' => 'postcode',
        'coordinates' => null,
        'bin_types' => ['Food', 'Recycling', 'Garden'],
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $areas[] = $newArea;
    
    file_put_contents(storage_path('app/allowed_areas.json'), json_encode($areas, JSON_PRETTY_PRINT));
    
    return response()->json([
        'success' => true,
        'message' => 'Test postcode area created',
        'area' => $newArea,
        'total_areas' => count($areas)
    ]);
});
