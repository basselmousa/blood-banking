<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\SaaS\CustomDomainController;
use App\Http\Controllers\API\SaaS\CustomBrandingController;
use App\Http\Controllers\API\SaaS\CustomFieldController;
use App\Http\Controllers\API\SaaS\EligibilityController;
use App\Http\Controllers\API\SaaS\WorkflowController;

Route::middleware(['auth:sanctum', 'tenant'])->group(function () {
    
    // Custom Domains API
    Route::apiResource('custom-domains', CustomDomainController::class);
    Route::post('custom-domains/{customDomain}/verify', [CustomDomainController::class, 'verify']);
    Route::get('custom-domains/{customDomain}/verification-records', [CustomDomainController::class, 'getVerificationRecords']);
    Route::post('custom-domains/{customDomain}/ssl/generate', [CustomDomainController::class, 'generateSsl']);
    Route::get('custom-domains/{customDomain}/ssl/expiration', [CustomDomainController::class, 'checkSslExpiration']);
    Route::post('custom-domains/{customDomain}/ssl/renew', [CustomDomainController::class, 'renewSsl']);
    Route::post('custom-domains/{customDomain}/activate', [CustomDomainController::class, 'activate']);
    Route::post('custom-domains/{customDomain}/deactivate', [CustomDomainController::class, 'deactivate']);
    Route::post('custom-domains/{customDomain}/set-primary', [CustomDomainController::class, 'setPrimary']);

    // Custom Branding API
    Route::get('custom-branding', [CustomBrandingController::class, 'show']);
    Route::put('custom-branding', [CustomBrandingController::class, 'update']);
    Route::post('custom-branding/logo', [CustomBrandingController::class, 'uploadLogo']);
    Route::post('custom-branding/favicon', [CustomBrandingController::class, 'uploadFavicon']);
    Route::get('custom-branding/theme/css', [CustomBrandingController::class, 'getThemeCss']);
    Route::get('custom-branding/theme/assets', [CustomBrandingController::class, 'getThemeAssets']);
    Route::get('custom-branding/preview', [CustomBrandingController::class, 'getPreview']);
    Route::post('custom-branding/email-template', [CustomBrandingController::class, 'setEmailTemplate']);
    Route::get('custom-branding/email-template', [CustomBrandingController::class, 'getEmailTemplate']);
    Route::post('custom-branding/hide-branding', [CustomBrandingController::class, 'hideAllBranding']);

    // Custom Fields API
    Route::apiResource('custom-fields', CustomFieldController::class);
    Route::post('custom-fields/validate', [CustomFieldController::class, 'validateValue']);
    Route::post('custom-fields/validate-bulk', [CustomFieldController::class, 'validateBulk']);
    Route::get('custom-fields/visible', [CustomFieldController::class, 'getVisibleFields']);
    Route::post('custom-fields/reorder', [CustomFieldController::class, 'reorder']);
    Route::post('custom-fields/bulk-create', [CustomFieldController::class, 'bulkCreate']);

    // Eligibility Templates API
    Route::apiResource('eligibility-templates', EligibilityController::class);
    Route::get('eligibility-templates/active', [EligibilityController::class, 'getActive']);
    Route::post('eligibility-templates/{eligibilityTemplate}/version', [EligibilityController::class, 'createVersion']);
    Route::post('eligibility-templates/{eligibilityTemplate}/set-default', [EligibilityController::class, 'setAsDefault']);
    Route::post('eligibility-templates/{eligibilityTemplate}/activate', [EligibilityController::class, 'setAsActive']);
    Route::post('eligibility-templates/evaluate/donor', [EligibilityController::class, 'evaluateDonor']);
    Route::post('eligibility-templates/{eligibilityTemplate}/test', [EligibilityController::class, 'testEvaluation']);
    Route::get('eligibility-templates/{eligibilityTemplate}/deferral-periods', [EligibilityController::class, 'getDefaultDeferralPeriods']);
    Route::post('eligibility-templates/create-defaults', [EligibilityController::class, 'createDefaults']);

    // Workflows API
    Route::apiResource('workflows', WorkflowController::class);
    Route::post('workflows/{workflow}/steps', [WorkflowController::class, 'addStep']);
    Route::put('workflows/{workflow}/steps/{stepId}', [WorkflowController::class, 'updateStep']);
    Route::delete('workflows/{workflow}/steps/{stepId}', [WorkflowController::class, 'removeStep']);
    Route::post('workflows/{workflow}/enable', [WorkflowController::class, 'enable']);
    Route::post('workflows/{workflow}/disable', [WorkflowController::class, 'disable']);
    Route::get('workflows/{workflow}/executions', [WorkflowController::class, 'getExecutionHistory']);
    Route::get('workflows/executions/{execution}', [WorkflowController::class, 'getExecution']);
    Route::post('workflows/executions/{execution}/retry', [WorkflowController::class, 'retryExecution']);
    Route::post('workflows/{workflow}/test', [WorkflowController::class, 'testWorkflow']);
    Route::post('workflows/trigger/manual', [WorkflowController::class, 'triggerManually']);
    Route::get('workflows/available/triggers', [WorkflowController::class, 'getAvailableTriggers']);
    Route::get('workflows/available/actions', [WorkflowController::class, 'getAvailableActions']);
});
