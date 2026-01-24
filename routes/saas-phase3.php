<?php

// Phase 3 Routes: Integrations & API

Route::middleware(['auth:sanctum', 'verified', 'tenant.resolution'])->group(function () {
    // Webhook Management
    Route::prefix('webhooks')->name('webhooks.')->group(function () {
        Route::get('/', [App\Http\Controllers\WebhookController::class, 'index'])->name('index')->middleware('can:view_reports');
        Route::post('/', [App\Http\Controllers\WebhookController::class, 'store'])->name('store')->middleware('can:manage_integrations');
        Route::get('{webhook}', [App\Http\Controllers\WebhookController::class, 'show'])->name('show')->middleware('can:view_reports');
        Route::put('{webhook}', [App\Http\Controllers\WebhookController::class, 'update'])->name('update')->middleware('can:manage_integrations');
        Route::delete('{webhook}', [App\Http\Controllers\WebhookController::class, 'delete'])->name('delete')->middleware('can:manage_integrations');
        Route::post('{webhook}/test', [App\Http\Controllers\WebhookController::class, 'test'])->name('test')->middleware('can:manage_integrations');
        Route::get('{webhook}/deliveries', [App\Http\Controllers\WebhookController::class, 'deliveryHistory'])->name('deliveryHistory')->middleware('can:view_reports');
        Route::post('retry-failed', [App\Http\Controllers\WebhookController::class, 'retryFailed'])->name('retryFailed')->middleware('can:manage_integrations');
    });

    // Integration Management
    Route::prefix('integrations')->name('integrations.')->group(function () {
        Route::get('/', [App\Http\Controllers\IntegrationController::class, 'index'])->name('index')->middleware('can:view_integrations');
        Route::post('/', [App\Http\Controllers\IntegrationController::class, 'store'])->name('store')->middleware('can:manage_integrations');
        Route::get('{integration}', [App\Http\Controllers\IntegrationController::class, 'show'])->name('show')->middleware('can:view_integrations');
        Route::put('{integration}', [App\Http\Controllers\IntegrationController::class, 'update'])->name('update')->middleware('can:manage_integrations');
        Route::delete('{integration}', [App\Http\Controllers\IntegrationController::class, 'delete'])->name('delete')->middleware('can:manage_integrations');
        Route::post('{integration}/test', [App\Http\Controllers\IntegrationController::class, 'test'])->name('test')->middleware('can:manage_integrations');
        Route::post('{integration}/toggle', [App\Http\Controllers\IntegrationController::class, 'toggle'])->name('toggle')->middleware('can:manage_integrations');
        Route::post('{integration}/sync', [App\Http\Controllers\IntegrationController::class, 'sync'])->name('sync')->middleware('can:manage_integrations');
    });

    // API Key Management
    Route::prefix('api-keys')->name('apiKeys.')->group(function () {
        Route::get('/', [App\Http\Controllers\ApiKeyController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\ApiKeyController::class, 'store'])->name('store');
        Route::get('{apiKey}', [App\Http\Controllers\ApiKeyController::class, 'show'])->name('show');
        Route::put('{apiKey}', [App\Http\Controllers\ApiKeyController::class, 'update'])->name('update');
        Route::post('{apiKey}/revoke', [App\Http\Controllers\ApiKeyController::class, 'revoke'])->name('revoke');
        Route::post('{apiKey}/regenerate', [App\Http\Controllers\ApiKeyController::class, 'regenerate'])->name('regenerate');
        Route::post('{apiKey}/permissions/grant', [App\Http\Controllers\ApiKeyController::class, 'grantPermission'])->name('grantPermission');
        Route::post('{apiKey}/permissions/revoke', [App\Http\Controllers\ApiKeyController::class, 'revokePermission'])->name('revokePermission');
    });

    // SMS Management
    Route::prefix('sms')->name('sms.')->group(function () {
        Route::get('/', [App\Http\Controllers\SmsController::class, 'index'])->name('index')->middleware('can:view_reports');
        Route::post('/send', [App\Http\Controllers\SmsController::class, 'send'])->name('send')->middleware('can:manage_integrations');
        Route::get('{message}', [App\Http\Controllers\SmsController::class, 'show'])->name('show')->middleware('can:view_reports');
        Route::post('{message}/resend', [App\Http\Controllers\SmsController::class, 'resend'])->name('resend')->middleware('can:manage_integrations');
        Route::get('statistics', [App\Http\Controllers\SmsController::class, 'statistics'])->name('statistics')->middleware('can:view_reports');
        Route::get('export', [App\Http\Controllers\SmsController::class, 'export'])->name('export')->middleware('can:view_reports');
    });

    // EHR Sync Management
    Route::prefix('ehr-syncs')->name('ehrSyncs.')->group(function () {
        Route::get('/', [App\Http\Controllers\EhrSyncController::class, 'index'])->name('index')->middleware('can:view_integrations');
        Route::post('/', [App\Http\Controllers\EhrSyncController::class, 'initializeSync'])->name('initialize')->middleware('can:manage_integrations');
        Route::get('{sync}', [App\Http\Controllers\EhrSyncController::class, 'show'])->name('show')->middleware('can:view_integrations');
        Route::post('{sync}/resolve-conflict', [App\Http\Controllers\EhrSyncController::class, 'resolveConflict'])->name('resolveConflict')->middleware('can:manage_integrations');
        Route::post('retry-failed', [App\Http\Controllers\EhrSyncController::class, 'resyncFailed'])->name('resyncFailed')->middleware('can:manage_integrations');
        Route::get('statistics', [App\Http\Controllers\EhrSyncController::class, 'statistics'])->name('statistics')->middleware('can:view_reports');
        Route::get('history', [App\Http\Controllers\EhrSyncController::class, 'history'])->name('history')->middleware('can:view_reports');
    });
});
