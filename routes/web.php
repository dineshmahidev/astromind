<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;

Route::get('/', function () {
    return view('welcome');
});

// Public Website Routes
Route::get('/astrologers', [AdminController::class, 'publicAstrologers']);

// Admin Routes
Route::prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard']);
    Route::get('/users', [AdminController::class, 'users']);
    Route::post('/users', [AdminController::class, 'storeUser']);
    Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);
    Route::put('/users/{id}', [AdminController::class, 'updateUser']);
    Route::get('/astrologers', [AdminController::class, 'astrologers']);
    Route::post('/astrologers', [AdminController::class, 'storeAstrologer']);
    Route::put('/astrologers/{id}', [AdminController::class, 'updateAstrologer']);
    Route::get('/plans', [AdminController::class, 'plans']);
    Route::post('/plans', [AdminController::class, 'storePlan']);
    Route::get('/consultations', [AdminController::class, 'consultations']);
    Route::get('/payments', [AdminController::class, 'payments']);
    Route::get('/settings', [AdminController::class, 'settings']);
    Route::post('/settings', [AdminController::class, 'updateSettings']);
});

Route::get('/login', function () {
    return view('welcome'); // Or a dedicated login view
})->name('login');

// Expert (Astrologer/Palm Reader) Routes
Route::prefix('expert')->middleware(['auth'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\ExpertController::class, 'dashboard']);
    Route::get('/consultations', [\App\Http\Controllers\ExpertController::class, 'consultations']);
    Route::get('/profile', [\App\Http\Controllers\ExpertController::class, 'profile']);
    Route::post('/profile', [\App\Http\Controllers\ExpertController::class, 'updateProfile']);
});
