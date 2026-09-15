<?php

use App\Http\Controllers\Admin\AdminCvController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminDocumentTypeController;
use App\Http\Controllers\Admin\AdminTemplateCategoryController;
use App\Http\Controllers\Admin\AdminTemplateController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\CvBuilderController;
use App\Http\Controllers\CvController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TemplateController;
use App\Services\TemplateService;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public Landing Page with Template Showcase
Route::get('/', function (TemplateService $templateService) {
    $categories = $templateService->getCategoriesWithTemplates();
    $templates = $templateService->getActiveTemplates();
    return view('welcome', compact('categories', 'templates'));
})->name('home');

// Public Template Routes
Route::get('/templates/{template}/preview', [TemplateController::class, 'preview'])->name('templates.preview');

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

    // Career Document Management Routes (CVs, Resumes, Cover Letters, Motivation Letters)
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
        Route::post('/{cv}/switch-template', [CvController::class, 'switchTemplate'])->name('switch-template');

        // Builder Routes (CV and Letter Split-Screen Workspace)
        Route::prefix('{cv}/builder')->name('builder.')->group(function () {
            Route::get('/', [CvBuilderController::class, 'show'])->name('show');
            Route::post('/personal-info', [CvBuilderController::class, 'savePersonalInfo'])->name('personal-info');
            Route::post('/summary', [CvBuilderController::class, 'saveSummary'])->name('summary');
            Route::post('/letter-details', [CvBuilderController::class, 'saveLetterDetails'])->name('letter-details');
            Route::post('/settings', [CvBuilderController::class, 'saveSettings'])->name('settings');
            Route::post('/autosave', [CvBuilderController::class, 'autosave'])->name('autosave');
            Route::get('/render-preview', [CvBuilderController::class, 'renderPreview'])->name('render-preview');
            Route::post('/items/{section}', [CvBuilderController::class, 'storeItem'])->name('items.store');
            Route::put('/items/{section}/{id}', [CvBuilderController::class, 'updateItem'])->name('items.update');
            Route::delete('/items/{section}/{id}', [CvBuilderController::class, 'deleteItem'])->name('items.destroy');
            Route::post('/items/{section}/reorder', [CvBuilderController::class, 'reorderItems'])->name('items.reorder');
            Route::post('/references/{id}/toggle-visibility', [CvBuilderController::class, 'toggleReferenceVisibility'])->name('references.toggle-visibility');
        });
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

        // All Documents Overview
        Route::get('/cvs', [AdminCvController::class, 'index'])->name('cvs.index');
        Route::get('/cvs/{cv}', [AdminCvController::class, 'show'])->name('cvs.show');

        // Document Types Management
        Route::resource('document-types', AdminDocumentTypeController::class)->except(['show']);
        Route::post('document-types/{documentType}/toggle-status', [AdminDocumentTypeController::class, 'toggleStatus'])->name('document-types.toggle-status');

        // Template Categories Management
        Route::prefix('templates')->name('templates.')->group(function () {
            Route::get('/categories', [AdminTemplateCategoryController::class, 'index'])->name('categories.index');
            Route::post('/categories', [AdminTemplateCategoryController::class, 'store'])->name('categories.store');
            Route::put('/categories/{category}', [AdminTemplateCategoryController::class, 'update'])->name('categories.update');
            Route::delete('/categories/{category}', [AdminTemplateCategoryController::class, 'destroy'])->name('categories.destroy');

            // Templates Management
            Route::get('/', [AdminTemplateController::class, 'index'])->name('index');
            Route::get('/create', [AdminTemplateController::class, 'create'])->name('create');
            Route::post('/', [AdminTemplateController::class, 'store'])->name('store');
            Route::get('/{template}/edit', [AdminTemplateController::class, 'edit'])->name('edit');
            Route::put('/{template}', [AdminTemplateController::class, 'update'])->name('update');
            Route::delete('/{template}', [AdminTemplateController::class, 'destroy'])->name('destroy');
            Route::post('/{template}/toggle-status', [AdminTemplateController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('/{template}/toggle-premium', [AdminTemplateController::class, 'togglePremium'])->name('toggle-premium');
        });
    });
});
