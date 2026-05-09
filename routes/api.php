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
Route::get('/astrology/predict', [AstrologyController::class, 'getPrediction']);

// Consultation Routes
Route::post('/consultation/voice-message', [ConsultationController::class, 'uploadVoiceMessage']);
Route::post('/consultation/image-message', [ConsultationController::class, 'uploadImage']);
Route::post('/consultation/call-token', [ConsultationController::class, 'generateCallToken']);
Route::get('/consultation/messages', [ConsultationController::class, 'getConsultationMessages']);
Route::get('/consultation/detail', [ConsultationController::class, 'getConsultationDetail']);
Route::get('/chat/history', [ConsultationController::class, 'getChatHistory']);
Route::post('/chat/save', [ConsultationController::class, 'saveMessage']);
Route::post('/consultation/end', [ConsultationController::class, 'endConsultation']);
Route::post('/consultation/send', [ConsultationController::class, 'startConsultation']);
Route::post('/consultation/finish', [ConsultationController::class, 'finishConsultation']);
Route::get('/user/history', [ConsultationController::class, 'getUserHistory']);

// Payment Routes
Route::post('/payment/dummy', [PaymentController::class, 'dummy']);

// Marriage Prediction
Route::post('/marriage/predict', [MarriageController::class, 'predict']);

// AI Chat
Route::post('/ai/chat', [\App\Http\Controllers\AIController::class, 'chat']);

// Astrologers
Route::get('/astrologers', [\App\Http\Controllers\AstrologerController::class, 'index']);
Route::get('/astrologers/{id}', [\App\Http\Controllers\AstrologerController::class, 'show']);
Route::post('/astrologers', [\App\Http\Controllers\AstrologerController::class, 'store']);
Route::post('/astrologer/update-profile', [\App\Http\Controllers\AstrologerController::class, 'updateProfile']);
Route::get('/astrologer/stats', [\App\Http\Controllers\AstrologerController::class, 'getStats']);
Route::get('/astrologer/dashboard', [\App\Http\Controllers\AstrologerController::class, 'getDashboardData']);
Route::get('/astrologer/consultations', [\App\Http\Controllers\AstrologerController::class, 'getConsultations']);
Route::get('/astrologer/wallet', [\App\Http\Controllers\AstrologerController::class, 'getWallet']);
Route::post('/astrologer/withdraw', [\App\Http\Controllers\AstrologerController::class, 'requestWithdrawal']);

// Settings & API Keys
Route::get('/settings', [\App\Http\Controllers\SettingController::class, 'getSetting']);
Route::post('/settings', [\App\Http\Controllers\SettingController::class, 'saveSetting']);
Route::get('/settings/public', [\App\Http\Controllers\SettingController::class, 'getPublicSettings']);
Route::get('/settings/zego', [\App\Http\Controllers\SettingController::class, 'getZegoConfig']);
