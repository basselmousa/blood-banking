# Quick Reference Guide - Donation Criteria System

## Medical Standards Implemented

### Age Requirements
- **Minimum:** 18 years old
- **Maximum:** 65 years old

### Physical Requirements
- **Weight:** Minimum 50 kg
- **Height:** Minimum 100 cm (for reference)
- **BMI:** 18.5 - 29.9

### Blood Metrics (Gender-Specific)
- **Hemoglobin (Female):** Minimum 12.5 g/dL
- **Hemoglobin (Male):** Minimum 13.5 g/dL

### Donation Gap
- **Minimum between donations:** 3 months (90 days)
- **Standard donation volume:** 450 ml

### Blood Compatibility Chart

| Donor Type | Can Donate To |
|-----------|--------------|
| O- (Universal) | Everyone (O+, O-, A+, A-, B+, B-, AB+, AB-) |
| O+ | O+, A+, B+, AB+ |
| A- | A-, A+, AB-, AB+ |
| A+ | A+, AB+ |
| B- | B-, B+, AB-, AB+ |
| B+ | B+, AB+ |
| AB- | AB-, AB+ |
| AB+ (Universal Recipient) | AB+ only |

---

## Disqualifying Conditions (Permanent)

- HIV
- Hepatitis B
- Hepatitis C
- Syphilis
- Active Malaria
- Active Tuberculosis
- Heart Disease (certain types)
- Kidney Disease (certain types)
- Liver Disease (certain types)
- Cancer (active treatment)

---

## Temporary Deferral Conditions

| Condition | Duration |
|-----------|----------|
| Recent Tattoo/Piercing | 6 months |
| Recent Vaccination | 2 weeks (varies) |
| Recent Surgery | 4 weeks |
| Dental Work | 1 week |
| Malaria Exposure | 12 months |
| Blood Transfusion | 3 months |
| Pregnancy | 9 months |
| Breastfeeding | 3 months |
| Recent Infection | 2 weeks |
| Recent Antibiotics | 1 week after completion |

---

## Risk Assessment Scoring

| Score Range | Category | Action |
|------------|----------|--------|
| 0-24 | **Low** | Proceed with donation |
| 25-49 | **Medium** | Monitor, additional checks |
| 50-74 | **High** | Review, possible deferral |
| 75-100 | **Critical** | Defer donor |

### Risk Factors
- Age (optimal: 25-60)
- BMI (outside 19-29)
- Low hemoglobin
- Stale health checkup (>6 months)
- Recent frequent donations

---

## API Endpoint Quick Reference

### Check Eligibility
```bash
GET /api/donors/{donor_id}/eligibility
Authorization: Bearer {sanctum_token}
```
**Response:**
```json
{
  "eligible": true,
  "issues": [],
  "next_eligible_date": "2026-04-24",
  "risk_level": "low"
}
```

### Find Compatible Donors
```bash
GET /api/patients/{patient_id}/compatible-donors
Authorization: Bearer {sanctum_token}
```

### List Eligible Donors
```bash
GET /api/eligible-donors?blood_group=O+&city=New+York
Authorization: Bearer {sanctum_token}
```

### Get Donor Statistics
```bash
GET /api/donors/{donor_id}/statistics
Authorization: Bearer {sanctum_token}
```

### List Deferred Donors
```bash
GET /api/deferred-donors
Authorization: Bearer {sanctum_token}
```

---

## Admin Dashboard Routes

| Route | Purpose |
|-------|---------|
| `/admin/donation-eligibility/dashboard` | Overview of eligibility metrics |
| `/admin/donors/{donor}/eligibility` | Check specific donor eligibility |
| `/admin/donors/eligible/list` | List all eligible donors |
| `/admin/donors/deferred/list` | List all deferred donors |
| `/admin/donors/{donor}/defer` | Create donor deferral |
| `/admin/donors/{donor}/clear-deferral` | Remove deferral |
| `/admin/donors/{donor}/record-donation` | Log donation event |

---

## Laravel Query Examples

### Find Eligible Donors by Blood Type
```php
$donors = Donor::byBloodGroup('O+')
    ->active()
    ->get();
```

### Get Recent Donors
```php
$recent = Donor::recentDonors() // Last 12 months
    ->get();
```

### Find Healthy Donors
```php
$healthy = Donor::healthy()
    ->byCity('New York')
    ->get();
```

### Get Deferred Donors
```php
$deferred = DonationDeferral::active()
    ->with('donor')
    ->get();
```

### Get Donation History
```php
$history = DonationRecord::where('donor_id', 1)
    ->completed()
    ->recent(12) // Last 12 months
    ->orderBy('donation_date', 'desc')
    ->get();
```

---

## Service Class Usage

### DonationEligibilityService

```php
use App\Services\DonationEligibilityService;

$service = new DonationEligibilityService();

// Check eligibility
$result = $service->isEligible($donor);
// Returns: ['eligible' => bool, 'issues' => [], 'next_eligible_date' => ..., 'risk_level' => ...]

// Defer a donor
$service->deferDonor(
    $donor,
    'recent_tattoo',
    'Tattoo applied 2 months ago',
    \Carbon\Carbon::now()->addMonths(4),
    'temporary'
);

// Clear deferral
$service->clearDeferral($donor);

// Get next eligible date
$nextDate = $service->getNextEligibleDate($donor);
```

### DonationRiskAssessmentService

```php
use App\Services\DonationRiskAssessmentService;

$riskService = new DonationRiskAssessmentService();

// Calculate risk score (0-100)
$score = $riskService->calculateRiskScore($donor);

// Get category
$category = $riskService->getRiskCategory($donor); // low/medium/high/critical

// Get detailed risks
$details = $riskService->getRiskDetails($donor);
// Returns: ['score' => int, 'category' => string, 'risks' => []]
```

---

## Helper Class Methods

### BloodCompatibilityHelper

```php
use App\Helpers\BloodCompatibilityHelper;

// Get compatible donor blood types for patient
$compatible = BloodCompatibilityHelper::getCompatibleDonors('AB+');
// ['AB+', 'AB-', 'A+', 'A-', 'B+', 'B-', 'O+', 'O-']

// Get recipient blood types for donor
$recipients = BloodCompatibilityHelper::getRecipientBloodGroups('O-');
// Everyone (universal donor)

// Check if specific donor can donate to patient
$canDonate = BloodCompatibilityHelper::isCompatible($donor, 'AB+', '+');

// Get population percentage
$percent = BloodCompatibilityHelper::getCompatibilityPercentage('O+');
// 37.0 (37% of population)
```

### DonationCriteriaHelper

```php
use App\Helpers\DonationCriteriaHelper;

// Check age
$valid = DonationCriteriaHelper::isAgeValid(25); // true

// Check weight
$valid = DonationCriteriaHelper::isWeightValid(60); // true

// Calculate BMI
$bmi = DonationCriteriaHelper::calculateBMI(70, 170); // weight(kg), height(cm)

// Check minimum hemoglobin
$sufficient = DonationCriteriaHelper::isHemoglobinSufficient(14.5, 'male');

// Check donation gap
$canDonate = DonationCriteriaHelper::isDonationGapSufficient($lastDonationDate);

// Get days until eligible
$days = DonationCriteriaHelper::getDaysUntilEligible($lastDonationDate);

// Get disqualifying conditions
$list = DonationCriteriaHelper::getDisqualifyingConditions();

// Check if condition is disqualifying
$hasDisqualifying = DonationCriteriaHelper::hasDisqualifyingCondition('HIV, Hepatitis B');
```

---

## Database Schema Reference

### donors table additions
```
- weight (integer, nullable)
- height (integer, nullable)
- blood_pressure (string, nullable)
- hemoglobin_level (decimal, nullable)
- last_health_checkup (date, nullable)
- health_conditions (json, nullable)
- is_deferred (boolean, default false)
- deferred_until (date, nullable)
- deferral_reason (text, nullable)
```

### donation_records table
```
- id (bigint)
- donor_id (bigint, foreign key)
- donation_date (datetime)
- blood_volume (integer, default 450)
- type (enum: whole_blood, plasma, platelets, red_cells)
- status (enum: completed, rejected, deferred, cancelled)
- rejection_reason (text, nullable)
- next_eligible_date (date, nullable)
- hemoglobin_before (integer, nullable)
- notes (text, nullable)
```

### donation_deferrals table
```
- id (bigint)
- donor_id (bigint, foreign key)
- deferral_type (enum: temporary, permanent, conditional)
- reason (string)
- description (text, nullable)
- deferral_date (date)
- eligible_after (date, nullable)
- is_active (boolean)
- admin_notes (text, nullable)
```

---

## Common Tasks

### Task: Record a Donation
```php
DonationRecord::create([
    'donor_id' => $donor->id,
    'donation_date' => now(),
    'blood_volume' => 450,
    'type' => 'whole_blood',
    'status' => 'completed',
    'hemoglobin_before' => 14.5,
    'next_eligible_date' => now()->addMonths(3),
]);

$donor->update(['last_donation_date' => now()]);
```

### Task: Defer a Donor for 6 Months
```php
$service->deferDonor(
    $donor,
    'recent_tattoo',
    'Tattoo applied on 2026-01-15',
    now()->addMonths(6),
    'temporary'
);
```

### Task: Make Permanent Deferral
```php
$service->deferDonor(
    $donor,
    'hiv_positive',
    'Test confirmed on 2026-01-10',
    null, // No eligible date
    'permanent'
);
```

### Task: Search for Compatible Donors
```php
$patient = Patient::find(1);
$service = new DonationEligibilityService();

$donors = Donor::byBloodGroup($patient->blood_group)
    ->active()
    ->get()
    ->filter(fn($d) => $service->isEligible($d)['eligible']);
```

---

## Testing the System

```php
// Test eligibility check
$donor = Donor::find(1);
$result = (new DonationEligibilityService())->isEligible($donor);
dd($result);

// Test risk assessment
$risk = (new DonationRiskAssessmentService())->getRiskDetails($donor);
dd($risk);

// Test blood compatibility
$compatible = BloodCompatibilityHelper::getCompatibleDonors('AB+');
dd($compatible);

// Test helper methods
$bmi = DonationCriteriaHelper::calculateBMI(70, 170);
dd($bmi);
```

---

## Need Help?

Refer to the main documentation file:
**DONATION_CRITERIA_ENHANCEMENTS.md**

Or check specific service/helper files for detailed comments and docstrings.
