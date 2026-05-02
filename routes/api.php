<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AstrologyController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\PaymentController;

use App\Http\Controllers\MarriageController;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::get('/astrology/details', [AstrologyController::class, 'getDetails']);
Route::get('/astrology/match', [AstrologyController::class, 'getMatch']);
Route::get('/astrology/chart', [AstrologyController::class, 'getChart']);
Route::get('/astrology/horoscope', [AstrologyController::class, 'getHoroscope']);

// Consultation Routes
Route::post('/consultation/voice-message', [ConsultationController::class, 'uploadVoiceMessage']);
Route::post('/consultation/call-token', [ConsultationController::class, 'generateCallToken']);
Route::get('/chat/history', [ConsultationController::class, 'getChatHistory']);
Route::post('/chat/save', [ConsultationController::class, 'saveMessage']);
Route::post('/consultation/end', [ConsultationController::class, 'endConsultation']);

// Payment Routes
Route::post('/payment/dummy', [PaymentController::class, 'dummy']);

// Marriage Prediction
Route::post('/marriage/predict', [MarriageController::class, 'predict']);

// AI Chat
Route::post('/ai/chat', [\App\Http\Controllers\AIController::class, 'chat']);

// Astrologers
Route::get('/astrologers', [\App\Http\Controllers\AstrologerController::class, 'index']);
Route::post('/astrologers', [\App\Http\Controllers\AstrologerController::class, 'store']);
Route::post('/astrologer/update-profile', [\App\Http\Controllers\AstrologerController::class, 'updateProfile']);
Route::get('/astrologer/stats', [\App\Http\Controllers\AstrologerController::class, 'getStats']);

// Settings & API Keys
Route::get('/settings', [\App\Http\Controllers\SettingController::class, 'getSetting']);
Route::post('/settings', [\App\Http\Controllers\SettingController::class, 'saveSetting']);
Route::get('/settings/public', [\App\Http\Controllers\SettingController::class, 'getPublicSettings']);
