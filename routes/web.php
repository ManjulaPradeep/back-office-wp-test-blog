<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\WordPressAuthController;


Route::get('/', function () {return view('auth.login');});

Route::group(['prefix' => 'auth', 'as' => 'auth.'], function () {
    Route::get('login', function () { return view('auth.login');})->name('login');
    Route::get('login/wordpress', [WordPressAuthController::class, 'redirectToWP'])->name('login.wordpress');
    Route::get('login/callback', [WordPressAuthController::class, 'handleWPCallback'])->name('login.callback');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {return view('dashboard.index');})->name('dashboard');
});
