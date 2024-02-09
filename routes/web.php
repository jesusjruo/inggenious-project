<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FollowController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

//User related routes
Route::get('/' , [UserController::class , 'showCorrectHomepage'])->name('login');
Route::post('/register' , [UserController::class , 'register'])->middleware('guest');
Route::post('/login' , [UserController::class , 'login'])->middleware('guest');
Route::post('/logout' , [UserController::class , 'logout'])->middleware('mustBeLoggedIn');
Route::get('/manage-avatar' , [UserController::class , 'showAvatarForm'])->middleware('mustBeLoggedIn');
Route::post('/manage-avatar' , [UserController::class , 'storeAvatar'])->middleware('mustBeLoggedIn');

//Blog posts related routes
Route::get('/create-post' , [PostController::class , 'showCreateForm'])->middleware('mustBeLoggedIn');
Route::post('/create-post' , [PostController::class , 'createPost'])->middleware('mustBeLoggedIn');
Route::get('/post/{post}' , [PostController::class , 'viewSinglePost']);
Route::get('/post/{post}/edit' , [PostController::class , 'showEditForm'])->middleware('can:update,post');
Route::put('/post/{post}' , [PostController::class , 'updatePost'])->middleware('can:update,post');
Route::delete('/post/{post}' , [PostController::class , 'deletePost'])->middleware('can:delete,post');
Route::get('/search/{term}' , [PostController::class , 'search']);

//Profile related routes
Route::get('/profile/{user:username}' , [UserController::class , 'profile']);
Route::get('/profile/{user:username}/followers' , [UserController::class , 'profileFollowers']);
Route::get('/profile/{user:username}/following' , [UserController::class , 'profileFollowing']);

//Follow related routes
Route::post('/follow-user/{user:username}' , [FollowController::class , 'followUser'])->middleware('mustBeLoggedIn');
Route::post('/unfollow-user/{user:username}' , [FollowController::class , 'unfollowUser'])->middleware('mustBeLoggedIn');

//Gate example
Route::get('/admins-only' , function() {
    return 'Only admins should be able to see this page';
})->middleware('can:visitAdminPages');
