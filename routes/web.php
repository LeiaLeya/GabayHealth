<?php

use Illuminate\Support\Facades\Route;

Route::get('/reports', fn() => view('pages.reports'))->name('reports.index');
Route::get('/schedules', fn() => view('pages.schedules'))->name('schedules.index');
Route::get('/events', fn() => view('pages.events'))->name('events.index');
Route::get('/inventory', fn() => view('pages.inventory'))->name('inventory.index');
Route::get('/personnel', fn() => view('pages.personnel'))->name('personnel.index');
Route::get('/logout', fn() => redirect('/'))->name('logout');

