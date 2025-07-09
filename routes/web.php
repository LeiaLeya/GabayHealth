<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RuralHealthUnitController;
use App\Http\Controllers\SysUserController;

Route::get('/login', [SysUserController::class, 'login'])->name('login');
Route::post('/login', [SysUserController::class, 'authenticate'])->name('login.submit');

Route::get('/register', [SysUserController::class, 'register'])->name('register');
Route::post('/register', [SysUserController::class, 'store'])->name('register.submit');

// Route::get('/', fn() => view('welcome'));

// Route::get('/reports', fn() => view('pages.reports'))->name('reports.index');
// Route::get('/schedules', fn() => view('pages.schedules'))->name('schedules.index');
// Route::get('/events', fn() => view('pages.events'))->name('events.index');
// Route::get('/inventory', fn() => view('pages.inventory'))->name('inventory.index');
// Route::get('/personnel', fn() => view('pages.personnel'))->name('personnel.index');
// Route::get('/logout', fn() => redirect('/'))->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/RHUs/approvals', [RuralHealthUnitController::class, 'indexApprovals'])->name('RHUs.approvals');
    Route::resource('RHUs', RuralHealthUnitController::class);
    Route::post('/logout', [SysUserController::class, 'logout'])->name('logout');
});


