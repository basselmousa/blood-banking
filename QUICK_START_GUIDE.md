# Donation Eligibility System - Quick Start Guide

## 🚀 5-Minute Setup

### Prerequisites
```bash
# Install PHP 7.4+ and Laravel 8
composer --version  # Should show Composer 2.x
php --version       # Should show PHP 7.4+
```

### 1. Install Dependencies (1 min)
```bash
cd d:/LaravelWork/blood-banking
composer install
npm install
```

### 2. Setup Environment (1 min)
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Configure Database (1 min)
Edit `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=blood_banking
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Run Migrations (1 min)
```bash
php artisan migrate
php artisan db:seed  # Optional: seeds sample data
```

### 5. Start Server (1 min)
```bash
php artisan serve
```

Visit: `http://localhost:8000`

---

## 📚 File Structure Overview

```
Key Implementation Files:

CONTROLLERS:
└── app/Http/Controllers/DonationEligibilityController.php

SERVICES (Business Logic):
├── app/Services/DonationEligibilityService.php
├── app/Services/DonationRiskAssessmentService.php
├── app/Services/DonationDeferralService.php
└── app/Services/DonationHelpers.php

MODELS:
├── app/Models/Donor.php
├── app/Models/DonationRecord.php
└── app/Models/DonationDeferral.php

VIEWS:
├── resources/views/donation/eligibility/dashboard.blade.php
├── resources/views/donation/eligibility/check.blade.php
├── resources/views/donation/eligibility/list.blade.php
├── resources/views/donation/deferrals/list.blade.php
└── resources/views/donation/donations/record.blade.php

TESTS:
├── tests/Unit/DonationEligibilityServiceTest.php
├── tests/Unit/DonationRiskAssessmentServiceTest.php
├── tests/Unit/DonationHelpersTest.php
└── tests/Feature/DonationEligibilityControllerTest.php

STYLING:
└── public/css/donation-criteria.css
```

---

## 🎯 Common Tasks

### Check if Donor is Eligible

```php
use App\Services\DonationEligibilityService;
use App\Models\Donor;

$service = new DonationEligibilityService();
$donor = Donor::find(1);
$result = $service->isEligible($donor);

if ($result['eligible']) {
    echo "✅ Donor is eligible!";
} else {
    echo "❌ Reasons: " . implode(", ", $result['reasons']);
}
```

### Assess Donor Risk

```php
use App\Services\DonationRiskAssessmentService;

$service = new DonationRiskAssessmentService();
$risk = $service->assessRisk($donor);

echo "Risk Level: " . $risk['level'];  // low, medium, high
echo "Risk Score: " . $risk['score'];   // 0-100
```

### Defer a Donor

```php
use App\Models\DonationDeferral;

DonationDeferral::create([
    'donor_id' => $donor->id,
    'reason' => 'Low hemoglobin',
    'description' => 'Hemoglobin below minimum threshold',
    'deferral_type' => 'temporary',  // temporary, permanent, conditional
    'eligible_after' => now()->addWeeks(4),
]);

// Dispatch event (triggers notification)
event(new DonorDeferredEvent(
    $donor,
    'Low hemoglobin',
    'temporary',
    now()->addWeeks(4)
));
```

### Record a Donation

```php
use App\Models\DonationRecord;

$donation = DonationRecord::create([
    'donor_id' => $donor->id,
    'donation_date' => now(),
    'blood_volume' => 450,
    'type' => 'whole_blood',  // whole_blood, plasma, platelets, red_cells
    'status' => 'completed',   // completed, rejected, deferred
    'hemoglobin_before' => 15.5,
    'notes' => 'Smooth process',
]);

// Update donor's last donation date
$donor->update(['last_donation_date' => now()]);

// Dispatch event (triggers thank you notification)
event(new DonationRecordedEvent($donation));
```

---

## 🧪 Running Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Unit/DonationEligibilityServiceTest.php

# Run with coverage
php artisan test --coverage

# Run single test method
php artisan test tests/Unit/DonationEligibilityServiceTest.php --filter test_eligible_donor_passes_all_checks
```

---

## 🔍 Eligibility Criteria

| Criteria | Min | Max | Note |
|----------|-----|-----|------|
| Age | 18 | 65 | Years |
| Weight | 50 | ∞ | Kilograms |
| BMI | 18.5 | 29.9 | kg/m² |
| Hemoglobin (M) | 13.5 | ∞ | g/dL |
| Hemoglobin (F) | 12.0 | ∞ | g/dL |
| Donation Gap | 3 | ∞ | Months |
| Health Status | None | - | No disqualifying diseases |

---

## 📊 Database Relationships

```
Donor (1) ──────── (Many) DonationRecord
  │
  └──────── (Many) DonationDeferral
```

### Key Fields

**Donor Table:**
- id, first_name, last_name, email, phone
- date_of_birth, gender, blood_group
- weight, height, bmi, hemoglobin, blood_pressure
- has_disease, disease_name
- is_deferred, deferred_until
- last_donation_date, created_at, updated_at

**DonationRecord Table:**
- id, donor_id, donation_date
- blood_volume, type, status
- rejection_reason, hemoglobin_before
- notes, created_at, updated_at

**DonationDeferral Table:**
- id, donor_id, reason, description
- deferral_type, eligible_after
- created_at, updated_at

---

## 🌐 API Routes

```php
// Protected routes (require authentication)
GET  /donation-eligibility/dashboard      → Dashboard overview
GET  /donation-eligibility/check/{id}     → Check donor eligibility
GET  /donation-eligibility/list            → List eligible donors (filter by blood group)
GET  /donation-eligibility/deferred        → List deferred donors
POST /donation-eligibility/defer           → Create deferral
POST /donation-eligibility/record          → Record donation
DELETE /donation-eligibility/clear/{id}    → Clear deferral
```

---

## 🎨 UI Views

### 1. Dashboard
- Location: `/donation-eligibility/dashboard`
- Shows: Total donors, eligible count, deferred count
- Includes: Blood group distribution chart, recent donations

### 2. Check Eligibility
- Location: `/donation-eligibility/check/{id}`
- Shows: Detailed donor info, eligibility status, risk assessment
- Actions: Defer donor, record donation

### 3. List Eligible Donors
- Location: `/donation-eligibility/list?blood_group=O+`
- Shows: Searchable table of eligible donors
- Filters: Blood group, city

### 4. List Deferred Donors
- Location: `/donation-eligibility/deferred`
- Shows: Deferred donors with remaining deferral days
- Actions: Clear deferral (when eligible)

### 5. Record Donation
- Location: `/donation-eligibility/donations/record`
- Form: Date, type, volume, status, hemoglobin
- Validation: Real-time form validation

---

## 🔔 Notifications

### Deferral Notification
Sent when donor is deferred:
```
Subject: Your Donation Deferral Notice

Dear [Donor Name],

You have been deferred from blood donation for the following reason:
Reason: [Deferral Reason]
Type: [Temporary/Permanent/Conditional]
Eligible After: [Date]

Please contact us for more information.
```

### Thank You Notification
Sent after successful donation:
```
Subject: Thank You for Your Blood Donation!

Dear [Donor Name],

Thank you for donating [Volume]ml of [Type] blood on [Date].

Your next eligible donation date: [Date]

Your contribution saves lives!
```

---

## 🐛 Debugging

### Check Logs
```bash
# View recent logs
tail -f storage/logs/laravel.log

# Filter by level
grep "ERROR" storage/logs/laravel.log
```

### Database Debugging
```bash
# Check if tables exist
mysql> SHOW TABLES;

# View table structure
mysql> DESCRIBE donors;

# Quick query
mysql> SELECT COUNT(*) FROM donors;
```

### Test Database Queries
```bash
# Launch Laravel Tinker
php artisan tinker

# In Tinker:
>>> $donors = App\Models\Donor::where('is_deferred', true)->get();
>>> $donors->count();
>>> $donors->first()->donationRecords()->count();
```

---

## 📈 Performance Tips

1. **Cache Dashboard Data**
   ```php
   $stats = Cache::remember('donation_stats', 3600, fn() => 
       // Calculate stats
   );
   ```

2. **Use Database Indexes**
   ```sql
   CREATE INDEX idx_donor_blood_group ON donors(blood_group);
   CREATE INDEX idx_donor_city ON donors(city);
   ```

3. **Eager Load Relationships**
   ```php
   $donors = Donor::with('donationRecords', 'donationDeferrals')->get();
   ```

4. **Queue Notifications**
   - Notifications sent asynchronously
   - Configure in `config/queue.php`
   - Run worker: `php artisan queue:work`

---

## 🔐 Security

- All routes protected with `auth` middleware
- Form validation with custom rules
- SQL injection prevention (Eloquent)
- CSRF token protection
- XSS protection (Blade escaping)

---

## 📖 Documentation Files

| File | Purpose |
|------|---------|
| `DONATION_SYSTEM_GUIDE.md` | Complete system documentation |
| `IMPLEMENTATION_SUMMARY.md` | Summary of all implementations |
| `API_TESTING_GUIDE.md` | API endpoint testing guide |
| `QUICK_START_GUIDE.md` | This file - quick reference |

---

## 💡 Pro Tips

1. **Use Factories for Testing**
   ```php
   $donor = Donor::factory()->eligible()->create();
   $donor = Donor::factory()->deferred(30)->create();
   ```

2. **Check Eligibility Programmatically**
   ```php
   if ($service->isEligible($donor)['eligible']) {
       // Proceed with donation
   }
   ```

3. **Handle Deferrals Elegantly**
   ```php
   $days = $donor->donationDeferrals()
       ->latest()
       ->first()
       ->daysRemaining();
   ```

4. **Use Repository Pattern**
   ```php
   $eligible = app(DonorRepository::class)->getEligible();
   ```

---

## ❓ FAQ

**Q: How do I add a new eligibility criterion?**
A: Edit `DonationEligibilityService.php` and add check in `isEligible()` method.

**Q: How do I customize email templates?**
A: Edit notification classes in `app/Notifications/`.

**Q: How do I change blood group validation rules?**
A: Edit `DonationHelpers.php` helper functions.

**Q: How do I run tests in CI/CD?**
A: Use `php artisan test --env=testing` in your pipeline.

**Q: Where are notifications configured?**
A: Check `config/mail.php` and `config/queue.php`.

---

## 🆘 Getting Help

1. Check documentation files
2. Review test files for usage examples
3. Check Laravel service class docblocks
4. Review migration files for schema
5. Open GitHub issue with details

---

**Version**: 1.0.0  
**Last Updated**: January 2024  
**Status**: Production Ready ✅

For complete details, see `DONATION_SYSTEM_GUIDE.md`
