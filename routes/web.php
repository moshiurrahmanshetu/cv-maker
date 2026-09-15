<?php

use App\Http\Controllers\Admin\AdminCvController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\CvController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public Landing Page
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');

    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

// Authenticated User Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // CV Management Routes
    Route::prefix('cvs')->name('cvs.')->group(function () {
        Route::get('/', [CvController::class, 'index'])->name('index');
        Route::get('/create', [CvController::class, 'create'])->name('create');
        Route::post('/', [CvController::class, 'store'])->name('store');
        Route::get('/{cv}', [CvController::class, 'show'])->name('show');
        Route::get('/{cv}/edit', [CvController::class, 'edit'])->name('edit');
        Route::put('/{cv}', [CvController::class, 'update'])->name('update');
        Route::delete('/{cv}', [CvController::class, 'destroy'])->name('destroy');
        Route::post('/{cv}/duplicate', [CvController::class, 'duplicate'])->name('duplicate');
        Route::post('/{cv}/toggle-status', [CvController::class, 'toggleStatus'])->name('toggle-status');
    });

    // Admin Panel Routes (Protected by Role Middleware)
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/', function () {
            return redirect()->route('admin.dashboard');
        });
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        
        // Users Management
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::post('/users/{user}/toggle-role', [AdminUserController::class, 'toggleRole'])->name('users.toggle-role');

        // All CVs Overview
        Route::get('/cvs', [AdminCvController::class, 'index'])->name('cvs.index');
        Route::get('/cvs/{cv}', [AdminCvController::class, 'show'])->name('cvs.show');
    });
});
