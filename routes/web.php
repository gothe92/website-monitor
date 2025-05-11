<?php

use App\Http\Controllers\MonitoringDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WebsiteController;
use App\Http\Controllers\CriticalEventsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::resource('websites', WebsiteController::class);
    Route::get('/monitoring', [MonitoringDashboardController::class, 'index'])->name('monitoring.dashboard');
    Route::get('/critical-events', [CriticalEventsController::class, 'index'])
        ->middleware(['auth'])
        ->name('critical-events.index');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
