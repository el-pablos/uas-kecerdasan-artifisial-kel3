<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FeedbackController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| Human-in-the-Loop Feedback Routes
|--------------------------------------------------------------------------
| Routes for Active Learning & Analyst Feedback System
| Lead Developer: Muhammad Akbar Hadi Pratama (@el-pablos)
*/

Route::prefix('feedback')->group(function () {
    Route::post('/', [FeedbackController::class, 'submitFeedback'])->name('api.feedback.submit');
    Route::get('/stats', [FeedbackController::class, 'getStats'])->name('api.feedback.stats');
});

Route::prefix('whitelist')->group(function () {
    Route::post('/add', [FeedbackController::class, 'addToWhitelist'])->name('api.whitelist.add');
    Route::post('/remove', [FeedbackController::class, 'removeFromWhitelist'])->name('api.whitelist.remove');
});
