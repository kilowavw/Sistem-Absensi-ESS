<?php

use Illuminate\Support\Facades\Route;

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

use App\Http\Controllers\AuthController;

// Halaman login (GET)
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::get('/home', [AuthController::class, 'showLogin'])->name('login');
// Proses login (POST)
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

// Logout (POST)
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

use App\Http\Controllers\UserController;

// Dashboard User
Route::get('/dashboard', [UserController::class, 'dashboard'])->name('user.dashboard')->middleware('auth');

// Proses Clock In / Clock Out
Route::post('/attendance', [UserController::class, 'toggleAttendance'])->name('user.toggleAttendance');
Route::post('/submit-activity', [UserController::class, 'submitActivity'])->name('user.submitActivity');
Route::post('/cancel-clockout', [UserController::class, 'cancelClockOut'])->name('user.cancelClockOut');

Route::post('/auto-clockout', [UserController::class, 'autoClockOut'])->name('user.autoClockOut');

use App\Http\Controllers\AdminController;

// Admin Routes
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/users', [AdminController::class, 'manageUsers'])->name('admin.users');
    Route::get('/admin/attendance', [AdminController::class, 'attendanceHistory'])->name('admin.attendance');
    Route::get('/admin/users', [AdminController::class, 'manageUsers'])->name('admin.users');
    Route::post('/admin/users/storeOrUpdate', [AdminController::class, 'storeOrUpdateUser'])->name('admin.storeOrUpdateUser');
    Route::delete('/admin/users/delete/{id}', [AdminController::class, 'deleteUser'])->name('admin.deleteUser');
});
