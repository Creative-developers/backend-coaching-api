<?php

use App\Http\Controllers\AIPrompt\AIPromptController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientSessionController;
use App\Http\Controllers\CoachingSessionController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('client', ClientController::class);
    Route::apiResource('coaching-sessions', CoachingSessionController::class);

    Route::get('/client/sessions/pending', [ClientSessionController::class, 'pendingSessions'])
    ->name('client.sessions.pending');

    Route::put('/client/sessions/{coachingSession}', [ClientSessionController::class, 'updateSession'])
        ->name('client.sessions.update');

    Route::get('analytics', [AnalyticsController::class, 'fetchAnalytics']);
});

#For AI Prompt part
Route::post('/ai-prompt', [AIPromptController::class, 'processAiPrompt']);
