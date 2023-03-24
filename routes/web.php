<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/



// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('test', function () {
    // abort(404);
    return view('welcome');

});

Route::get('/login', [UserController::class, 'login'])->name('user_login'); // ログイン画面
Route::get('/register', [UserController::class, 'register']); // 新規登録画面
Route::post('/user_register', [UserController::class, 'user_register']); // 新規登録処理
Route::post('/certification', [UserController::class, 'certification']); // ログイン認証
Route::post('/logout', [UserController::class, 'logout'])->name('user_logout'); // ログアウト処理
Route::get('/password_reset_form', [UserController::class, 'password_reset_form'])->name('password_reset_form'); // パスワードリセット画面




Route::group(['middleware' => 'auth'], function() {
    Route::get('/', [HomeController::class, 'index'])->name('home'); // フォルダを作成していない時の画面
    Route::get('/folders/{id}/tasks', [TaskController::class, 'index'])->name('tasks.index'); // 一覧
    Route::get('/folders/create', [FolderController::class, 'showCreateForm'])->name('folders.create'); // フォルダ作成画面
    Route::post('/folders/create', [FolderController::class, 'create']); // フォルダ作成処理
    Route::get('/folders/{id}/tasks/create', [TaskController::class, 'showCreateForm'])->name('tasks.create'); // タスク作成画面
    Route::post('/folders/{id}/tasks/create', [TaskController::class, 'create']); // タスク作成処理
    Route::get('/folders/{id}/tasks/{task_id}/edit', [TaskController::class, 'showEditForm'])->name('tasks.edit'); // タスク編集画面
    Route::post('/folders/{id}/tasks/{task_id}/edit', [TaskController::class, 'edit']); // タスク編集処理
});


// パスワードリセット関連
// Route::get('/forgot-password', function () {
//     return view('forgot-password');
// })->middleware('guest')->name('password.request');

Route::post('/forgot-password', [UserController::class, 'forgot_password']); // パスワードリセット処理

// パスワードリセットメール送信
Route::get('/reset-password/{token}', function ($token) {
    return view('auth/passwords/reset', ['token' => $token]);
})->middleware('guest')->name('password.reset');

Route::post('/reset_password', [UserController::class, 'reset_password'])->name('password.update'); // パスワードリセット処理