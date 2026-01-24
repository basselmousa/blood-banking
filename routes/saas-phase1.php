<?php

/**
 * SaaS Phase 1 Routes
 * 
 * Multi-tenancy, Subscriptions, RBAC, and core SaaS features
 * Routes: /app/*, /billing/*, /admin/tenants/*
 */

use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TenantController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    
    // ==========================
    // Tenant Management Routes
    // ==========================
    Route::prefix('admin/tenants')->name('tenants.')->middleware('can:manage_tenants')->group(function () {
        Route::get('/', [TenantController::class, 'index'])->name('index');
        Route::get('/create', [TenantController::class, 'create'])->name('create');
        Route::post('/', [TenantController::class, 'store'])->name('store');
        Route::get('/{tenant}', [TenantController::class, 'show'])->name('show');
        Route::get('/{tenant}/edit', [TenantController::class, 'edit'])->name('edit');
        Route::put('/{tenant}', [TenantController::class, 'update'])->name('update');
        Route::post('/{tenant}/activate', [TenantController::class, 'activate'])->name('activate');
        Route::post('/{tenant}/deactivate', [TenantController::class, 'deactivate'])->name('deactivate');
        Route::delete('/{tenant}', [TenantController::class, 'destroy'])->name('destroy');
    });

    // ==========================
    // Billing/Subscription Routes
    // ==========================
    Route::prefix('billing')->name('billing.')->group(function () {
        Route::get('/subscription', [SubscriptionController::class, 'show'])->name('subscription');
        Route::post('/subscription/upgrade', [SubscriptionController::class, 'upgrade'])->name('upgrade');
        Route::post('/subscription/downgrade', [SubscriptionController::class, 'downgrade'])->name('downgrade');
        Route::post('/subscription/cancel', [SubscriptionController::class, 'cancel'])->name('cancel');
        Route::post('/subscription/resume', [SubscriptionController::class, 'resume'])->name('resume');
    });
});
