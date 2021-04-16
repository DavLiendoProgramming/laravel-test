<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;

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

//Unprotected Routes
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::get('posts', [PostController::class, 'getAllPosts']);
Route::get('/posts/{slug}', [PostController::class, 'getUserPostBySlug']);

//Protected Routes
Route::group(['middleware' => ['jwt.verify']], function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('user', [AuthController::class, 'getUser']);
    Route::get('users', [AuthController::class, 'getUsers']);
    Route::delete('user', [AuthController::class, 'deleteUser']);

    //CRUD posts
    Route::post('create', [PostController::class, 'store']);
    Route::get('user/posts', [PostController::class, 'getUserPosts']);
    Route::put('user/posts/{id}', [PostController::class, 'update']);
    Route::delete('user/posts/{id}', [PostController::class, 'destroy']);
});
