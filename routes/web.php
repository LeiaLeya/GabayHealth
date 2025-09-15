<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\RHUController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\UserRequestController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;

// Home route - redirects to appropriate dashboard
Route::get('/', function() {
    if (session('user')) {
        $role = session('user.role');
        if ($role === 'admin') {
            return redirect()->route('admin.rhus.index');
        } elseif ($role === 'rhu') {
            return redirect()->route('schedules.index');
        } elseif ($role === 'barangay') {
            return redirect()->route('reports.index');
        }
    }
    return redirect()->route('login');
})->name('home');

// Public routes (no authentication required)
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

// Registration landing and role-specific forms
Route::get('/register', [RegisterController::class, 'landing'])->name('register.landing');
Route::get('/register/bhw', [RegisterController::class, 'showBhwForm'])->name('register.bhw');
Route::post('/register/bhw', [RegisterController::class, 'registerBhw'])->name('register.bhw.submit');
Route::get('/register/rhu', [RegisterController::class, 'showRhuForm'])->name('register.rhu');
Route::post('/register/rhu', [RegisterController::class, 'registerRhu'])->name('register.rhu.submit');

// Debug session route (public)
Route::get('/debug-session', function() {
    return response()->json([
        'session_has_user' => session('user') ? 'YES' : 'NO',
        'session_data' => session()->all(),
        'user_data' => session('user'),
        'intended_url' => session('intended_url')
    ]);
})->name('debug.session');

// Test session persistence
Route::get('/test-session-persistence', function() {
    session(['test_key' => 'test_value_' . time()]);
    return response()->json([
        'session_id' => session()->getId(),
        'test_value' => session('test_key'),
        'all_session_data' => session()->all()
    ]);
})->name('test.session.persistence');

// Test login simulation
Route::get('/test-login-simulation', function() {
    // Simulate a successful login
    session(['user' => [
        'id' => 'test_barangay_id',
        'role' => 'barangay',
        'username' => 'test_user',
        'name' => 'Test Barangay',
        'barangayId' => 'test_barangay_id'
    ]]);
    
    return response()->json([
        'message' => 'Test login simulation completed',
        'session_data' => session('user'),
        'session_id' => session()->getId()
    ]);
})->name('test.login.simulation');

// Protected routes (require authentication)
Route::middleware('auth.check')->group(function () {
    
    // Remove the auth middleware group for RHUs
    Route::get('/RHUs/approvals', [AdminController::class, 'indexApprovals'])->name('RHUs.approvals');
    Route::resource('RHUs', AdminController::class);

    // Add BHUs resource for compatibility (Kim's structure)
    Route::resource('BHUs', RHUController::class);

    Route::get('/events', [EventController::class, 'index'])->name('events.index');
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('/events/store', [EventController::class, 'store'])->name('events.store');
    Route::get('/events/{id}', [EventController::class, 'show'])->name('events.show');
    Route::get('/events/{id}/edit', [EventController::class, 'edit'])->name('events.edit');
    Route::put('/events/{id}', [EventController::class, 'update'])->name('events.update');
    Route::get('/events/{id}/export-pdf', [EventController::class, 'exportPdf'])->name('events.exportPdf');

    // Inventory routes
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/add-batch', [InventoryController::class, 'showAddBatch'])->name('inventory.add-batch');
    Route::get('/inventory/{id}/sort', [InventoryController::class, 'showSorted'])->name('inventory.show.sorted');
    Route::get('/inventory/residents/search', [InventoryController::class, 'searchResidents'])->name('inventory.residents.search');
    Route::get('/inventory/{id}/release-history', [InventoryController::class, 'showReleaseHistory'])->name('inventory.release-history');
    Route::get('/inventory/{id}', [InventoryController::class, 'show'])->name('inventory.show');
    Route::get('/inventory/{parentId}/batches/{batchId}/history', [InventoryController::class, 'showDistributionHistory'])->name('inventory.batches.history');
    Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
    Route::post('/inventory/batches', [InventoryController::class, 'storeBatch'])->name('inventory.batches.store');
    Route::put('/inventory/{id}', [InventoryController::class, 'update'])->name('inventory.update');
    Route::put('/inventory/{parentId}/batches/{batchId}/distribute', [InventoryController::class, 'distributeBatch'])->name('inventory.batches.distribute');
    Route::put('/inventory/{parentId}/batches/{batchId}', [InventoryController::class, 'updateBatch'])->name('inventory.batches.update');
    Route::put('/inventory/{parentId}/release', [InventoryController::class, 'releaseMedicine'])->name('inventory.release');
    Route::delete('/inventory/{id}', [InventoryController::class, 'destroy'])->name('inventory.destroy');
    Route::delete('/inventory/{parentId}/batches/{batchId}', [InventoryController::class, 'destroyBatch'])->name('inventory.batches.destroy');

    // Personnel routes
    Route::get('/personnel', [PersonnelController::class, 'index'])->name('personnel.index');
    Route::post('/personnel', [PersonnelController::class, 'store'])->name('personnel.store');
    Route::put('/personnel/{id}', [PersonnelController::class, 'update'])->name('personnel.update');
    Route::delete('/personnel/{id}', [PersonnelController::class, 'destroy'])->name('personnel.destroy');

    // New feature pages
    Route::get('/calendars', [CalendarController::class, 'index'])->name('calendars.index');
    Route::get('/calendars/data', [CalendarController::class, 'getCalendarData'])->name('calendars.data');

    // Reports routes (for health workers)
    Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');
    Route::get('/reports/verify', [ReportsController::class, 'verify'])->name('reports.verify');
    Route::post('/reports/{id}/approve', [ReportsController::class, 'approve'])->name('reports.approve');
    Route::post('/reports/{id}/reject', [ReportsController::class, 'reject'])->name('reports.reject');



    // Services Management Routes
    Route::prefix('services')->name('services.')->group(function () {
        Route::get('/', [App\Http\Controllers\ServicesController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\ServicesController::class, 'store'])->name('store');
        Route::put('/{id}', [App\Http\Controllers\ServicesController::class, 'update'])->name('update');
        Route::patch('/{id}/toggle-status', [App\Http\Controllers\ServicesController::class, 'toggleStatus'])->name('toggle-status');
        Route::delete('/{id}', [App\Http\Controllers\ServicesController::class, 'destroy'])->name('destroy');
    });

    // User Requests Management Routes
    Route::prefix('user-requests')->name('user-requests.')->group(function () {
        Route::get('/', [App\Http\Controllers\UserRequestController::class, 'index'])->name('index');
        Route::get('/{id}', [App\Http\Controllers\UserRequestController::class, 'show'])->name('show');
        Route::post('/{id}/approve', [App\Http\Controllers\UserRequestController::class, 'approve'])->name('approve');
        Route::post('/{id}/decline', [App\Http\Controllers\UserRequestController::class, 'decline'])->name('decline');
    });

    // Schedules Management Routes
    Route::prefix('schedules')->name('schedules.')->group(function () {
        Route::get('/', [App\Http\Controllers\ScheduleController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\ScheduleController::class, 'store'])->name('store');
        Route::put('/{id}', [App\Http\Controllers\ScheduleController::class, 'update'])->name('update');
        Route::delete('/{id}', [App\Http\Controllers\ScheduleController::class, 'destroy'])->name('destroy');
        Route::get('/assigned-doctors', [App\Http\Controllers\ScheduleController::class, 'getAssignedDoctors'])->name('assigned-doctors');
    });
    // Account Management Routes
    Route::prefix('accounts')->name('accounts.')->group(function () {
        Route::get('/', [App\Http\Controllers\AccountController::class, 'index'])->name('index');
        Route::get('/profile', [App\Http\Controllers\AccountController::class, 'editProfile'])->name('profile.edit');
        Route::put('/profile', [App\Http\Controllers\AccountController::class, 'updateProfile'])->name('profile.update');
        Route::put('/password', [App\Http\Controllers\AccountController::class, 'changePassword'])->name('password.update');
        
        // Staff Management
        Route::get('/staff/create', [App\Http\Controllers\AccountController::class, 'createStaff'])->name('staff.create');
        Route::post('/staff', [App\Http\Controllers\AccountController::class, 'storeStaff'])->name('staff.store');
        Route::get('/staff/{id}/edit', [App\Http\Controllers\AccountController::class, 'editStaff'])->name('staff.edit');
        Route::put('/staff/{id}', [App\Http\Controllers\AccountController::class, 'updateStaff'])->name('staff.update');
        Route::delete('/staff/{id}', [App\Http\Controllers\AccountController::class, 'destroyStaff'])->name('staff.destroy');
    });

    // Admin RHU management routes
    Route::prefix('admin/rhus')->name('admin.rhus.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::get('/approvals', [AdminController::class, 'indexApprovals'])->name('approvals');
        Route::get('/create', [AdminController::class, 'create'])->name('create');
        Route::post('/', [AdminController::class, 'store'])->name('store');
        Route::get('/{id}', [AdminController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [AdminController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AdminController::class, 'update'])->name('update');
        Route::delete('/{id}', [AdminController::class, 'destroy'])->name('destroy');
    });

    // RHU management routes
    Route::prefix('rhus')->name('rhus.')->group(function () {
        Route::get('/', [RHUController::class, 'index'])->name('index');
        Route::get('/approvals', [RHUController::class, 'indexApprovals'])->name('approvals');
        Route::get('/create', [RHUController::class, 'create'])->name('create');
        Route::post('/', [RHUController::class, 'store'])->name('store');
        Route::get('/{id}', [RHUController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [RHUController::class, 'edit'])->name('edit');
        Route::put('/{id}', [RHUController::class, 'update'])->name('update');
        Route::delete('/{id}', [RHUController::class, 'destroy'])->name('destroy');
    });

    // Test authentication route
    Route::get('/test-auth', function() {
        return response()->json([
            'authenticated' => session('user') ? true : false,
            'user_data' => session('user'),
            'message' => 'If you can see this, you are authenticated!'
        ]);
    })->name('test.auth');

    // Test session route
    Route::get('/test-session', function() {
        return response()->json([
            'session_has_user' => session('user') ? 'YES' : 'NO',
            'session_data' => session()->all(),
            'user_data' => session('user'),
            'barangayId' => session('user.barangayId'),
            'user_id' => session('user.id'),
            'user_role' => session('user.role')
        ]);
    })->name('test.session');

    // Test sub-collections route
    Route::get('/test-subcollections', function() {
        $firestore = app('App\Services\FirebaseService')->getFirestore();
        $barangayId = session('user.barangayId', session('user.id', 'sZK52EtUl22SSCKzSPIM'));
        
        $results = [];
        $collections = ['inventory', 'personnel', 'schedules', 'events', 'userRequests'];
        
        foreach ($collections as $collection) {
            try {
                $start = microtime(true);
                $docs = $firestore
                    ->collection("barangay/{$barangayId}/{$collection}")
                    ->limit(5)
                    ->documents();
                
                $count = 0;
                foreach ($docs as $doc) {
                    if ($doc->exists()) {
                        $count++;
                    }
                }
                
                $time = microtime(true) - $start;
                $results[$collection] = [
                    'exists' => true,
                    'count' => $count,
                    'time' => round($time, 3)
                ];
            } catch (\Exception $e) {
                $results[$collection] = [
                    'exists' => false,
                    'error' => $e->getMessage(),
                    'time' => 0
                ];
            }
        }
        
        return response()->json([
            'barangay_id' => $barangayId,
            'results' => $results
        ]);
    })->name('test.subcollections');
});
