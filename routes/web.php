<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SysUserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\FirestoreTestController;
use App\Http\Middleware\SessionAuth;
use App\Http\Kernel;

// Route::get('/firestore-test', [FirestoreTestController::class, 'index']);

Route::get('/login', [SysUserController::class, 'login'])->name('login');
Route::post('/login', [SysUserController::class, 'authenticate'])->name('login.submit');

Route::get('/register', [SysUserController::class, 'register'])->name('register');
Route::post('/register', [SysUserController::class, 'store'])->name('register.submit');

Route::get('/', [HomeController::class, 'index']);

// Route::get('/', fn() => view('welcome'));

// Route::get('/reports', fn() => view('pages.reports'))->name('reports.index');
// Route::get('/schedules', fn() => view('pages.schedules'))->name('schedules.index');
// Route::get('/events', fn() => view('pages.events'))->name('events.index');
// Route::get('/inventory', fn() => view('pages.inventory'))->name('inventory.index');
// Route::get('/personnel', fn() => view('pages.personnel'))->name('personnel.index');
// Route::get('/logout', fn() => redirect('/'))->name('logout');

// Route::middleware(['custom.auth'])->group(function () {
    Route::get('/RHUs/approvals', [AdminController::class, 'indexApprovals'])->name('RHUs.approvals');
    Route::resource('RHUs', AdminController::class);
    Route::post('/logout', [SysUserController::class, 'logout'])->name('logout');
// });


