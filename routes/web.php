<?php

use App\Http\Controllers\AcademicAppController;
use Illuminate\Support\Facades\Route;

Route::get('/live-notas', [AcademicAppController::class, 'liveNotes'])->name('live.notes');

Route::middleware('guest')->group(function (): void {
    Route::get('/', [AcademicAppController::class, 'showAuth'])->name('auth');
    Route::get('/login', [AcademicAppController::class, 'showLogin'])->name('login.view');
    Route::get('/register', [AcademicAppController::class, 'showRegister'])->name('register.view');
    Route::post('/register', [AcademicAppController::class, 'register'])->name('register');
    Route::post('/login', [AcademicAppController::class, 'login'])->name('login');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', [AcademicAppController::class, 'dashboard'])->name('dashboard');
    Route::post('/logout', [AcademicAppController::class, 'logout'])->name('logout');
    Route::get('/schools/search', [AcademicAppController::class, 'searchSchools'])->name('schools.search');
    Route::post('/profile', [AcademicAppController::class, 'updateProfile'])->name('profile.update');
    Route::post('/calculations', [AcademicAppController::class, 'saveCalculation'])->name('calculations.save');
    Route::post('/simulations', [AcademicAppController::class, 'saveSimulation'])->name('simulations.save');
    Route::post('/ai-chat', [AcademicAppController::class, 'aiChat'])->name('ai.chat');
    Route::post('/request-enable', [AcademicAppController::class, 'requestEnable'])->name('request.enable');

    Route::get('/admin', [AcademicAppController::class, 'admin'])->name('admin');
    Route::post('/admin/users/{user}/toggle-follower', [AcademicAppController::class, 'toggleFollower'])->name('admin.toggleFollower');
    Route::get('/admin/requests/{enableRequest}', [AcademicAppController::class, 'showEnableRequest'])->name('admin.requests.show')->middleware('signed');
    Route::post('/admin/requests/{enableRequest}/approve', [AcademicAppController::class, 'approveEnableRequest'])->name('admin.requests.approve');
});
