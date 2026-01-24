<?php

/**
 * SaaS Phase 2 Routes
 * 
 * Advanced features: Donor portal, appointments, inventory, analytics
 * Routes: /appointments/*, /inventory/*, /analytics/*, /donor-portal/*
 */

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\DonorPortalController;
use App\Http\Controllers\InventoryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {

    // ==========================
    // Appointment Management Routes
    // ==========================
    Route::prefix('appointments')->name('appointments.')->group(function () {
        // Public/Donor endpoints
        Route::post('/available-slots', [AppointmentController::class, 'availableSlots'])->name('available-slots');
        Route::post('/book', [AppointmentController::class, 'book'])->name('book');

        // Staff/Admin endpoints
        Route::get('/', [AppointmentController::class, 'index'])->name('index')->middleware('can:view_appointment');
        Route::get('/{appointment}', [AppointmentController::class, 'show'])->name('show')->middleware('can:view_appointment');
        Route::put('/{appointment}', [AppointmentController::class, 'update'])->name('update')->middleware('can:edit_appointment');
        Route::post('/{appointment}/complete', [AppointmentController::class, 'complete'])->name('complete')->middleware('can:record_donation');
        Route::post('/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('cancel')->middleware('can:edit_appointment');
        Route::get('/statistics/overview', [AppointmentController::class, 'statistics'])->name('statistics')->middleware('can:view_reports');
        Route::get('/export/list', [AppointmentController::class, 'export'])->name('export')->middleware('can:view_reports');
    });

    // ==========================
    // Inventory Management Routes
    // ==========================
    Route::prefix('inventory')->name('inventory.')->middleware('can:manage_inventory')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('index');
        Route::get('/{inventory}', [InventoryController::class, 'show'])->name('show');
        Route::put('/{inventory}', [InventoryController::class, 'update'])->name('update');
        Route::post('/{inventory}/add', [InventoryController::class, 'add'])->name('add');
        Route::post('/{inventory}/remove', [InventoryController::class, 'remove'])->name('remove');
        Route::post('/{inventory}/waste', [InventoryController::class, 'waste'])->name('waste');
        Route::get('/summary/overview', [InventoryController::class, 'summary'])->name('summary');
        Route::get('/alerts/list', [InventoryController::class, 'alerts'])->name('alerts');
        Route::get('/low-stock/items', [InventoryController::class, 'lowStock'])->name('low-stock');
        Route::get('/expiring/items', [InventoryController::class, 'expiringSoon'])->name('expiring');
        Route::get('/export/report', [InventoryController::class, 'export'])->name('export');
    });

    // ==========================
    // Analytics & Reporting Routes
    // ==========================
    Route::prefix('analytics')->name('analytics.')->middleware('can:view_reports')->group(function () {
        Route::get('/dashboard', [AnalyticsController::class, 'dashboard'])->name('dashboard');
        Route::get('/donors', [AnalyticsController::class, 'donors'])->name('donors');
        Route::get('/donations', [AnalyticsController::class, 'donations'])->name('donations');
        Route::get('/inventory', [AnalyticsController::class, 'inventory'])->name('inventory');
        Route::get('/appointments', [AnalyticsController::class, 'appointments'])->name('appointments');
        Route::get('/revenue', [AnalyticsController::class, 'revenue'])->name('revenue');
        Route::get('/realtime', [AnalyticsController::class, 'realtime'])->name('realtime');
        Route::get('/export', [AnalyticsController::class, 'export'])->name('export');
    });

    // ==========================
    // Donor Portal Routes
    // ==========================
    Route::prefix('donor-portal')->name('donor-portal.')->group(function () {
        // Settings (admin only)
        Route::get('/settings', [DonorPortalController::class, 'settings'])->name('settings')->middleware('can:manage_users');
        Route::post('/settings', [DonorPortalController::class, 'updateSettings'])->name('update-settings')->middleware('can:manage_users');
        Route::post('/settings/enable-feature', [DonorPortalController::class, 'enableFeature'])->name('enable-feature')->middleware('can:manage_users');
        Route::post('/settings/disable-feature', [DonorPortalController::class, 'disableFeature'])->name('disable-feature')->middleware('can:manage_users');

        // Portal view (donor self-service)
        Route::get('/', [DonorPortalController::class, 'view'])->name('view');
    });

});
