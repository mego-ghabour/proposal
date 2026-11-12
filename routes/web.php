<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProposalDashboardController;
use App\Http\Controllers\PublicProposalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect root to Dashboard (which will prompt login if unauthenticated)
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Sales & Management AI Dashboard (Protected by auth middleware)
Route::middleware('auth')->prefix('dashboard')->group(function () {
    Route::get('/', [ProposalDashboardController::class, 'index'])->name('dashboard');

    // Dashboard REST APIs
    Route::prefix('api')->group(function () {
        Route::get('/proposals', [ProposalDashboardController::class, 'list']);
        Route::get('/templates', [ProposalDashboardController::class, 'templates']);
        Route::post('/analyze-idea', [ProposalDashboardController::class, 'analyzeIdea']);
        Route::post('/generate-proposal', [ProposalDashboardController::class, 'generateProposal']);
        Route::post('/proposals', [ProposalDashboardController::class, 'store']);
        Route::get('/proposals/{proposal}', [ProposalDashboardController::class, 'show']);
        Route::put('/proposals/{proposal}', [ProposalDashboardController::class, 'update']);
        Route::delete('/proposals/{proposal}', [ProposalDashboardController::class, 'destroy']);
        Route::post('/proposals/{proposal}/duplicate', [ProposalDashboardController::class, 'duplicate']);
        Route::post('/settings', [ProposalDashboardController::class, 'updateSettings']);
        Route::post('/test-gemini', [ProposalDashboardController::class, 'testGemini']);
        Route::post('/change-password', [AuthController::class, 'updatePassword']);
    });
});

// Client Public Shareable Proposal Views (No login needed)
Route::get('/p/{identifier}', [PublicProposalController::class, 'show'])->name('proposals.show');
Route::post('/p/{identifier}/accept', [PublicProposalController::class, 'accept'])->name('proposals.accept');
Route::get('/proposal/{identifier}', [PublicProposalController::class, 'show']);
