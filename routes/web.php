<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\WordPressAuthController;
use App\Http\Controllers\Api\PostController;


Route::get('/', function () {return view('auth.login');});

Route::group(['prefix' => 'auth', 'as' => 'auth.'], function () {
    Route::get('login', function () { return view('auth.login');})->name('login');
    Route::get('login/wordpress', [WordPressAuthController::class, 'redirectToWP'])->name('login.wordpress');
    Route::get('login/callback', [WordPressAuthController::class, 'handleWPCallback'])->name('login.callback');
    Route::post('/logout', [WordPressAuthController::class, 'logout'])->name('logout');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {return view('dashboard.index');})->name('dashboard');

    // Route::get('/posts', [PostController::class, 'index']);
    // Route::post('/posts/refresh', [PostController::class, 'refreshFromWp']);
    // Route::post('/posts', [PostController::class, 'store']);
    // Route::put('/posts/{wpId}', [PostController::class, 'update']);
    // Route::delete('/posts/{wpId}', [PostController::class, 'destroy']);

    Route::apiResource('posts', PostController::class);
    Route::patch('posts/{post}/priority', [PostController::class, 'updatePriority']);
    Route::post('posts/sync', [PostController::class, 'syncFromWordPress']);

});


