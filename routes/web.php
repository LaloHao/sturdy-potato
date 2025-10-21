<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DecisionController;
use App\Http\Controllers\VoteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CommentController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Decisions routes
    Route::get('/decisions', function () {
        return Inertia::render('Decisions/Index');
    })->name('decisions.index');

    Route::get('/decisions/create', function () {
        return Inertia::render('Decisions/Create');
    })->name('decisions.create');

    Route::get('/decisions/{id}', function ($id) {
        return Inertia::render('Decisions/Show', ['id' => $id]);
    })->name('decisions.show');

    Route::get('/my-decisions', function () {
        return Inertia::render('Decisions/MyDecisions');
    })->name('decisions.mine');

    Route::get('/voted-decisions', function () {
        return Inertia::render('Decisions/VotedDecisions');
    })->name('decisions.voted');

    // API routes for authenticated users
    Route::prefix('api')->group(function () {
        Route::apiResource('decisions', DecisionController::class);
        Route::get('my-decisions', [DecisionController::class, 'myDecisions']);
        Route::get('voted-decisions', [DecisionController::class, 'votedDecisions']);
        Route::post('votes', [VoteController::class, 'store']);
        Route::delete('votes/{id}', [VoteController::class, 'destroy']);

        // Comments routes for authenticated users
        Route::post('decisions/{decision}/comments', [CommentController::class, 'store']);
        Route::put('decisions/{decision}/comments/{comment}', [CommentController::class, 'update']);
        Route::delete('decisions/{decision}/comments/{comment}', [CommentController::class, 'destroy']);
    });
});

// Public API routes
Route::prefix('api')->group(function () {
    Route::get('decisions', [DecisionController::class, 'index']);
    Route::get('decisions/{id}', [DecisionController::class, 'show']);
    // Comments public route
    Route::get('decisions/{decision}/comments', [CommentController::class, 'index']);
});

require __DIR__.'/auth.php';
