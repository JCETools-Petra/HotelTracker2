<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\HotelRoomController as AdminHotelRoomController;
use App\Http\Controllers\Admin\IncomeController as AdminIncomeController;
use App\Http\Controllers\Admin\InventoryController as AdminInventoryController;
use App\Http\Controllers\Admin\MiceCategoryController;
use App\Http\Controllers\Admin\PricePackageController;
use App\Http\Controllers\Admin\PricingRuleController;
use App\Http\Controllers\Admin\PropertyController as AdminPropertyController;
use App\Http\Controllers\Admin\RevenueTargetController;
use App\Http\Controllers\Admin\RoomController as AdminRoomController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TargetController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Ecommerce\BarDisplayController;
use App\Http\Controllers\Ecommerce\DashboardController as EcommerceDashboardController;
use App\Http\Controllers\Ecommerce\ReservationController as EcommerceReservationController;
use App\Http\Controllers\Housekeeping\InventoryController;
use App\Http\Controllers\Inventory\DashboardController as InventoryDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PropertyIncomeController;
use App\Http\Controllers\Sales\BookingController;
use App\Http\Controllers\Sales\CalendarController as SalesCalendarController;
use App\Http\Controllers\Sales\DashboardController as SalesDashboardController;
use App\Http\Controllers\Sales\DocumentController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        if (in_array($user->role, ['admin', 'owner', 'pengurus'])) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->role === 'pengguna_properti') {
            return redirect()->route('property.dashboard');
        } elseif ($user->role === 'sales') {
            return redirect()->route('sales.dashboard');
        } elseif ($user->role === 'online_ecommerce') {
            return redirect()->route('ecommerce.dashboard');
        } elseif ($user->role === 'inventaris') {
            return redirect()->route('inventory.dashboard');
        } elseif ($user->role === 'hk') {
            return redirect()->route('housekeeping.inventory.index');
        }
        return redirect()->route('dashboard');
    }
    return view('auth.login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/dashboard', function () {
        $user = Auth::user();
        if (in_array($user->role, ['admin', 'owner', 'pengurus'])) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->role === 'pengguna_properti') {
            return redirect()->route('property.dashboard');
        } elseif ($user->role === 'online_ecommerce') {
            return redirect()->route('ecommerce.dashboard');
        } elseif ($user->role === 'sales') {
            return redirect()->route('sales.dashboard');
        } elseif ($user->role === 'inventaris') { // <-- INI BAGIAN YANG DIPERBAIKI
            return redirect()->route('inventory.dashboard'); // <-- INI BAGIAN YANG DIPERBAIKI
        } elseif ($user->role === 'hk') {
            return redirect()->route('housekeeping.inventory.index');
        }
        abort(403, 'Tidak ada dashboard yang sesuai untuk peran Anda.');
    })->name('dashboard');
});

require __DIR__ . '/auth.php';

// Grup Admin - Laporan
Route::prefix('admin')->middleware(['auth', 'verified', 'role:admin,owner,pengurus'])->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/export-excel', [AdminDashboardController::class, 'exportExcel'])->name('dashboard.exportExcel');
    Route::get('/kpi-analysis', [AdminDashboardController::class, 'kpiAnalysis'])->name('kpi.analysis');
    Route::get('/kpi-analysis/export', [AdminDashboardController::class, 'exportKpiAnalysis'])->name('kpi.analysis.export');
    Route::get('/properties/compare', [AdminPropertyController::class, 'showComparisonForm'])->name('properties.compare_page');
    Route::get('/properties/compare/results', [AdminPropertyController::class, 'showComparisonResults'])->name('properties.compare.results');
    Route::get('properties/{property}', [AdminPropertyController::class, 'show'])->name('properties.show');
    Route::get('/inventories/export', [\App\Http\Controllers\Admin\InventoryController::class, 'exportExcel'])->name('inventories.export');
});

// Grup Admin - Manajemen
Route::prefix('admin')->middleware(['auth', 'verified', 'role:admin,owner'])->name('admin.')->group(function () {
    Route::get('/sales-analytics', [AdminDashboardController::class, 'salesAnalytics'])->name('sales.analytics');
    Route::get('/calendar/unified', [AdminDashboardController::class, 'unifiedCalendar'])->name('calendar.unified');
    Route::get('/calendar/unified/events', [AdminDashboardController::class, 'getUnifiedCalendarEvents'])->name('calendar.unified.events');
    Route::resource('users', AdminUserController::class);
    Route::get('/users-trashed', [AdminUserController::class, 'trashed'])->name('users.trashed');
    Route::post('/users/{user}/restore', [AdminUserController::class, 'restore'])->name('users.restore');
    Route::delete('/users/{user}/force-delete', [AdminUserController::class, 'forceDelete'])->name('users.force-delete');
    Route::resource('properties', AdminPropertyController::class)->except(['show']);
    Route::resource('categories', App\Http\Controllers\Admin\CategoryController::class);
    Route::get('inventories/select', [AdminInventoryController::class, 'select'])->name('inventories.select');
    Route::resource('inventories', AdminInventoryController::class);
    Route::resource('revenue-targets', RevenueTargetController::class);
    Route::resource('targets', TargetController::class);
    Route::resource('mice-categories', MiceCategoryController::class);
    Route::resource('price-packages', PricePackageController::class);
    Route::get('/reports/amenities', [AdminInventoryController::class, 'report'])->name('reports.amenities');
    Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('activity_log.index');
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'store'])->name('settings.store');
    Route::resource('properties.rooms', AdminRoomController::class)->shallow();
    Route::resource('properties.hotel_rooms', AdminHotelRoomController::class)->shallow()->names('properties.hotel-rooms');
    Route::resource('properties.incomes', AdminIncomeController::class)->shallow();
    Route::post('properties/{property}/occupancy', [AdminPropertyController::class, 'updateOccupancy'])->name('properties.occupancy.update');
    Route::prefix('properties/{property}/pricing-rule')->name('pricing-rules.')->group(function () {
        Route::get('/', [PricingRuleController::class, 'index'])->name('index');
        Route::post('/store-room-type', [PricingRuleController::class, 'storeRoomType'])->name('room-type.store');
        Route::put('/update-pricing-rule/{roomType}', [PricingRuleController::class, 'updatePricingRule'])->name('rule.update');
        Route::delete('/destroy-room-type/{roomType}', [PricingRuleController::class, 'destroyRoomType'])->name('room-type.destroy');
        Route::put('/update-property-bars', [PricingRuleController::class, 'updatePropertyBars'])->name('property-bars.update');
    });
    Route::get('/inventories/export', [\App\Http\Controllers\Admin\InventoryController::class, 'exportExcel'])->name('inventories.export');
    Route::post('/settings/test-msq-email', [\App\Http\Controllers\Admin\SettingController::class, 'sendTestMsqEmail'])->name('settings.testMsqEmail');
});

// Grup Sales
Route::prefix('sales')->middleware(['auth', 'verified', 'role:admin,sales,owner'])->name('sales.')->group(function () {
    Route::get('/dashboard', [SalesDashboardController::class, 'index'])->name('dashboard');
    Route::resource('bookings', BookingController::class);
    Route::get('/bookings/{booking}/download-beo', [BookingController::class, 'downloadBeo'])->name('bookings.download_beo');
    Route::get('/bookings/{booking}/show-beo', [BookingController::class, 'showBeo'])->name('bookings.show_beo');
    Route::get('/bookings/{booking}/beo', [BookingController::class, 'beo'])->name('bookings.beo');
    Route::post('/bookings/{booking}/beo', [BookingController::class, 'storeBeo'])->name('bookings.storeBeo');
    Route::get('/bookings/{booking}/beo/show', [BookingController::class, 'showBeo'])->name('bookings.showBeo');
    Route::get('/bookings/{booking}/beo/print', [BookingController::class, 'printBeo'])->name('bookings.printBeo');
    Route::get('/bookings/{booking}/quotation', [DocumentController::class, 'generateQuotation'])->name('documents.quotation');
    Route::get('/bookings/{booking}/invoice', [DocumentController::class, 'generateInvoice'])->name('documents.invoice');
    Route::get('/bookings/{booking}/beo/pdf', [DocumentController::class, 'generateBeo'])->name('documents.beo');
    Route::get('/calendar', [SalesCalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/events', [SalesCalendarController::class, 'events'])->name('calendar.events');
});

// Grup Housekeeping
Route::prefix('housekeeping')->middleware(['auth', 'verified', 'role:hk,owner'])->name('housekeeping.')->group(function () {
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('/inventory/select-room', [InventoryController::class, 'selectRoom'])->name('inventory.select-room');
    Route::get('/inventory/assign/{room}', [InventoryController::class, 'assign'])->name('inventory.assign');
    Route::post('/inventory/update/{room}', [InventoryController::class, 'updateInventory'])->name('inventory.update');
    Route::get('/history', [InventoryController::class, 'history'])->name('inventory.history');
});

// Grup Pengguna Properti
Route::prefix('property')->middleware(['auth', 'verified', 'role:pengguna_properti,owner'])->name('property.')->group(function () {
    Route::get('/dashboard', [PropertyIncomeController::class, 'dashboard'])->name('dashboard');
    Route::get('/calendar', [PropertyIncomeController::class, 'calendar'])->name('calendar.index');
    Route::get('/calendar-data', [PropertyIncomeController::class, 'getCalendarData'])->name('calendar.data');
    Route::get('properties/{property}/room-types', [EcommerceReservationController::class, 'getRoomTypesByProperty'])->name('properties.room-types');
    Route::get('room-types/{roomType}/active-price', [EcommerceReservationController::class, 'getActiveBarPrice'])->name('room-types.active-price');
    Route::resource('reservations', EcommerceReservationController::class);
    Route::get('/income', [PropertyIncomeController::class, 'index'])->name('income.index');
    Route::get('/income/create', [PropertyIncomeController::class, 'create'])->name('income.create');
    Route::post('/income', [PropertyIncomeController::class, 'store'])->name('income.store');
    Route::get('/income/{income}/edit', [PropertyIncomeController::class, 'edit'])->name('income.edit');
    Route::put('/income/{income}', [PropertyIncomeController::class, 'update'])->name('income.update');
    Route::delete('/income/{income}', [PropertyIncomeController::class, 'destroy'])->name('income.destroy');
    Route::post('/occupancy/update', [PropertyIncomeController::class, 'updateOccupancy'])->name('occupancy.update');
});

// Grup E-commerce
Route::prefix('ecommerce')->middleware(['auth', 'verified', 'role:online_ecommerce'])->name('ecommerce.')->group(function () {
    Route::get('/dashboard', [EcommerceDashboardController::class, 'index'])->name('dashboard');
    Route::resource('reservations', EcommerceReservationController::class);
    Route::get('/bar-prices', [BarDisplayController::class, 'index'])->name('bar-prices.index');
});

// Grup Inventaris
Route::prefix('inventory')->middleware(['auth', 'verified', 'role:inventaris,owner,admin'])->name('inventory.')->group(function () {
    Route::get('/', function() {
        return redirect()->route('inventory.dashboard');
    });
    
    // Rute utama (dashboard) sekarang mengarah ke ItemController
    Route::get('/dashboard', [\App\Http\Controllers\Inventory\ItemController::class, 'index'])->name('dashboard');

    // Resource routes untuk Item dan Kategori
    Route::resource('items', \App\Http\Controllers\Inventory\ItemController::class)->except(['show', 'index']);
    Route::resource('categories', \App\Http\Controllers\Inventory\CategoryController::class)->except(['show']);
});