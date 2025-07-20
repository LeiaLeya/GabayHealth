<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;

// Login routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

// Registration landing and role-specific forms
Route::get('/register', [RegisterController::class, 'landing'])->name('register.landing');
Route::get('/register/bhw', [RegisterController::class, 'showBhwForm'])->name('register.bhw');
Route::post('/register/bhw', [RegisterController::class, 'registerBhw'])->name('register.bhw.submit');
Route::get('/register/rhu', [RegisterController::class, 'showRhuForm'])->name('register.rhu');
Route::post('/register/rhu', [RegisterController::class, 'registerRhu'])->name('register.rhu.submit');

Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
Route::post('/events/store', [EventController::class, 'store'])->name('events.store');
Route::get('/events/{id}', [EventController::class, 'show'])->name('events.show');
Route::get('/events/{id}/edit', [EventController::class, 'edit'])->name('events.edit');
Route::put('/events/{id}', [EventController::class, 'update'])->name('events.update');
Route::post('/events/{id}/export-csv', [EventController::class, 'exportCsv'])->name('events.exportCsv');

// Inventory routes
Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
Route::put('/inventory/{id}', [InventoryController::class, 'update'])->name('inventory.update');
Route::delete('/inventory/{id}', [InventoryController::class, 'destroy'])->name('inventory.destroy');

// Other static pages
Route::get('/reports', fn() => view('pages.reports'))->name('reports.index');
Route::get('/schedules', fn() => view('pages.schedules'))->name('schedules.index');
Route::get('/personnel', fn() => view('pages.personnel'))->name('personnel.index');

// New feature pages
Route::get('/reports/verify', fn() => view('pages.reports_verify'))->name('reports.verify');
Route::get('/services', fn() => view('pages.services'))->name('services.index');
Route::get('/calendars', fn() => view('pages.calendars'))->name('calendars.index');
Route::get('/accounts', fn() => view('pages.accounts'))->name('accounts.index');
