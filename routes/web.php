<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SysUserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\FirestoreTestController;
use App\Http\Controllers\RHUController;
use App\Http\Controllers\RHURegistrationController;
use App\Http\Controllers\NotificationController;

Route::get('/firestore-test', [FirestoreTestController::class, 'index']);

Route::get('/login', [SysUserController::class, 'login'])->name('login');
Route::post('/login', [SysUserController::class, 'authenticate'])->name('login');

Route::get('/register', function () {
    return view('auth.registerSelect');
})->name('register.select');

Route::get('/register/admin', [SysUserController::class, 'register'])->name('register.admin');
Route::post('/register/admin', [SysUserController::class, 'store'])->name('register.admin.submit');

Route::get('/register/rhu', [RHURegistrationController::class, 'create'])->name('rhu.register');
Route::post('/register/rhu', [RHURegistrationController::class, 'store'])->name('rhu.register.submit');

Route::get('/', [HomeController::class, 'index']);

Route::post('/logout', [SysUserController::class, 'logout'])->name('logout');

Route::middleware(['admin.auth'])->group(function () {
    Route::get('/RHUs/approvals', [AdminController::class, 'indexApprovals'])->name('RHUs.approvals');
    Route::resource('RHUs', AdminController::class);
});

Route::middleware(['rhu.auth'])->group(function () {
    Route::get('/rhu/pending', [RHUController::class, 'pending'])->name('rhu.pending');
    Route::resource('BHUs', RHUController::class);
    Route::get('/rhu/reports', [RHUController::class, 'indexReports'])->name('rhu.reports');
    Route::get('/rhu/reports/{id}', [RHUController::class, 'showReport'])->name('rhu.reports.view');
    Route::get('/rhu/approvals', [RHUController::class, 'indexApprovals'])->name('rhu.approvals');
    Route::get('/rhu/doctors', [RHUController::class, 'indexDoctors'])->name('rhu.doctors');
    Route::get('/rhu/notifications', [NotificationController::class, 'index'])->name('rhu.notifications');
    Route::get('/notifications/{id}/view', [NotificationController::class, 'view'])->name('notifications.view');
    Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
});