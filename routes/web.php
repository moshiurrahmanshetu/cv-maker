<?php

use App\Http\Controllers\Admin\AdminCvController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminDocumentTypeController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminTemplateCategoryController;
use App\Http\Controllers\Admin\AdminTemplateController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\AtsAnalyzerController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\CheckoutController;
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
        Route::get('/{cv}/pdf', [CvController::class, 'downloadPdf'])->name('pdf');
        Route::get('/{cv}/pdf/preview', [CvController::class, 'previewPdf'])->name('pdf.preview');

        // Phase 10: ATS Analyzer & Job Matching Routes
        Route::prefix('{cv}/ats')->name('ats.')->group(function () {
            Route::get('/', [AtsAnalyzerController::class, 'show'])->name('show');
            Route::post('/analyze', [AtsAnalyzerController::class, 'analyze'])->name('analyze');
            Route::post('/match-job', [AtsAnalyzerController::class, 'matchJob'])->name('match-job');
            Route::delete('/job', [AtsAnalyzerController::class, 'clearJob'])->name('clear-job');
        });

        // Builder Routes (CV and Letter Split-Screen Workspace)
        Route::prefix('{cv}/builder')->name('builder.')->group(function () {
            Route::get('/', [CvBuilderController::class, 'show'])->name('show');
            Route::get('/pdf', [CvBuilderController::class, 'downloadPdf'])->name('pdf');
            Route::post('/personal-info', [CvBuilderController::class, 'savePersonalInfo'])->name('personal-info');
            Route::post('/summary', [CvBuilderController::class, 'saveSummary'])->name('summary');
            Route::post('/letter-details', [CvBuilderController::class, 'saveLetterDetails'])->name('letter-details');
            Route::post('/settings', [CvBuilderController::class, 'saveSettings'])->name('settings');
            Route::post('/autosave', [CvBuilderController::class, 'autosave'])->name('autosave');
            Route::get('/render-preview', [CvBuilderController::class, 'renderPreview'])->name('render-preview');
            Route::post('/items/{section}', [CvBuilderController::class, 'storeItem'])->name('items.store');
            Route::post('/items/{section}/batch', [CvBuilderController::class, 'saveSectionBatch'])->name('items.batch');
            Route::put('/items/{section}/{id}', [CvBuilderController::class, 'updateItem'])->name('items.update');
            Route::delete('/items/{section}/{id}', [CvBuilderController::class, 'deleteItem'])->name('items.destroy');
            Route::post('/items/{section}/reorder', [CvBuilderController::class, 'reorderItems'])->name('items.reorder');
            Route::post('/references/{id}/toggle-visibility', [CvBuilderController::class, 'toggleReferenceVisibility'])->name('references.toggle-visibility');

            // Phase 5: AI Career Assistant Endpoints (Protected with 30 req/min throttle)
            Route::prefix('ai')->name('ai.')->middleware('throttle:30,1')->group(function () {
                Route::post('/summary', [\App\Http\Controllers\AiAssistantController::class, 'generateSummary'])->name('summary');
                Route::post('/objective', [\App\Http\Controllers\AiAssistantController::class, 'generateObjective'])->name('objective');
                Route::post('/experience', [\App\Http\Controllers\AiAssistantController::class, 'rewriteExperience'])->name('experience');
                Route::post('/project', [\App\Http\Controllers\AiAssistantController::class, 'rewriteProject'])->name('project');
                Route::post('/skills', [\App\Http\Controllers\AiAssistantController::class, 'suggestSkills'])->name('skills');
                Route::post('/skills/append', [\App\Http\Controllers\AiAssistantController::class, 'appendSkills'])->name('skills.append');
                Route::post('/improve', [\App\Http\Controllers\AiAssistantController::class, 'improveContent'])->name('improve');
                Route::post('/cover-letter', [\App\Http\Controllers\AiAssistantController::class, 'generateCoverLetter'])->name('cover-letter');
                Route::post('/motivation-letter', [\App\Http\Controllers\AiAssistantController::class, 'generateMotivationLetter'])->name('motivation-letter');
            });
        });
    });

    // Phase 11: Checkout & Payment Flow Routes
    Route::prefix('checkout')->name('checkout.')->group(function () {
        Route::get('/template/{template}', [CheckoutController::class, 'showTemplate'])->name('template');
        Route::post('/process', [CheckoutController::class, 'process'])->name('process');
        Route::get('/callback/{order}', [CheckoutController::class, 'callback'])->name('callback');
        Route::get('/success/{order}', [CheckoutController::class, 'success'])->name('success');
        Route::get('/failed/{order}', [CheckoutController::class, 'failed'])->name('failed');
    });

    // Phase 11: Billing & Purchase History Routes
    Route::prefix('billing')->name('billing.')->group(function () {
        Route::get('/', [BillingController::class, 'index'])->name('index');
        Route::get('/orders/{order}', [BillingController::class, 'showOrder'])->name('orders.show');
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

        // Phase 5: AI Usage & Telemetry Management
        Route::get('/ai', [\App\Http\Controllers\Admin\AdminAiController::class, 'index'])->name('ai.index');

        // Phase 11: Orders & Payment Transactions Management
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [AdminOrderController::class, 'index'])->name('index');
            Route::get('/{order}', [AdminOrderController::class, 'show'])->name('show');
        });

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

// Phase 11: Payment Gateway Webhook Endpoint (Excluded from CSRF)
Route::post('/checkout/webhook/{gateway}', [CheckoutController::class, 'webhook'])->name('checkout.webhook');
