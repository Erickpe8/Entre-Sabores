<?php

use App\Http\Controllers\PostCommentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UsernameAvailabilityController;
use App\Http\Controllers\WallController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('welcome');
})->name('home');

Route::get('/welcome', function () {
    return view('welcome');
})->name('welcome');

Route::get('/posts/filter', [WallController::class, 'filter'])
    ->middleware('throttle:120,1')
    ->name('posts.filter');

Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');

Route::post('/posts/{post}/comments', [PostCommentController::class, 'store'])
    ->middleware('auth')
    ->name('posts.comments.store');

Route::patch('/posts/{post}/country', [PostController::class, 'updateCountry'])
    ->middleware('auth')
    ->name('posts.country.update');

Route::get('/username/availability', UsernameAvailabilityController::class)
    ->middleware('throttle:30,1')
    ->name('username.availability');

Route::get('/user/{username}', [UserController::class, 'show'])
    ->where('username', '[a-z0-9_-]+')
    ->name('user.profile');

Route::get('/dashboard', [WallController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
