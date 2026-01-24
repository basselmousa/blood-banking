# Phase 4: White-Labeling & Customization - Implementation Guide

## Overview

Phase 4 transforms the blood banking SaaS platform into a fully customizable, white-label solution enabling enterprise customers to brand the system as their own and automate complex business logic through workflow automation.

**Phase 4 Objectives:**
- ✅ Enable white-labeling with custom domains, branding, and CSS
- ✅ Implement dynamic custom fields system
- ✅ Create configurable eligibility criteria templates
- ✅ Build workflow automation engine with event-triggered actions
- ✅ Support multi-tenant isolation with full customization

**Technology Stack:**
- Laravel 8.0+, PHP 7.4+
- MySQL 5.7+
- Bootstrap 5, Blade templating
- Redis queue for workflow processing
- Storage for media assets (logos, favicons)

---

## Database Schema

### 1. Custom Domains Table

Manages per-tenant custom domain routing with SSL and DNS verification support.

```php
Schema::create('custom_domains', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('tenant_id');
    $table->string('domain')->unique();
    $table->string('base_domain'); // e.g., example.com from www.example.com
    $table->boolean('is_primary')->default(false);
    $table->boolean('is_verified')->default(false);
    $table->string('verification_token')->nullable();
    $table->timestamp('verified_at')->nullable();
    $table->text('ssl_certificate')->nullable();
    $table->text('ssl_key')->nullable();
    $table->timestamp('ssl_expires_at')->nullable();
    $table->boolean('is_active')->default(false);
    $table->json('dns_records')->nullable();
    $table->timestamps();
    
    $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
    $table->index(['tenant_id', 'is_active']);
});
```

### 2. Custom Branding Table

Stores all branding customization: colors, fonts, logos, CSS, HTML, and email templates.

```php
Schema::create('custom_branding', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('tenant_id')->unique();
    $table->string('logo_path')->nullable();
    $table->string('favicon_path')->nullable();
    $table->string('primary_color')->default('#FF6B6B');
    $table->string('secondary_color')->default('#4ECDC4');
    $table->string('accent_color')->default('#45B7D1');
    $table->string('text_color')->default('#2C3E50');
    $table->string('background_color')->default('#FFFFFF');
    $table->string('font_family')->default('Segoe UI, Tahoma, Geneva, Verdana, sans-serif');
    $table->longText('custom_css')->nullable();
    $table->longText('custom_html_header')->nullable();
    $table->longText('custom_html_footer')->nullable();
    $table->string('company_name')->nullable();
    $table->text('company_description')->nullable();
    $table->string('support_email')->nullable();
    $table->string('support_phone')->nullable();
    $table->string('privacy_policy_url')->nullable();
    $table->string('terms_of_service_url')->nullable();
    $table->boolean('hide_saas_branding')->default(false);
    $table->boolean('show_powered_by')->default(true);
    $table->json('email_templates')->nullable();
    $table->json('report_branding')->nullable();
    $table->timestamps();
    
    $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
});
```

### 3. Custom Fields Table

Enables tenants to define custom fields for any entity type with validation rules.

```php
Schema::create('custom_fields', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('tenant_id');
    $table->enum('entity_type', ['donor', 'donation', 'appointment', 'patient']);
    $table->string('field_name');
    $table->string('field_label');
    $table->enum('field_type', [
        'text', 'email', 'phone', 'select', 'multiselect',
        'date', 'datetime', 'textarea', 'checkbox', 'radio',
        'url', 'number'
    ]);
    $table->json('field_options')->nullable();
    $table->json('validation_rules')->nullable();
    $table->text('default_value')->nullable();
    $table->boolean('is_required')->default(false);
    $table->boolean('is_visible')->default(true);
    $table->boolean('is_searchable')->default(false);
    $table->integer('sort_order')->default(0);
    $table->json('conditional_logic')->nullable();
    $table->text('help_text')->nullable();
    $table->timestamps();
    
    $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
    $table->unique(['tenant_id', 'entity_type', 'field_name']);
    $table->index(['tenant_id', 'entity_type', 'is_visible']);
});
```

### 4. Eligibility Templates Table

Stores customizable eligibility criteria for blood donors with versioning support.

```php
Schema::create('eligibility_templates', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('tenant_id');
    $table->string('name');
    $table->text('description')->nullable();
    $table->json('criteria')->nullable();
    $table->json('age_range')->nullable(); // {min, max}
    $table->json('hemoglobin_levels')->nullable(); // {min_male, min_female}
    $table->decimal('weight_limit', 8, 2)->nullable();
    $table->json('blood_pressure')->nullable(); // {systolic_max, diastolic_max}
    $table->json('custom_questions')->nullable();
    $table->json('deferral_periods')->nullable(); // {period_type: days}
    $table->json('medications_to_defer')->nullable();
    $table->json('conditions_to_defer')->nullable();
    $table->boolean('is_default')->default(false);
    $table->boolean('is_active')->default(true);
    $table->integer('version')->default(1);
    $table->timestamps();
    
    $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
    $table->index(['tenant_id', 'is_active', 'version']);
    $table->index(['tenant_id', 'is_default']);
});
```

### 5. Workflows Table

Defines automation workflows with event triggers and conditions.

```php
Schema::create('workflows', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('tenant_id');
    $table->string('name');
    $table->text('description')->nullable();
    $table->enum('trigger_type', [
        'donor.created', 'donor.updated', 'donation.recorded', 'donation.completed',
        'appointment.scheduled', 'appointment.completed', 'patient.created',
        'eligibility.failed', 'deferral.created', 'manual_trigger'
    ]);
    $table->json('trigger_conditions')->nullable();
    $table->boolean('is_active')->default(true);
    $table->integer('execution_order')->default(0);
    $table->timestamps();
    
    $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
    $table->index(['tenant_id', 'trigger_type', 'is_active']);
});
```

### 6. Workflow Steps Table

Individual actions/steps within a workflow with conditional logic and delays.

```php
Schema::create('workflow_steps', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('tenant_id');
    $table->unsignedBigInteger('workflow_id');
    $table->integer('step_number');
    $table->enum('action_type', [
        'send_email', 'send_sms', 'create_task', 'defer_donor',
        'update_field', 'create_notification', 'trigger_webhook',
        'call_api', 'add_tag', 'remove_tag', 'change_status'
    ]);
    $table->json('action_config');
    $table->json('conditions')->nullable();
    $table->boolean('is_conditional')->default(false);
    $table->integer('wait_hours')->default(0);
    $table->timestamps();
    
    $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
    $table->foreign('workflow_id')->references('id')->on('workflows')->onDelete('cascade');
    $table->unique(['workflow_id', 'step_number']);
    $table->index(['tenant_id', 'workflow_id']);
});
```

### 7. Workflow Executions Table

Tracks execution history, progress, and results of workflow runs.

```php
Schema::create('workflow_executions', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('tenant_id');
    $table->unsignedBigInteger('workflow_id');
    $table->string('entity_type');
    $table->unsignedBigInteger('entity_id');
    $table->enum('status', ['pending', 'running', 'completed', 'failed', 'paused'])->default('pending');
    $table->json('completed_steps')->nullable();
    $table->text('error_message')->nullable();
    $table->json('execution_log')->nullable();
    $table->timestamp('started_at')->nullable();
    $table->timestamp('completed_at')->nullable();
    $table->timestamps();
    
    $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
    $table->foreign('workflow_id')->references('id')->on('workflows')->onDelete('cascade');
    $table->index(['tenant_id', 'workflow_id', 'status']);
    $table->index(['entity_type', 'entity_id']);
});
```

---

## Models

### CustomDomain Model

```php
class CustomDomain extends Model {
    use BelongsToTenant;
    
    // Scopes
    scopePrimary() - Get primary domain
    scopeVerified() - Get verified domains
    scopeActive() - Get active domains
    
    // Methods
    verify($token) - Verify domain with token
    getVerificationDnsRecords() - Get CNAME records for verification
    isSslExpiringSoon($days = 30) - Check SSL expiration
}
```

### CustomBranding Model

```php
class CustomBranding extends Model {
    use BelongsToTenant;
    
    // Methods
    getThemeAssets() - Return organized theme assets
    generateThemeCss() - Generate CSS from branding colors
    getEmailTemplate($name, $data) - Get templated email content
}
```

### CustomField Model

```php
class CustomField extends Model {
    use BelongsToTenant;
    
    // Constants
    FIELD_TYPES - Available field types (text, email, select, etc.)
    ENTITY_TYPES - Entity types (donor, donation, appointment, patient)
    
    // Methods
    validate($value) - Validate field value
    shouldDisplay($parentData) - Check conditional visibility
    
    // Scopes
    forEntity($entityType) - Get visible fields for entity
    searchable() - Get searchable fields
}
```

### EligibilityTemplate Model

```php
class EligibilityTemplate extends Model {
    use BelongsToTenant;
    
    // Methods
    evaluateDonor($donor) - Evaluate donor eligibility
    getDeferralPeriod($type) - Get deferral period in days
    clone() - Create new version of template
    
    // Scopes
    default() - Get default template
    active() - Get active templates
    latestVersion() - Get latest version of template
}
```

### Workflow Model

```php
class Workflow extends Model {
    use BelongsToTenant;
    
    // Constants
    TRIGGER_TYPES - Available trigger events
    
    // Relationships
    steps() - Has many WorkflowStep
    executions() - Has many WorkflowExecution
    
    // Methods
    shouldTrigger($entity, $triggerType) - Evaluate trigger conditions
    execute($entity, $triggerType) - Create execution record
    
    // Scopes
    active() - Get active workflows
    forTrigger($triggerType) - Get workflows for trigger type
}
```

### WorkflowStep Model

```php
class WorkflowStep extends Model {
    use BelongsToTenant;
    
    // Constants
    ACTION_TYPES - Available actions (send_email, send_sms, defer_donor, etc.)
    
    // Methods
    shouldExecute($entity, $stepData) - Evaluate step conditions
    getActionDetails() - Get action details and timing
}
```

### WorkflowExecution Model

```php
class WorkflowExecution extends Model {
    use BelongsToTenant;
    
    // Constants
    STATUSES - Execution statuses (pending, running, completed, failed, paused)
    
    // Methods
    logEvent($event, $details) - Add event to execution log
    addCompletedStep($stepNumber, $result) - Mark step as completed
    markAsRunning() - Set status to running
    markAsCompleted() - Set status to completed
    markAsFailed($errorMessage) - Set status to failed
    getExecutionProgress() - Get progress percentage and details
    
    // Scopes
    pending() - Get pending executions
    completed() - Get completed executions
    failed() - Get failed executions
}
```

---

## Services

### DomainService

Manages custom domain lifecycle, verification, and SSL certificates.

**Key Methods:**
```php
createDomain($tenantId, $domain)
setPrimaryDomain($customDomainId)
verifyDomain($domainId, $verificationToken)
generateSslCertificate($customDomainId)
activateDomain($customDomainId)
deactivateDomain($customDomainId)
listDomainsForTenant($tenantId)
checkSslExpiration($customDomainId, $daysThreshold = 30)
validateDomainFormat($domain)
isDomainAvailable($domain)
```

### BrandingService

Manages branding customization, theme generation, and email templates.

**Key Methods:**
```php
getOrCreateBranding($tenantId)
updateBranding($tenantId, $data)
uploadLogo($tenantId, $file)
uploadFavicon($tenantId, $file)
generateThemeCss($tenantId)
getThemeAssets($tenantId)
setEmailTemplate($tenantId, $templateName, $templateContent)
getEmailTemplate($tenantId, $templateName, $data = [])
hideAllSaasBranding($tenantId)
getBrandingForDomain($domain)
```

### CustomFieldService

Manages custom field lifecycle and validation.

**Key Methods:**
```php
createCustomField($tenantId, $data)
updateCustomField($fieldId, $data)
deleteCustomField($fieldId)
getCustomFieldsForEntity($tenantId, $entityType)
getSearchableFieldsForEntity($tenantId, $entityType)
validateCustomFieldValue($field, $value)
validateCustomFields($tenantId, $entityType, $data)
filterVisibleFields($tenantId, $entityType, $parentData = [])
reorderFields($tenantId, $entityType, $fieldOrder)
bulkCreateFields($tenantId, $entityType, $fieldDefinitions)
```

### EligibilityService

Manages eligibility templates, evaluation, and versioning.

**Key Methods:**
```php
createEligibilityTemplate($tenantId, $data)
updateEligibilityTemplate($templateId, $data)
createTemplateVersion($templateId)
getActiveTemplate($tenantId)
getDefaultTemplate($tenantId)
setAsDefault($templateId)
setAsActive($templateId)
evaluateDonorEligibility($donor, $templateId = null)
getDeferralPeriod($templateId, $deferralType)
listEligibilityTemplates($tenantId)
deleteTemplate($templateId)
createDefaultTemplates($tenantId)
```

### WorkflowService

Manages workflow lifecycle and execution orchestration.

**Key Methods:**
```php
createWorkflow($tenantId, $data)
updateWorkflow($workflowId, $data)
createWorkflowStep($workflowId, $stepNumber, $data)
updateWorkflowStep($stepId, $data)
deleteWorkflowStep($stepId)
deleteWorkflow($workflowId)
enableWorkflow($workflowId)
disableWorkflow($workflowId)
executeWorkflow($entity, $triggerType)
processExecution($executionId)
listWorkflows($tenantId)
getWorkflowExecutionHistory($workflowId, $limit = 50)
getExecutionDetails($executionId)
retryExecution($executionId)
```

---

## API Endpoints

### Custom Domains

```
GET    /api/custom-domains                           # List all domains
POST   /api/custom-domains                           # Create new domain
GET    /api/custom-domains/{id}                      # Get domain details
POST   /api/custom-domains/{id}/verify               # Verify domain
GET    /api/custom-domains/{id}/verification-records # Get DNS records
POST   /api/custom-domains/{id}/ssl/generate         # Generate SSL
GET    /api/custom-domains/{id}/ssl/expiration       # Check SSL expiration
POST   /api/custom-domains/{id}/ssl/renew            # Renew SSL
POST   /api/custom-domains/{id}/activate             # Activate domain
POST   /api/custom-domains/{id}/deactivate           # Deactivate domain
POST   /api/custom-domains/{id}/set-primary          # Set as primary
DELETE /api/custom-domains/{id}                      # Delete domain
```

### Custom Branding

```
GET    /api/custom-branding                          # Get branding config
PUT    /api/custom-branding                          # Update branding
POST   /api/custom-branding/logo                     # Upload logo
POST   /api/custom-branding/favicon                  # Upload favicon
GET    /api/custom-branding/theme/css                # Get theme CSS
GET    /api/custom-branding/theme/assets             # Get theme assets
GET    /api/custom-branding/preview                  # Get theme preview
POST   /api/custom-branding/email-template           # Set email template
GET    /api/custom-branding/email-template           # Get email template
POST   /api/custom-branding/hide-branding            # Hide SaaS branding
```

### Custom Fields

```
GET    /api/custom-fields                            # List fields (filtered by entity_type)
POST   /api/custom-fields                            # Create custom field
GET    /api/custom-fields/{id}                       # Get field details
PUT    /api/custom-fields/{id}                       # Update field
DELETE /api/custom-fields/{id}                       # Delete field
POST   /api/custom-fields/validate                   # Validate single value
POST   /api/custom-fields/validate-bulk              # Validate multiple values
GET    /api/custom-fields/visible                    # Get visible fields (with conditions)
POST   /api/custom-fields/reorder                    # Reorder fields
POST   /api/custom-fields/bulk-create                # Create multiple fields
```

### Eligibility Templates

```
GET    /api/eligibility-templates                    # List all templates
POST   /api/eligibility-templates                    # Create template
GET    /api/eligibility-templates/{id}               # Get template details
PUT    /api/eligibility-templates/{id}               # Update template
DELETE /api/eligibility-templates/{id}               # Delete template
GET    /api/eligibility-templates/active             # Get active template
POST   /api/eligibility-templates/{id}/version       # Create new version
POST   /api/eligibility-templates/{id}/set-default   # Set as default
POST   /api/eligibility-templates/{id}/activate      # Set as active
POST   /api/eligibility-templates/evaluate/donor     # Evaluate donor eligibility
POST   /api/eligibility-templates/{id}/test          # Test evaluation
GET    /api/eligibility-templates/{id}/deferral-periods # Get deferral periods
POST   /api/eligibility-templates/create-defaults    # Create default templates
```

### Workflows

```
GET    /api/workflows                                # List all workflows
POST   /api/workflows                                # Create workflow
GET    /api/workflows/{id}                           # Get workflow details
PUT    /api/workflows/{id}                           # Update workflow
DELETE /api/workflows/{id}                           # Delete workflow
POST   /api/workflows/{id}/steps                     # Add workflow step
PUT    /api/workflows/{id}/steps/{stepId}            # Update workflow step
DELETE /api/workflows/{id}/steps/{stepId}            # Delete workflow step
POST   /api/workflows/{id}/enable                    # Enable workflow
POST   /api/workflows/{id}/disable                   # Disable workflow
GET    /api/workflows/{id}/executions                # Get execution history
GET    /api/workflows/executions/{executionId}       # Get execution details
POST   /api/workflows/executions/{executionId}/retry # Retry execution
POST   /api/workflows/{id}/test                      # Test workflow
POST   /api/workflows/trigger/manual                 # Trigger manually
GET    /api/workflows/available/triggers             # List available triggers
GET    /api/workflows/available/actions              # List available actions
```

---

## Usage Examples

### Example 1: Setup Custom Domain with Branding

```php
// Create custom domain
$domain = $domainService->createDomain($tenantId, 'client.mybloodbank.com');

// Get verification records for DNS setup
$records = $domainService->getVerificationDnsRecords($domain->id);
// Response: ['type' => 'CNAME', 'name' => 'client.mybloodbank.com', 'value' => 'verify.saas.bloodbank.com']

// Verify domain after DNS configuration
$verified = $domainService->verifyDomain($domain->id, $verificationToken);

// Generate SSL certificate
$domain = $domainService->generateSslCertificate($domain->id);

// Set as primary and activate
$domainService->setPrimaryDomain($domain->id);
$domainService->activateDomain($domain->id);

// Setup branding
$brandingService->updateBranding($tenantId, [
    'primary_color' => '#0066CC',
    'company_name' => 'Client Blood Bank',
    'hide_saas_branding' => true,
]);
```

### Example 2: Create Custom Eligibility Criteria

```php
// Create custom eligibility template
$template = $eligibilityService->createEligibilityTemplate($tenantId, [
    'name' => 'Premium Donor Requirements',
    'age_range' => ['min' => 18, 'max' => 60],
    'weight_limit' => 55,
    'hemoglobin_levels' => ['min_male' => 14.0, 'min_female' => 13.0],
    'blood_pressure' => ['systolic_max' => 140, 'diastolic_max' => 90],
    'deferral_periods' => [
        'fever' => 14,
        'surgery' => 45,
        'vaccination' => 21,
    ],
    'medications_to_defer' => ['Warfarin', 'Aspirin'],
    'conditions_to_defer' => ['HIV', 'Hepatitis', 'Diabetes'],
]);

// Set as active
$eligibilityService->setAsActive($template->id);

// Evaluate donor
$evaluation = $eligibilityService->evaluateDonorEligibility($donor);
```

### Example 3: Create Workflow for Donation Follow-up

```php
// Create workflow triggered on donation completion
$workflow = $workflowService->createWorkflow($tenantId, [
    'name' => 'Post-Donation Follow-up',
    'trigger_type' => 'donation.completed',
    'is_active' => true,
]);

// Step 1: Send thank you email
$workflowService->createWorkflowStep($workflow->id, 1, [
    'action_type' => 'send_email',
    'action_config' => [
        'subject' => 'Thank you for your donation!',
        'body' => 'Your donation will help save lives.',
    ],
]);

// Step 2: Wait 24 hours, then send follow-up SMS
$workflowService->createWorkflowStep($workflow->id, 2, [
    'action_type' => 'send_sms',
    'action_config' => [
        'message' => 'We hope you are feeling well. You are eligible to donate again in 56 days.',
    ],
    'wait_hours' => 24,
]);

// Workflow automatically triggers on donation.completed events
```

### Example 4: Custom Fields for Donors

```php
// Create custom blood type information field
$fieldService->createCustomField($tenantId, [
    'entity_type' => 'donor',
    'field_name' => 'rare_blood_type',
    'field_label' => 'Do you have a rare blood type?',
    'field_type' => 'select',
    'field_options' => ['No', 'Yes - Duffy Negative', 'Yes - Kell Negative', 'Other'],
    'is_required' => true,
    'is_searchable' => true,
]);

// Create conditional field that shows only if rare_blood_type is selected
$fieldService->createCustomField($tenantId, [
    'entity_type' => 'donor',
    'field_name' => 'rare_type_details',
    'field_label' => 'Please describe your rare blood type',
    'field_type' => 'textarea',
    'conditional_logic' => [
        ['field_name' => 'rare_blood_type', 'operator' => '!=', 'value' => 'No'],
    ],
]);

// Get visible fields for donor (with conditional logic applied)
$fields = $fieldService->filterVisibleFields($tenantId, 'donor', $donorData);
```

---

## File Structure

```
app/
├── Models/
│   ├── CustomDomain.php
│   ├── CustomBranding.php
│   ├── CustomField.php
│   ├── EligibilityTemplate.php
│   ├── Workflow.php
│   ├── WorkflowStep.php
│   └── WorkflowExecution.php
├── Services/
│   ├── DomainService.php
│   ├── BrandingService.php
│   ├── CustomFieldService.php
│   ├── EligibilityService.php
│   └── WorkflowService.php
├── Http/Controllers/API/SaaS/
│   ├── CustomDomainController.php
│   ├── CustomBrandingController.php
│   ├── CustomFieldController.php
│   ├── EligibilityController.php
│   └── WorkflowController.php
└── Jobs/
    └── ProcessWorkflowExecution.php
database/
├── migrations/
│   ├── 2026_01_24_300001_create_custom_domains_table.php
│   ├── 2026_01_24_300002_create_custom_branding_table.php
│   ├── 2026_01_24_300003_create_custom_fields_table.php
│   ├── 2026_01_24_300004_create_eligibility_templates_table.php
│   ├── 2026_01_24_300005_create_workflows_table.php
│   ├── 2026_01_24_300006_create_workflow_steps_table.php
│   └── 2026_01_24_300007_create_workflow_executions_table.php
└── seeders/
    └── Phase4Seeder.php
routes/
└── api/
    └── saas-phase4.php
```

---

## Testing

### Domain Management Test

```php
public function testDomainCreationAndVerification()
{
    $domain = $this->domainService->createDomain($this->tenantId, 'test.client.com');
    
    $this->assertFalse($domain->is_verified);
    $this->assertNotNull($domain->verification_token);
    
    $verified = $this->domainService->verifyDomain($domain->id, $domain->verification_token);
    $this->assertTrue($verified->is_verified);
}
```

### Custom Field Validation Test

```php
public function testCustomFieldValidation()
{
    $field = CustomField::create([
        'field_type' => 'email',
        'is_required' => true,
    ]);
    
    $validation = $this->fieldService->validateCustomFieldValue($field, 'invalid');
    $this->assertFalse($validation['valid']);
    
    $validation = $this->fieldService->validateCustomFieldValue($field, 'test@example.com');
    $this->assertTrue($validation['valid']);
}
```

### Workflow Execution Test

```php
public function testWorkflowExecution()
{
    $execution = WorkflowExecution::create([...]);
    
    $this->workflowService->processExecution($execution->id);
    
    $this->assertEquals('completed', $execution->status);
    $this->assertNotNull($execution->completed_at);
}
```

---

## Performance Considerations

1. **Database Indexing**: All frequently queried fields are indexed for fast lookups
2. **Async Processing**: Workflow execution uses queue jobs to prevent request blocking
3. **Caching**: Theme CSS and branding assets can be cached at the web server level
4. **Pagination**: Large result sets are paginated to reduce memory usage

---

## Security

1. **Multi-Tenant Isolation**: All queries use `BelongsToTenant` trait for automatic filtering
2. **Authorization**: Policies control access to tenant resources
3. **Input Validation**: All inputs are validated before processing
4. **File Upload Security**: Only whitelisted file types allowed for logos/favicons

---

## Migration & Deployment

1. **Run migrations**: `php artisan migrate`
2. **Seed defaults**: `php artisan db:seed --class=Phase4Seeder`
3. **Include routes**: Routes are automatically loaded from `routes/api/saas-phase4.php`
4. **Create routes file entry**: Ensure the routes file is imported in the main API routes

---

## Monitoring & Logging

- **Workflow Executions**: All executions are logged with detailed event logs
- **Error Tracking**: Failed executions capture error messages and stack traces
- **Performance Metrics**: Monitor workflow execution time and success rates

---

## Next Steps (Phase 5)

- Mobile apps (iOS/Android) with white-label support
- Advanced analytics and reporting
- Machine learning for donor recruitment
- Advanced integrations (Salesforce, HubSpot)

