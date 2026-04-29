<?php

use App\Http\Controllers\FollowController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PostCommentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostLikeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TagController;
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

Route::get('/tags', [TagController::class, 'index'])
    ->middleware('throttle:120,1')
    ->name('tags.index');

Route::get('/tags/search', [TagController::class, 'search'])
    ->middleware('throttle:90,1')
    ->name('tags.search');

Route::get('/posts/filter', [WallController::class, 'filter'])
    ->middleware('throttle:120,1')
    ->name('posts.filter');

// GET /posts?search=… → muro con búsqueda (alineado con descubrimiento tipo red social).
Route::get('/posts', function (\Illuminate\Http\Request $request) {
    if (! auth()->check()) {
        return redirect()->route('welcome');
    }
    $search = $request->query('search');
    if (is_string($search) && trim($search) !== '') {
        return redirect()->route('dashboard', ['search' => $search]);
    }

    return redirect()->route('dashboard');
})->name('posts.index');

Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');

Route::post('/posts/{post}/comments', [PostCommentController::class, 'store'])
    ->middleware(['auth', 'throttle:30,1'])
    ->name('posts.comments.store');

Route::post('/posts/{post}/likes/toggle', [PostLikeController::class, 'toggle'])
    ->middleware(['auth', 'throttle:120,1'])
    ->name('posts.likes.toggle');

Route::post('/posts', [PostController::class, 'store'])
    ->middleware('auth')
    ->name('posts.store');

Route::get('/username/availability', UsernameAvailabilityController::class)
    ->middleware('throttle:30,1')
    ->name('username.availability');

Route::get('/user/{username}', function (string $username) {
    return redirect()->route('profile.show', ['username' => $username], 301);
})->where('username', '[a-z0-9_-]+');

Route::get('/profile/{username}', [UserController::class, 'show'])
    ->where('username', '[a-z0-9_-]+')
    ->name('profile.show');

Route::get('/users/{username}/posts', [UserController::class, 'posts'])
    ->where('username', '[a-z0-9_-]+')
    ->middleware('throttle:120,1')
    ->name('users.posts.index');

Route::get('/dashboard', [WallController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard');

Route::middleware(['auth', 'throttle:120,1'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read_all');
});

Route::middleware('auth')->group(function () {
    Route::post('/users/{username}/follow', [FollowController::class, 'store'])
        ->where('username', '[a-z0-9_-]+')
        ->name('users.follow.store');
    Route::delete('/users/{username}/follow', [FollowController::class, 'destroy'])
        ->where('username', '[a-z0-9_-]+')
        ->name('users.follow.destroy');

    Route::get('/profile', function () {
        return redirect()->route('settings.profile');
    })->name('profile.redirect');

    Route::get('/settings/profile', [ProfileController::class, 'edit'])->name('settings.profile');
    Route::patch('/settings/profile', [ProfileController::class, 'update'])->name('settings.profile.update');
    Route::delete('/settings/profile', [ProfileController::class, 'destroy'])->name('settings.profile.destroy');
    Route::get('/settings/account', [ProfileController::class, 'account'])->name('settings.account');
});

require __DIR__.'/auth.php';
