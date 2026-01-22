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
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\AuthController;

// BHC Controllers
use App\Http\Controllers\BHC\ReportsController as BHCReportsController;
use App\Http\Controllers\BHC\InventoryController as BHCInventoryController;
use App\Http\Controllers\BHC\ScheduleController as BHCScheduleController;
use App\Http\Controllers\BHC\ServicesController as BHCServicesController;
use App\Http\Controllers\BHC\PersonnelController as BHCPersonnelController;
use App\Http\Controllers\BHC\EventController as BHCEventController;
use App\Http\Controllers\BHC\CalendarController as BHCCalendarController;
use App\Http\Controllers\BHC\UserRequestController as BHCUserRequestController;
use App\Http\Controllers\BHC\AccountController as BHCAccountController;
use App\Http\Controllers\BHC\NotificationController as BHCNotificationController;

// RHU Controllers
use App\Http\Controllers\RHU\ReportsController as RHUReportsController;
use App\Http\Controllers\RHU\InventoryController as RHUInventoryController;
use App\Http\Controllers\RHU\ScheduleController as RHUScheduleController;
use App\Http\Controllers\RHU\ServicesController as RHUServicesController;
use App\Http\Controllers\RHU\PersonnelController as RHUPersonnelController;
use App\Http\Controllers\RHU\EventController as RHUEventController;
use App\Http\Controllers\RHU\CalendarController as RHUCalendarController;
use App\Http\Controllers\RHU\UserRequestController as RHUUserRequestController;
use App\Http\Controllers\RHU\AccountController as RHUAccountController;
use App\Http\Controllers\RHU\NotificationController as RHUNotificationController;

Route::get('/debug-firestore', function () {
    $firebaseService = app(\App\Services\FirebaseService::class);

    try {
        $start = microtime(true);
        $docs = $firebaseService->getFirestore()->collection('barangay')->limit(1)->documents();
        $duration = microtime(true) - $start;

        \Log::info('Firestore debug: fetched ' . iterator_count($docs) . ' docs in ' . $duration . ' seconds');
        return 'OK';
    } catch (\Throwable $e) {
        \Log::error('Firestore debug error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        return 'Error: ' . $e->getMessage();
    }
});

// Home route - redirects to appropriate dashboard
Route::get('/', function() {
    if (session('user')) {
        $role = session('user.role');
        if ($role === 'admin') {
            return redirect()->route('admin.rhus.index');
        } elseif ($role === 'rhu') {
            return redirect()->route('rhu.reports.index');
        } elseif ($role === 'barangay') {
            return redirect()->route('bhc.reports.index');
        }
    }
    return redirect()->route('login');
})->name('home');

// Public routes (no authentication required)
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

// Google OAuth routes for LOGIN (not registration)
Route::get('/auth/google/login', [LoginController::class, 'redirectToGoogle'])->name('google.login.redirect');
Route::get('/auth/google/login/callback', [LoginController::class, 'handleGoogleCallback'])->name('google.login.callback');

// Registration landing and role-specific forms
Route::get('/register', [RegisterController::class, 'landing'])->name('register.landing');
Route::get('/register/bhw', [RegisterController::class, 'showBhwForm'])->name('register.bhw');
Route::post('/register/bhw', [RegisterController::class, 'registerBhw'])->name('register.bhw.submit');
Route::get('/register/rhu', [RegisterController::class, 'showRhuForm'])->name('register.rhu');
Route::post('/register/rhu', [RegisterController::class, 'registerRhu'])->name('register.rhu.submit');

// Google OAuth routes
Route::get('/auth/google', [RegisterController::class, 'redirectToGoogle'])->name('google.redirect');
Route::get('/auth/google/callback', [RegisterController::class, 'handleGoogleCallback'])->name('google.callback');
Route::get('/register/rhu/google', [RegisterController::class, 'showGoogleForm'])->name('register.rhu.google');
Route::post('/register/rhu/google', [RegisterController::class, 'registerRhuGoogle'])->name('register.rhu.google.submit');

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

    // Generic routes - Redirect to role-based routes
    Route::get('/events', function() {
        $user = session('user');
        if (!$user) return redirect()->route('login');
        $role = $user['role'] ?? null;
        if ($role === 'rhu') return redirect()->route('rhu.events.index');
        elseif ($role === 'barangay') return redirect()->route('bhc.events.index');
        return app(EventController::class)->index();
    })->name('events.index');
    
    Route::get('/events/create', function() {
        $user = session('user');
        if (!$user) return redirect()->route('login');
        $role = $user['role'] ?? null;
        if ($role === 'rhu') return redirect()->route('rhu.events.create');
        elseif ($role === 'barangay') return redirect()->route('bhc.events.create');
        return app(EventController::class)->create();
    })->name('events.create');
    
    Route::post('/events/store', function() {
        $user = session('user');
        if (!$user) return redirect()->route('login');
        $role = $user['role'] ?? null;
        if ($role === 'rhu') return app(RHUEventController::class)->store(request());
        elseif ($role === 'barangay') return app(BHCEventController::class)->store(request());
        return app(EventController::class)->store(request());
    })->name('events.store');
    
    Route::get('/events/{id}', function($id) {
        $user = session('user');
        if (!$user) return redirect()->route('login');
        $role = $user['role'] ?? null;
        if ($role === 'rhu') return redirect()->route('rhu.events.show', $id);
        elseif ($role === 'barangay') return redirect()->route('bhc.events.show', $id);
        return app(EventController::class)->show($id);
    })->name('events.show');
    
    Route::get('/events/{id}/edit', function($id) {
        $user = session('user');
        if (!$user) return redirect()->route('login');
        $role = $user['role'] ?? null;
        if ($role === 'rhu') return redirect()->route('rhu.events.edit', $id);
        elseif ($role === 'barangay') return redirect()->route('bhc.events.edit', $id);
        return app(EventController::class)->edit($id);
    })->name('events.edit');
    
    Route::put('/events/{id}', function($id) {
        $user = session('user');
        if (!$user) return redirect()->route('login');
        $role = $user['role'] ?? null;
        if ($role === 'rhu') return app(RHUEventController::class)->update(request(), $id);
        elseif ($role === 'barangay') return app(BHCEventController::class)->update(request(), $id);
        return app(EventController::class)->update(request(), $id);
    })->name('events.update');
    
    Route::post('/events/{id}/cancel', function($id) {
        $user = session('user');
        if (!$user) return redirect()->route('login');
        $role = $user['role'] ?? null;
        if ($role === 'rhu') return app(RHUEventController::class)->cancel($id);
        elseif ($role === 'barangay') return app(BHCEventController::class)->cancel($id);
        return app(EventController::class)->cancel($id);
    })->name('events.cancel');
    
    Route::get('/events/{id}/export-pdf', function($id) {
        $user = session('user');
        if (!$user) return redirect()->route('login');
        $role = $user['role'] ?? null;
        if ($role === 'rhu') return redirect()->route('rhu.events.exportPdf', $id);
        elseif ($role === 'barangay') return redirect()->route('bhc.events.exportPdf', $id);
        return app(EventController::class)->exportPdf($id);
    })->name('events.exportPdf');

    // Inventory routes - Redirect to role-based routes
    Route::get('/inventory', function() {
        $user = session('user');
        if (!$user) return redirect()->route('login');
        $role = $user['role'] ?? null;
        if ($role === 'rhu') return redirect()->route('rhu.inventory.index');
        elseif ($role === 'barangay') return redirect()->route('bhc.inventory.index');
        return app(InventoryController::class)->index();
    })->name('inventory.index');
    Route::get('/inventory/add-batch', [InventoryController::class, 'showAddBatch'])->name('inventory.add-batch');
    Route::get('/inventory/{id}/sort', [InventoryController::class, 'showSorted'])->name('inventory.show.sorted');
    Route::get('/inventory/residents/search', [InventoryController::class, 'searchResidents'])->name('inventory.residents.search');
    Route::post('/inventory/residents', function() {
        $user = session('user');
        if (!$user) return redirect()->route('login');
        $role = $user['role'] ?? null;
        if ($role === 'rhu') return app(RHUInventoryController::class)->storeResident(request());
        elseif ($role === 'barangay') return app(BHCInventoryController::class)->storeResident(request());
        return app(InventoryController::class)->storeResident(request());
    })->name('inventory.residents.store');
    
    Route::get('/inventory/personnel/search', [InventoryController::class, 'searchPersonnel'])->name('inventory.personnel.search');
    Route::get('/inventory/{id}/release-history', [InventoryController::class, 'showReleaseHistory'])->name('inventory.release-history');
    Route::get('/inventory/{id}', [InventoryController::class, 'show'])->name('inventory.show');
    Route::get('/inventory/{parentId}/batches/{batchId}/history', [InventoryController::class, 'showDistributionHistory'])->name('inventory.batches.history');
    
    Route::post('/inventory', function() {
        $user = session('user');
        if (!$user) return redirect()->route('login');
        $role = $user['role'] ?? null;
        if ($role === 'rhu') return app(RHUInventoryController::class)->store(request());
        elseif ($role === 'barangay') return app(BHCInventoryController::class)->store(request());
        return app(InventoryController::class)->store(request());
    })->name('inventory.store');
    
    Route::post('/inventory/batches', function() {
        $user = session('user');
        if (!$user) return redirect()->route('login');
        $role = $user['role'] ?? null;
        if ($role === 'rhu') return app(RHUInventoryController::class)->storeBatch(request());
        elseif ($role === 'barangay') return app(BHCInventoryController::class)->storeBatch(request());
        return app(InventoryController::class)->storeBatch(request());
    })->name('inventory.batches.store');
    
    Route::put('/inventory/{id}', function($id) {
        $user = session('user');
        if (!$user) return redirect()->route('login');
        $role = $user['role'] ?? null;
        if ($role === 'rhu') return app(RHUInventoryController::class)->update(request(), $id);
        elseif ($role === 'barangay') return app(BHCInventoryController::class)->update(request(), $id);
        return app(InventoryController::class)->update(request(), $id);
    })->name('inventory.update');
    
    Route::put('/inventory/{parentId}/batches/{batchId}/distribute', function($parentId, $batchId) {
        $user = session('user');
        if (!$user) return redirect()->route('login');
        $role = $user['role'] ?? null;
        if ($role === 'rhu') return app(RHUInventoryController::class)->distributeBatch(request(), $parentId, $batchId);
        elseif ($role === 'barangay') return app(BHCInventoryController::class)->distributeBatch(request(), $parentId, $batchId);
        return app(InventoryController::class)->distributeBatch(request(), $parentId, $batchId);
    })->name('inventory.batches.distribute');
    
    Route::put('/inventory/{parentId}/batches/{batchId}', function($parentId, $batchId) {
        $user = session('user');
        if (!$user) return redirect()->route('login');
        $role = $user['role'] ?? null;
        if ($role === 'rhu') return app(RHUInventoryController::class)->updateBatch(request(), $parentId, $batchId);
        elseif ($role === 'barangay') return app(BHCInventoryController::class)->updateBatch(request(), $parentId, $batchId);
        return app(InventoryController::class)->updateBatch(request(), $parentId, $batchId);
    })->name('inventory.batches.update');
    
    Route::put('/inventory/{parentId}/release', function($parentId) {
        $user = session('user');
        if (!$user) return redirect()->route('login');
        $role = $user['role'] ?? null;
        if ($role === 'rhu') return app(RHUInventoryController::class)->releaseMedicine(request(), $parentId);
        elseif ($role === 'barangay') return app(BHCInventoryController::class)->releaseMedicine(request(), $parentId);
        return app(InventoryController::class)->releaseMedicine(request(), $parentId);
    })->name('inventory.release');
    
    Route::delete('/inventory/{id}', function($id) {
        $user = session('user');
        if (!$user) return redirect()->route('login');
        $role = $user['role'] ?? null;
        if ($role === 'rhu') return app(RHUInventoryController::class)->destroy($id);
        elseif ($role === 'barangay') return app(BHCInventoryController::class)->destroy($id);
        return app(InventoryController::class)->destroy($id);
    })->name('inventory.destroy');
    
    Route::delete('/inventory/{parentId}/batches/{batchId}', function($parentId, $batchId) {
        $user = session('user');
        if (!$user) return redirect()->route('login');
        $role = $user['role'] ?? null;
        if ($role === 'rhu') return app(RHUInventoryController::class)->destroyBatch($parentId, $batchId);
        elseif ($role === 'barangay') return app(BHCInventoryController::class)->destroyBatch($parentId, $batchId);
        return app(InventoryController::class)->destroyBatch($parentId, $batchId);
    })->name('inventory.batches.destroy');

    // Personnel routes - Redirect to role-based routes
    Route::get('/personnel', function() {
        $user = session('user');
        if (!$user) return redirect()->route('login');
        $role = $user['role'] ?? null;
        if ($role === 'rhu') return redirect()->route('rhu.personnel.index');
        elseif ($role === 'barangay') return redirect()->route('bhc.personnel.index');
        return app(PersonnelController::class)->index();
    })->name('personnel.index');
    Route::post('/personnel', function() {
        $user = session('user');
        if (!$user) return redirect()->route('login');
        $role = $user['role'] ?? null;
        if ($role === 'rhu') return app(RHUPersonnelController::class)->store(request());
        elseif ($role === 'barangay') return app(BHCPersonnelController::class)->store(request());
        return app(PersonnelController::class)->store(request());
    })->name('personnel.store');
    
    Route::put('/personnel/{id}', function($id) {
        $user = session('user');
        if (!$user) return redirect()->route('login');
        $role = $user['role'] ?? null;
        if ($role === 'rhu') return app(RHUPersonnelController::class)->update(request(), $id);
        elseif ($role === 'barangay') return app(BHCPersonnelController::class)->update(request(), $id);
        return app(PersonnelController::class)->update(request(), $id);
    })->name('personnel.update');
    
    Route::delete('/personnel/{id}', function($id) {
        $user = session('user');
        if (!$user) return redirect()->route('login');
        $role = $user['role'] ?? null;
        if ($role === 'rhu') return app(RHUPersonnelController::class)->destroy($id);
        elseif ($role === 'barangay') return app(BHCPersonnelController::class)->destroy($id);
        return app(PersonnelController::class)->destroy($id);
    })->name('personnel.destroy');

    // New feature pages - Redirect to role-based calendar routes
    Route::get('/calendars', function() {
        $user = session('user');
        if (!$user) {
            return redirect()->route('login');
        }
        
        $role = $user['role'] ?? null;
        if ($role === 'rhu') {
            return redirect()->route('rhu.calendars.index');
        } elseif ($role === 'barangay') {
            return redirect()->route('bhc.calendars.index');
        }
        
        // Fallback to generic calendar (for other roles like admin, health-worker)
        return app(CalendarController::class)->index();
    })->name('calendars.index');
    
    Route::get('/calendars/data', [CalendarController::class, 'getCalendarData'])->name('calendars.data');

    // Reports routes - Redirect to role-based routes
    Route::get('/reports', function() {
        $user = session('user');
        if (!$user) return redirect()->route('login');
        $role = $user['role'] ?? null;
        if ($role === 'rhu') return redirect()->route('rhu.reports.index');
        elseif ($role === 'barangay') return redirect()->route('bhc.reports.index');
        return app(ReportsController::class)->index();
    })->name('reports.index');
    Route::get('/reports/verify', function() {
        $user = session('user');
        if (!$user) return redirect()->route('login');
        $role = $user['role'] ?? null;
        if ($role === 'rhu') return redirect()->route('rhu.reports.verify');
        elseif ($role === 'barangay') return redirect()->route('bhc.reports.verify');
        return app(ReportsController::class)->verify();
    })->name('reports.verify');
    Route::get('/reports/rejected', [ReportsController::class, 'rejected'])->name('reports.rejected');
    Route::post('/reports/{id}/approve', function($id) {
        $user = session('user');
        if (!$user) return redirect()->route('login');
        $role = $user['role'] ?? null;
        if ($role === 'rhu') return app(RHUReportsController::class)->approve($id);
        elseif ($role === 'barangay') return app(BHCReportsController::class)->approve($id);
        return app(ReportsController::class)->approve($id);
    })->name('reports.approve');
    
    Route::post('/reports/{id}/reject', function($id) {
        $user = session('user');
        if (!$user) return redirect()->route('login');
        $role = $user['role'] ?? null;
        if ($role === 'rhu') return app(RHUReportsController::class)->reject($id);
        elseif ($role === 'barangay') return app(BHCReportsController::class)->reject($id);
        return app(ReportsController::class)->reject($id);
    })->name('reports.reject');

    // Notifications routes - Redirect to role-based routes
    Route::get('/notifications', function() {
        $user = session('user');
        if (!$user) return redirect()->route('login');
        $role = $user['role'] ?? null;
        if ($role === 'rhu') return redirect()->route('rhu.notifications.index');
        elseif ($role === 'barangay') return redirect()->route('bhc.notifications.index');
        return app(NotificationController::class)->index();
    })->name('notifications.index');
    Route::post('/notifications', function() {
        $user = session('user');
        if (!$user) return redirect()->route('login');
        $role = $user['role'] ?? null;
        if ($role === 'rhu') return app(RHUNotificationController::class)->store(request());
        elseif ($role === 'barangay') return app(BHCNotificationController::class)->store(request());
        return app(NotificationController::class)->store(request());
    })->name('notifications.store');
    
    Route::delete('/notifications/{id}', function($id) {
        $user = session('user');
        if (!$user) return redirect()->route('login');
        $role = $user['role'] ?? null;
        if ($role === 'rhu') return app(RHUNotificationController::class)->destroy($id);
        elseif ($role === 'barangay') return app(BHCNotificationController::class)->destroy($id);
        return app(NotificationController::class)->destroy($id);
    })->name('notifications.destroy');



    // Services Management Routes - Redirect to role-based routes
    Route::prefix('services')->name('services.')->group(function () {
        Route::get('/', function() {
            $user = session('user');
            if (!$user) return redirect()->route('login');
            $role = $user['role'] ?? null;
            if ($role === 'rhu') return redirect()->route('rhu.services.index');
            elseif ($role === 'barangay') return redirect()->route('bhc.services.index');
            return app(ServicesController::class)->index();
        })->name('index');
        Route::post('/', function() {
            $user = session('user');
            if (!$user) return redirect()->route('login');
            $role = $user['role'] ?? null;
            if ($role === 'rhu') return app(RHUServicesController::class)->store(request());
            elseif ($role === 'barangay') return app(BHCServicesController::class)->store(request());
            return app(ServicesController::class)->store(request());
        })->name('store');
        
        Route::put('/{id}', function($id) {
            $user = session('user');
            if (!$user) return redirect()->route('login');
            $role = $user['role'] ?? null;
            if ($role === 'rhu') return app(RHUServicesController::class)->update(request(), $id);
            elseif ($role === 'barangay') return app(BHCServicesController::class)->update(request(), $id);
            return app(ServicesController::class)->update(request(), $id);
        })->name('update');
        
        Route::patch('/{id}/toggle-status', function($id) {
            $user = session('user');
            if (!$user) return redirect()->route('login');
            $role = $user['role'] ?? null;
            if ($role === 'rhu') return app(RHUServicesController::class)->toggleStatus($id);
            elseif ($role === 'barangay') return app(BHCServicesController::class)->toggleStatus($id);
            return app(ServicesController::class)->toggleStatus($id);
        })->name('toggle-status');
        
        Route::delete('/{id}', function($id) {
            $user = session('user');
            if (!$user) return redirect()->route('login');
            $role = $user['role'] ?? null;
            if ($role === 'rhu') return app(RHUServicesController::class)->destroy($id);
            elseif ($role === 'barangay') return app(BHCServicesController::class)->destroy($id);
            return app(ServicesController::class)->destroy($id);
        })->name('destroy');
    });

    // User Requests Management Routes - Redirect to role-based routes
    Route::prefix('user-requests')->name('user-requests.')->group(function () {
        Route::get('/', function() {
            $user = session('user');
            if (!$user) return redirect()->route('login');
            $role = $user['role'] ?? null;
            if ($role === 'rhu') return redirect()->route('rhu.user-requests.index');
            elseif ($role === 'barangay') return redirect()->route('bhc.user-requests.index');
            return app(UserRequestController::class)->index();
        })->name('index');
        Route::get('/{id}', [App\Http\Controllers\UserRequestController::class, 'show'])->name('show');
        Route::post('/{id}/approve', function($id) {
            $user = session('user');
            if (!$user) return redirect()->route('login');
            $role = $user['role'] ?? null;
            if ($role === 'rhu') return app(RHUUserRequestController::class)->approve($id);
            elseif ($role === 'barangay') return app(BHCUserRequestController::class)->approve($id);
            return app(UserRequestController::class)->approve($id);
        })->name('approve');
        
        Route::post('/{id}/decline', function($id) {
            $user = session('user');
            if (!$user) return redirect()->route('login');
            $role = $user['role'] ?? null;
            if ($role === 'rhu') return app(RHUUserRequestController::class)->decline($id);
            elseif ($role === 'barangay') return app(BHCUserRequestController::class)->decline($id);
            return app(UserRequestController::class)->decline($id);
        })->name('decline');
    });

    // Schedules Management Routes - Redirect to role-based routes
    Route::prefix('schedules')->name('schedules.')->group(function () {
        Route::get('/', function() {
            $user = session('user');
            if (!$user) return redirect()->route('login');
            $role = $user['role'] ?? null;
            if ($role === 'rhu') return redirect()->route('rhu.schedules.index');
            elseif ($role === 'barangay') return redirect()->route('bhc.schedules.index');
            return app(ScheduleController::class)->index();
        })->name('index');
        Route::post('/', function() {
            $user = session('user');
            if (!$user) return redirect()->route('login');
            $role = $user['role'] ?? null;
            if ($role === 'rhu') return app(RHUScheduleController::class)->store(request());
            elseif ($role === 'barangay') return app(BHCScheduleController::class)->store(request());
            return app(ScheduleController::class)->store(request());
        })->name('store');
        
        Route::put('/{id}', function($id) {
            $user = session('user');
            if (!$user) return redirect()->route('login');
            $role = $user['role'] ?? null;
            if ($role === 'rhu') return app(RHUScheduleController::class)->update(request(), $id);
            elseif ($role === 'barangay') return app(BHCScheduleController::class)->update(request(), $id);
            return app(ScheduleController::class)->update(request(), $id);
        })->name('update');
        
        Route::delete('/{id}', function($id) {
            $user = session('user');
            if (!$user) return redirect()->route('login');
            $role = $user['role'] ?? null;
            if ($role === 'rhu') return app(RHUScheduleController::class)->destroy($id);
            elseif ($role === 'barangay') return app(BHCScheduleController::class)->destroy($id);
            return app(ScheduleController::class)->destroy($id);
        })->name('destroy');
        Route::get('/assigned-doctors', [App\Http\Controllers\ScheduleController::class, 'getAssignedDoctors'])->name('assigned-doctors');
    });
    // Account Management Routes - Redirect to role-based routes
    Route::prefix('accounts')->name('accounts.')->group(function () {
        Route::get('/', function() {
            $user = session('user');
            if (!$user) return redirect()->route('login');
            $role = $user['role'] ?? null;
            if ($role === 'rhu') return redirect()->route('rhu.accounts.index');
            elseif ($role === 'barangay') return redirect()->route('bhc.accounts.index');
            return app(AccountController::class)->index();
        })->name('index');
        Route::get('/profile', [App\Http\Controllers\AccountController::class, 'editProfile'])->name('profile.edit');
        Route::put('/profile', function() {
            $user = session('user');
            if (!$user) return redirect()->route('login');
            $role = $user['role'] ?? null;
            if ($role === 'rhu') return app(RHUAccountController::class)->updateProfile(request());
            elseif ($role === 'barangay') return app(BHCAccountController::class)->updateProfile(request());
            return app(AccountController::class)->updateProfile(request());
        })->name('profile.update');
        
        Route::put('/password', function() {
            $user = session('user');
            if (!$user) return redirect()->route('login');
            $role = $user['role'] ?? null;
            if ($role === 'rhu') return app(RHUAccountController::class)->changePassword(request());
            elseif ($role === 'barangay') return app(BHCAccountController::class)->changePassword(request());
            return app(AccountController::class)->changePassword(request());
        })->name('password.update');
        
        // Staff Management
        Route::get('/staff/create', function() {
            $user = session('user');
            if (!$user) return redirect()->route('login');
            $role = $user['role'] ?? null;
            if ($role === 'rhu') return app(RHUAccountController::class)->createStaff();
            elseif ($role === 'barangay') return app(BHCAccountController::class)->createStaff();
            return app(AccountController::class)->createStaff();
        })->name('staff.create');
        
        Route::post('/staff', function() {
            $user = session('user');
            if (!$user) return redirect()->route('login');
            $role = $user['role'] ?? null;
            if ($role === 'rhu') return app(RHUAccountController::class)->storeStaff(request());
            elseif ($role === 'barangay') return app(BHCAccountController::class)->storeStaff(request());
            return app(AccountController::class)->storeStaff(request());
        })->name('staff.store');
        
        Route::get('/staff/{id}/edit', [App\Http\Controllers\AccountController::class, 'editStaff'])->name('staff.edit');
        
        Route::put('/staff/{id}', function($id) {
            $user = session('user');
            if (!$user) return redirect()->route('login');
            $role = $user['role'] ?? null;
            if ($role === 'rhu') return app(RHUAccountController::class)->updateStaff(request(), $id);
            elseif ($role === 'barangay') return app(BHCAccountController::class)->updateStaff(request(), $id);
            return app(AccountController::class)->updateStaff(request(), $id);
        })->name('staff.update');
        
        Route::delete('/staff/{id}', function($id) {
            $user = session('user');
            if (!$user) return redirect()->route('login');
            $role = $user['role'] ?? null;
            if ($role === 'rhu') return app(RHUAccountController::class)->destroyStaff($id);
            elseif ($role === 'barangay') return app(BHCAccountController::class)->destroyStaff($id);
            return app(AccountController::class)->destroyStaff($id);
        })->name('staff.destroy');
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

    // ============================================
    // BHC (Barangay Health Center) Routes
    // ============================================
    Route::middleware(['auth.check', 'role:barangay'])->prefix('bhc')->name('bhc.')->group(function () {
        // Reports routes
        Route::get('/reports', [BHCReportsController::class, 'index'])->name('reports.index');
        Route::get('/reports/verify', [BHCReportsController::class, 'verify'])->name('reports.verify');
        Route::get('/reports/verified', [BHCReportsController::class, 'verified'])->name('reports.verified');
        Route::get('/reports/rejected', [BHCReportsController::class, 'rejected'])->name('reports.rejected');
        Route::post('/reports/{id}/approve', [BHCReportsController::class, 'approve'])->name('reports.approve');
        Route::post('/reports/{id}/reject', [BHCReportsController::class, 'reject'])->name('reports.reject');

        // Inventory routes
        Route::get('/inventory', [BHCInventoryController::class, 'index'])->name('inventory.index');
        Route::get('/inventory/add-batch', [BHCInventoryController::class, 'showAddBatch'])->name('inventory.add-batch');
        Route::get('/inventory/{id}/sort', [BHCInventoryController::class, 'showSorted'])->name('inventory.show.sorted');
        Route::get('/inventory/residents/search', [BHCInventoryController::class, 'searchResidents'])->name('inventory.residents.search');
        Route::post('/inventory/residents', [BHCInventoryController::class, 'storeResident'])->name('inventory.residents.store');
        Route::get('/inventory/personnel/search', [BHCInventoryController::class, 'searchPersonnel'])->name('inventory.personnel.search');
        Route::get('/inventory/{id}/release-history', [BHCInventoryController::class, 'showReleaseHistory'])->name('inventory.release-history');
        Route::get('/inventory/{id}', [BHCInventoryController::class, 'show'])->name('inventory.show');
        Route::get('/inventory/{parentId}/batches/{batchId}/history', [BHCInventoryController::class, 'showDistributionHistory'])->name('inventory.batches.history');
        Route::post('/inventory', [BHCInventoryController::class, 'store'])->name('inventory.store');
        Route::post('/inventory/batches', [BHCInventoryController::class, 'storeBatch'])->name('inventory.batches.store');
        Route::put('/inventory/{id}', [BHCInventoryController::class, 'update'])->name('inventory.update');
        Route::put('/inventory/{parentId}/batches/{batchId}/distribute', [BHCInventoryController::class, 'distributeBatch'])->name('inventory.batches.distribute');
        Route::put('/inventory/{parentId}/batches/{batchId}', [BHCInventoryController::class, 'updateBatch'])->name('inventory.batches.update');
        Route::put('/inventory/{parentId}/release', [BHCInventoryController::class, 'releaseMedicine'])->name('inventory.release');
        Route::delete('/inventory/{id}', [BHCInventoryController::class, 'destroy'])->name('inventory.destroy');
        Route::delete('/inventory/{parentId}/batches/{batchId}', [BHCInventoryController::class, 'destroyBatch'])->name('inventory.batches.destroy');

        // Personnel routes
        Route::get('/personnel', [BHCPersonnelController::class, 'index'])->name('personnel.index');
        Route::post('/personnel', [BHCPersonnelController::class, 'store'])->name('personnel.store');
        Route::put('/personnel/{id}', [BHCPersonnelController::class, 'update'])->name('personnel.update');
        Route::delete('/personnel/{id}', [BHCPersonnelController::class, 'destroy'])->name('personnel.destroy');

        // Schedules routes
        Route::prefix('schedules')->name('schedules.')->group(function () {
            Route::get('/', [BHCScheduleController::class, 'index'])->name('index');
            Route::post('/', [BHCScheduleController::class, 'store'])->name('store');
            Route::put('/{id}', [BHCScheduleController::class, 'update'])->name('update');
            Route::delete('/{id}', [BHCScheduleController::class, 'destroy'])->name('destroy');
            Route::get('/assigned-doctors', [BHCScheduleController::class, 'getAssignedDoctors'])->name('assigned-doctors');
        });

        // Events routes
        Route::get('/events', [BHCEventController::class, 'index'])->name('events.index');
        Route::get('/events/create', [BHCEventController::class, 'create'])->name('events.create');
        Route::post('/events/store', [BHCEventController::class, 'store'])->name('events.store');
        Route::get('/events/{id}', [BHCEventController::class, 'show'])->name('events.show');
        Route::get('/events/{id}/edit', [BHCEventController::class, 'edit'])->name('events.edit');
        Route::put('/events/{id}', [BHCEventController::class, 'update'])->name('events.update');
        Route::post('/events/{id}/cancel', [BHCEventController::class, 'cancel'])->name('events.cancel');
        Route::get('/events/{id}/export-pdf', [BHCEventController::class, 'exportPdf'])->name('events.exportPdf');

        // Calendar routes
        Route::get('/calendars', [BHCCalendarController::class, 'index'])->name('calendars.index');
        Route::get('/calendars/data', [BHCCalendarController::class, 'getCalendarData'])->name('calendars.data');

        // Services routes
        Route::prefix('services')->name('services.')->group(function () {
            Route::get('/', [BHCServicesController::class, 'index'])->name('index');
            Route::post('/', [BHCServicesController::class, 'store'])->name('store');
            Route::put('/{id}', [BHCServicesController::class, 'update'])->name('update');
            Route::patch('/{id}/toggle-status', [BHCServicesController::class, 'toggleStatus'])->name('toggle-status');
            Route::delete('/{id}', [BHCServicesController::class, 'destroy'])->name('destroy');
        });

        // User Requests routes
        Route::prefix('user-requests')->name('user-requests.')->group(function () {
            Route::get('/', [BHCUserRequestController::class, 'index'])->name('index');
            Route::get('/{id}', [BHCUserRequestController::class, 'show'])->name('show');
            Route::post('/{id}/approve', [BHCUserRequestController::class, 'approve'])->name('approve');
            Route::post('/{id}/decline', [BHCUserRequestController::class, 'decline'])->name('decline');
        });

        // Account routes
        Route::prefix('accounts')->name('accounts.')->group(function () {
            Route::get('/', [BHCAccountController::class, 'index'])->name('index');
            Route::get('/profile', [BHCAccountController::class, 'editProfile'])->name('profile.edit');
            Route::put('/profile', [BHCAccountController::class, 'updateProfile'])->name('profile.update');
            Route::put('/password', [BHCAccountController::class, 'changePassword'])->name('password.update');
            Route::get('/staff/create', [BHCAccountController::class, 'createStaff'])->name('staff.create');
            Route::post('/staff', [BHCAccountController::class, 'storeStaff'])->name('staff.store');
            Route::get('/staff/{id}/edit', [BHCAccountController::class, 'editStaff'])->name('staff.edit');
            Route::put('/staff/{id}', [BHCAccountController::class, 'updateStaff'])->name('staff.update');
            Route::delete('/staff/{id}', [BHCAccountController::class, 'destroyStaff'])->name('staff.destroy');
        });

        // Notifications routes
        Route::get('/notifications', [BHCNotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications', [BHCNotificationController::class, 'store'])->name('notifications.store');
        Route::delete('/notifications/{id}', [BHCNotificationController::class, 'destroy'])->name('notifications.destroy');
    });

    // ============================================
    // RHU (Rural Health Unit) Routes
    // ============================================
    Route::middleware(['auth.check', 'role:rhu'])->prefix('rhu')->name('rhu.')->group(function () {
        // Reports routes
        Route::get('/reports', [RHUReportsController::class, 'index'])->name('reports.index');
        Route::get('/reports/verify', [RHUReportsController::class, 'verify'])->name('reports.verify');
        Route::get('/reports/verified', [RHUReportsController::class, 'verified'])->name('reports.verified');
        Route::get('/reports/rejected', [RHUReportsController::class, 'rejected'])->name('reports.rejected');
        Route::post('/reports/{id}/approve', [RHUReportsController::class, 'approve'])->name('reports.approve');
        Route::post('/reports/{id}/reject', [RHUReportsController::class, 'reject'])->name('reports.reject');

        // Inventory routes
        Route::get('/inventory', [RHUInventoryController::class, 'index'])->name('inventory.index');
        Route::get('/inventory/add-batch', [RHUInventoryController::class, 'showAddBatch'])->name('inventory.add-batch');
        Route::get('/inventory/{id}/sort', [RHUInventoryController::class, 'showSorted'])->name('inventory.show.sorted');
        Route::get('/inventory/residents/search', [RHUInventoryController::class, 'searchResidents'])->name('inventory.residents.search');
        Route::post('/inventory/residents', [RHUInventoryController::class, 'storeResident'])->name('inventory.residents.store');
        Route::get('/inventory/personnel/search', [RHUInventoryController::class, 'searchPersonnel'])->name('inventory.personnel.search');
        Route::get('/inventory/{id}/release-history', [RHUInventoryController::class, 'showReleaseHistory'])->name('inventory.release-history');
        Route::get('/inventory/{id}', [RHUInventoryController::class, 'show'])->name('inventory.show');
        Route::get('/inventory/{parentId}/batches/{batchId}/history', [RHUInventoryController::class, 'showDistributionHistory'])->name('inventory.batches.history');
        Route::post('/inventory', [RHUInventoryController::class, 'store'])->name('inventory.store');
        Route::post('/inventory/batches', [RHUInventoryController::class, 'storeBatch'])->name('inventory.batches.store');
        Route::put('/inventory/{id}', [RHUInventoryController::class, 'update'])->name('inventory.update');
        Route::put('/inventory/{parentId}/batches/{batchId}/distribute', [RHUInventoryController::class, 'distributeBatch'])->name('inventory.batches.distribute');
        Route::put('/inventory/{parentId}/batches/{batchId}', [RHUInventoryController::class, 'updateBatch'])->name('inventory.batches.update');
        Route::put('/inventory/{parentId}/release', [RHUInventoryController::class, 'releaseMedicine'])->name('inventory.release');
        Route::delete('/inventory/{id}', [RHUInventoryController::class, 'destroy'])->name('inventory.destroy');
        Route::delete('/inventory/{parentId}/batches/{batchId}', [RHUInventoryController::class, 'destroyBatch'])->name('inventory.batches.destroy');

        // Personnel routes
        Route::get('/personnel', [RHUPersonnelController::class, 'index'])->name('personnel.index');
        Route::post('/personnel', [RHUPersonnelController::class, 'store'])->name('personnel.store');
        Route::put('/personnel/{id}', [RHUPersonnelController::class, 'update'])->name('personnel.update');
        Route::delete('/personnel/{id}', [RHUPersonnelController::class, 'destroy'])->name('personnel.destroy');

        // Schedules routes
        Route::prefix('schedules')->name('schedules.')->group(function () {
            Route::get('/', [RHUScheduleController::class, 'index'])->name('index');
            Route::post('/', [RHUScheduleController::class, 'store'])->name('store');
            Route::put('/{id}', [RHUScheduleController::class, 'update'])->name('update');
            Route::delete('/{id}', [RHUScheduleController::class, 'destroy'])->name('destroy');
            Route::get('/assigned-doctors', [RHUScheduleController::class, 'getAssignedDoctors'])->name('assigned-doctors');
        });

        // Events routes
        Route::get('/events', [RHUEventController::class, 'index'])->name('events.index');
        Route::get('/events/create', [RHUEventController::class, 'create'])->name('events.create');
        Route::post('/events/store', [RHUEventController::class, 'store'])->name('events.store');
        Route::get('/events/{id}', [RHUEventController::class, 'show'])->name('events.show');
        Route::get('/events/{id}/edit', [RHUEventController::class, 'edit'])->name('events.edit');
        Route::put('/events/{id}', [RHUEventController::class, 'update'])->name('events.update');
        Route::post('/events/{id}/cancel', [RHUEventController::class, 'cancel'])->name('events.cancel');
        Route::get('/events/{id}/export-pdf', [RHUEventController::class, 'exportPdf'])->name('events.exportPdf');

        // Calendar routes
        Route::get('/calendars', [RHUCalendarController::class, 'index'])->name('calendars.index');
        Route::get('/calendars/data', [RHUCalendarController::class, 'getCalendarData'])->name('calendars.data');

        // Services routes
        Route::prefix('services')->name('services.')->group(function () {
            Route::get('/', [RHUServicesController::class, 'index'])->name('index');
            Route::post('/', [RHUServicesController::class, 'store'])->name('store');
            Route::put('/{id}', [RHUServicesController::class, 'update'])->name('update');
            Route::patch('/{id}/toggle-status', [RHUServicesController::class, 'toggleStatus'])->name('toggle-status');
            Route::delete('/{id}', [RHUServicesController::class, 'destroy'])->name('destroy');
        });

        // User Requests routes
        Route::prefix('user-requests')->name('user-requests.')->group(function () {
            Route::get('/', [RHUUserRequestController::class, 'index'])->name('index');
            Route::get('/{id}', [RHUUserRequestController::class, 'show'])->name('show');
            Route::post('/{id}/approve', [RHUUserRequestController::class, 'approve'])->name('approve');
            Route::post('/{id}/decline', [RHUUserRequestController::class, 'decline'])->name('decline');
        });

        // Account routes
        Route::prefix('accounts')->name('accounts.')->group(function () {
            Route::get('/', [RHUAccountController::class, 'index'])->name('index');
            Route::get('/profile', [RHUAccountController::class, 'editProfile'])->name('profile.edit');
            Route::put('/profile', [RHUAccountController::class, 'updateProfile'])->name('profile.update');
            Route::put('/password', [RHUAccountController::class, 'changePassword'])->name('password.update');
            Route::get('/staff/create', [RHUAccountController::class, 'createStaff'])->name('staff.create');
            Route::post('/staff', [RHUAccountController::class, 'storeStaff'])->name('staff.store');
            Route::get('/staff/{id}/edit', [RHUAccountController::class, 'editStaff'])->name('staff.edit');
            Route::put('/staff/{id}', [RHUAccountController::class, 'updateStaff'])->name('staff.update');
            Route::delete('/staff/{id}', [RHUAccountController::class, 'destroyStaff'])->name('staff.destroy');
        });

        // Notifications routes
        Route::get('/notifications', [RHUNotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications', [RHUNotificationController::class, 'store'])->name('notifications.store');
        Route::delete('/notifications/{id}', [RHUNotificationController::class, 'destroy'])->name('notifications.destroy');
    });

    // Dashboard route (protected by auth)
    Route::get('/dashboard', function () {
        $role = session('user.role');
        return match($role) {
            'rhu' => redirect()->route('rhu.reports.index'),
            'barangay' => redirect()->route('bhc.reports.index'),
            'admin' => redirect()->route('admin.rhus.index'),
            default => redirect()->route('login'),
        };
    })->name('dashboard');
});
