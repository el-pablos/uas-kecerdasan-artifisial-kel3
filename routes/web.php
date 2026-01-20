<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\LogAnalysisController;

/*
|--------------------------------------------------------------------------
| Web Routes - Log Sentinel
|--------------------------------------------------------------------------
|
| Definisi route untuk aplikasi Log Sentinel - Anomaly Detection System.
| Sistem ini mengintegrasikan Laravel dengan Python ML Service
| untuk mendeteksi aktivitas mencurigakan pada log server.
|
*/

// Authentication Routes
Auth::routes();

// Language Translation
Route::get('index/{locale}', [App\Http\Controllers\HomeController::class, 'lang']);

// ========================================
// LOG SENTINEL ROUTES (PROTECTED BY AUTH)
// ========================================

// Redirect root ke dashboard sentinel
Route::get('/', function () {
    return redirect()->route('sentinel.dashboard');
});

// Semua route Log Sentinel WAJIB login
Route::middleware(['auth'])->group(function () {
    // Dashboard Utama
    Route::get('/dashboard', [LogAnalysisController::class, 'dashboard'])
        ->name('sentinel.dashboard');

    // Halaman Daftar Log
    Route::get('/logs', [LogAnalysisController::class, 'logList'])
        ->name('sentinel.logs');

    // Halaman About
    Route::get('/about', [LogAnalysisController::class, 'about'])
        ->name('sentinel.about');
});

// ========================================
// API ENDPOINTS UNTUK AJAX
// ========================================

Route::prefix('api')->group(function () {
    // Endpoint untuk analisis log baru
    Route::post('/analyze', [LogAnalysisController::class, 'analyze'])
        ->name('api.analyze');

    // Endpoint untuk simulasi serangan
    Route::post('/simulate-attack', [LogAnalysisController::class, 'simulateAttack'])
        ->name('api.simulate');

    // Endpoint untuk mengambil log terbaru (polling)
    Route::get('/recent-logs', [LogAnalysisController::class, 'getRecentLogs'])
        ->name('api.recent-logs');

    // Endpoint untuk data chart
    Route::get('/chart-data', [LogAnalysisController::class, 'getChartData'])
        ->name('api.chart-data');

    // Endpoint untuk statistik dashboard
    Route::get('/stats', [LogAnalysisController::class, 'getStats'])
        ->name('api.stats');
});

// ========================================
// ROUTE UNTUK PROFILE (dari Velzon)
// ========================================

Route::post('/update-profile/{id}', [App\Http\Controllers\HomeController::class, 'updateProfile'])
    ->name('updateProfile');
Route::post('/update-password/{id}', [App\Http\Controllers\HomeController::class, 'updatePassword'])
    ->name('updatePassword');

// Fallback untuk halaman Velzon lainnya (jika diperlukan)
Route::get('{any}', [App\Http\Controllers\HomeController::class, 'index'])
    ->where('any', '^(?!api|dashboard|logs|about).*$')
    ->name('index');
