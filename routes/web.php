<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\PdfController; // Impor Controller
use App\Http\Controllers\RoleController;

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

Route::get('/', function () {
    return redirect('/dashboard');
})->middleware('auth');

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard')->middleware('auth');

Route::get('/tables', function () {
    return view('tables');
})->name('tables')->middleware('auth');

Route::get('/wallet', function () {
    return view('wallet');
})->name('wallet')->middleware('auth');

Route::get('/RTL', function () {
    return view('RTL');
})->name('RTL')->middleware('auth');

Route::get('/profile', function () {
    return view('account-pages.profile');
})->name('profile')->middleware('auth');

Route::get('/signin', function () {
    return view('account-pages.signin');
})->name('signin');

Route::get('/signup', function () {
    return view('account-pages.signup');
})->name('signup')->middleware('guest');

Route::get('/sign-up', [RegisterController::class, 'create'])
    ->middleware('guest')
    ->name('sign-up');

Route::post('/sign-up', [RegisterController::class, 'store'])
    ->middleware('guest');

Route::get('/sign-in', [LoginController::class, 'create'])
    ->middleware('guest')
    ->name('sign-in');

Route::post('/sign-in', [LoginController::class, 'store'])
    ->middleware('guest');

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])
    ->middleware('guest')
    ->name('password.request');

Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])
    ->middleware('guest')
    ->name('password.email');

Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])
    ->middleware('guest')
    ->name('password.reset');

Route::post('/reset-password', [ResetPasswordController::class, 'store'])
    ->middleware('guest');

// Route::get('/laravel-examples/user-profile', [ProfileController::class, 'index'])->name('users.profile')->middleware('auth');
// Route::put('/laravel-examples/user-profile/update', [ProfileController::class, 'update'])->name('users.update')->middleware('auth');
// Route::get('/laravel-examples/users-management', [UserController::class, 'index'])->name('users-management')->middleware('auth');

// Route::get('/test-pdf-corporate', [PdfController::class, 'generatePdf']);

// =========================================================
// GRUP ROUTE E-RAPOR: PENGATURAN SISTEM (Membutuhkan Spatie Permission)
// URL Prefix: /pengaturan
// =========================================================

// Middleware 'can:pengaturan-manage-users' memastikan hanya yang punya izin Admin/Operator yang bisa akses
Route::prefix('pengaturan')->middleware(['auth', 'can:pengaturan-manage-users'])->group(function () {
    
    // 1. ROLE MANAGEMENT
    // Route resource untuk Role (users.index, users.edit, users.update, etc.)
    // Nama Route: roles.index, roles.create, roles.edit, roles.update, roles.destroy
    Route::resource('roles', RoleController::class)->except(['show']);
    
    // 2. USER MANAGEMENT
    // Route resource untuk User (users.index, users.edit, users.update, etc.)
    // Nama Route: users.index, users.create, users.edit, users.update, users.destroy
    // Catatan: Jika Anda ingin menggunakan URL /users untuk list user, Anda harus mengganti nama users-management yang sudah ada
    Route::resource('users', UserController::class)->except(['show']);
    
});

// Catatan: Jika Anda ingin mempertahankan route lama:
// Route::get('/laravel-examples/users-management', [UserController::class, 'index'])->name('users-management')->middleware('auth');
// Maka Anda harus MENGHAPUS salah satu dari resource users atau mengganti nama route users.index menjadi users-management.index
// Untuk kesederhanaan, kita ganti yang lama:

Route::get('/laravel-examples/users-management', [UserController::class, 'index'])->name('users-management.index')->middleware('auth');
// DAN JANGAN MENGGUNAKAN Route::resource('users', ...) di atas, atau gunakan alias jika nama sudah terpakai.

// Karena kita menggunakan Route::resource('users', ...) di atas, kita ASUMSIKAN kita akan menggunakan users.index