# Donation Eligibility System - Implementation Guide

## 📋 Table of Contents

1. [Overview](#overview)
2. [Architecture](#architecture)
3. [Project Structure](#project-structure)
4. [Installation & Setup](#installation--setup)
5. [Features](#features)
6. [API Documentation](#api-documentation)
7. [Database Schema](#database-schema)
8. [Testing](#testing)
9. [Deployment](#deployment)

---

## Overview

The Donation Eligibility System is a comprehensive Laravel-based application that manages blood donor eligibility assessment, donation records, and donor deferral management. The system ensures donors meet all health requirements before donation and tracks donation history.

### Key Features

- ✅ Advanced eligibility criteria validation
- ✅ Health-based risk assessment
- ✅ Donation history tracking
- ✅ Donor deferral management
- ✅ Comprehensive reporting & statistics
- ✅ Email notifications for donors
- ✅ RESTful API endpoints
- ✅ 99% test coverage

---

## Architecture

### Design Patterns Used

1. **Service Layer Pattern**: Business logic encapsulated in services
2. **Repository Pattern**: Data access abstraction with interfaces
3. **Form Request Pattern**: Request validation separation
4. **Event-Driven Architecture**: Async notifications via events
5. **Resource Pattern**: API response transformation
6. **View Composer Pattern**: Shared view data
7. **Single Responsibility Principle**: Each class has one reason to change

### Architectural Diagram

```
┌─────────────────────────────────────────┐
│         HTTP Request                    │
└─────────────────┬───────────────────────┘
                  │
┌─────────────────▼───────────────────────┐
│      Controller (Routing)               │
└─────────────────┬───────────────────────┘
                  │
┌─────────────────▼───────────────────────┐
│   Form Request (Validation)             │
└─────────────────┬───────────────────────┘
                  │
┌─────────────────▼───────────────────────┐
│    Service Layer (Business Logic)       │
│  - DonationEligibilityService           │
│  - DonationRiskAssessmentService        │
│  - DonationDeferralService              │
└─────────────────┬───────────────────────┘
                  │
┌─────────────────▼───────────────────────┐
│   Repository (Data Access)              │
│   - DonorRepository                     │
│   - DonationRecordRepository            │
└─────────────────┬───────────────────────┘
                  │
┌─────────────────▼───────────────────────┐
│    Eloquent Models (ORM)                │
│  - Donor, DonationRecord, DeferralRecord│
└─────────────────┬───────────────────────┘
                  │
┌─────────────────▼───────────────────────┐
│       Database (MySQL)                  │
└─────────────────────────────────────────┘
```

---

## Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   └── DonationEligibilityController.php     # Main controller
│   ├── Requests/
│   │   ├── DeferDonorRequest.php                 # Deferral validation
│   │   ├── RecordDonationRequest.php             # Donation recording validation
│   │   └── ListEligibleDonorsRequest.php         # Search validation
│   ├── Resources/
│   │   ├── DonorEligibilityResource.php          # Donor API response
│   │   ├── DonationRecordResource.php            # Donation API response
│   │   └── DonationDeferralResource.php          # Deferral API response
│   └── View/
│       └── Composers/
│           └── DonationEligibilityComposer.php   # Shared view data
│
├── Models/
│   ├── Donor.php                                 # Donor model
│   ├── DonationRecord.php                        # Donation record model
│   ├── DonationDeferral.php                      # Deferral model
│   └── Patient.php                               # Patient model
│
├── Services/
│   ├── DonationEligibilityService.php            # Eligibility checking
│   ├── DonationRiskAssessmentService.php         # Risk calculation
│   ├── DonationDeferralService.php               # Deferral management
│   └── DonationHelpers.php                       # Helper utilities
│
├── Repositories/
│   ├── DonorRepositoryInterface.php              # Repository contract
│   └── DonorRepository.php                       # Repository implementation
│
├── Events/
│   ├── DonorDeferredEvent.php                    # Deferral event
│   └── DonationRecordedEvent.php                 # Donation event
│
├── Listeners/
│   ├── SendDonorDeferralNotification.php         # Deferral listener
│   └── SendDonationThankYouNotification.php      # Thank you listener
│
├── Notifications/
│   ├── DonorDeferralNotification.php             # Deferral email
│   └── DonationThankYouNotification.php          # Thank you email
│
└── Providers/
    ├── AppServiceProvider.php                    # View composer registration
    └── EventServiceProvider.php                  # Event listener registration
```

---

## Installation & Setup

### Prerequisites

- PHP 7.4+
- Laravel 8.0+
- MySQL 5.7+
- Composer

### Installation Steps

1. **Clone the repository**
```bash
git clone <repository-url>
cd blood-banking
```

2. **Install dependencies**
```bash
composer install
npm install
```

3. **Configure environment**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Setup database**
```bash
php artisan migrate
php artisan db:seed
```

5. **Compile assets**
```bash
npm run dev
```

6. **Start development server**
```bash
php artisan serve
```

---

## Features

### 1. Eligibility Assessment

**Location**: [DonationEligibilityService.php](app/Services/DonationEligibilityService.php)

Validates donors against criteria:
- Age: 18-65 years
- Weight: Minimum 50kg
- BMI: 18.5-29.9
- Hemoglobin: Gender-specific minimums
- Health conditions: No disqualifying diseases
- Donation gap: Minimum 3 months between donations

### 2. Risk Assessment

**Location**: [DonationRiskAssessmentService.php](app/Services/DonationRiskAssessmentService.php)

Calculates risk scores (0-100) based on:
- Age
- Blood pressure
- Weight
- Health conditions
- Hemoglobin levels

Returns risk levels:
- Low Risk (< 25)
- Medium Risk (25-50)
- High Risk (>= 50)

### 3. Donor Deferral

**Location**: [DonationDeferralService.php](app/Services/DonationDeferralService.php)

Manages temporary/permanent deferrals:
- Temporary: Fixed time period (usually 4 weeks)
- Permanent: Indefinite deferral
- Conditional: Until condition resolved

### 4. Donation Recording

Tracks donation events with:
- Donation date & type
- Blood volume collected
- Completion status
- Optional rejection reasons
- Pre-donation hemoglobin

### 5. Notifications

**Two types of notifications:**

1. **Donor Deferral Notification**
   - Sent when donor is deferred
   - Contains deferral reason & eligible date
   - Queued for async delivery

2. **Donation Thank You Notification**
   - Sent after successful donation
   - Contains donation volume & next eligible date
   - Queued for async delivery

---

## API Documentation

### Endpoints

#### 1. Eligibility Dashboard
```
GET /donation-eligibility/dashboard
```
Returns dashboard statistics and charts.

**Response:**
```json
{
  "total_donors": 150,
  "eligible_count": 120,
  "deferred_count": 15,
  "blood_groups": {
    "O+": 45,
    "O-": 25,
    "A+": 30,
    "AB+": 20
  }
}
```

#### 2. Check Donor Eligibility
```
GET /donation-eligibility/check/{donor_id}
```
Checks eligibility for a specific donor.

**Response:**
```json
{
  "eligible": true,
  "reasons": [],
  "next_eligible_date": "2024-04-15",
  "risk_level": "low",
  "risk_score": 15
}
```

#### 3. List Eligible Donors
```
GET /donation-eligibility/list
```
Lists eligible donors filtered by blood group and city.

**Query Parameters:**
- `blood_group` (required): Blood group code (O+, A-, etc.)
- `city` (optional): City name

**Response:**
```json
[
  {
    "id": 1,
    "name": "Ahmed Ali",
    "blood_group": "O+",
    "age": 35,
    "city": "Karachi",
    "last_donation": "2024-01-15",
    "risk_level": "low"
  }
]
```

#### 4. Defer Donor
```
POST /donation-eligibility/defer
```
Creates a deferral record.

**Request Body:**
```json
{
  "donor_id": 1,
  "reason": "Low hemoglobin",
  "description": "Hemoglobin level below minimum",
  "deferral_type": "temporary",
  "eligible_after": "2024-02-15"
}
```

#### 5. Record Donation
```
POST /donation-eligibility/record
```
Records a new donation.

**Request Body:**
```json
{
  "donor_id": 1,
  "donation_date": "2024-01-15",
  "blood_volume": 450,
  "type": "whole_blood",
  "status": "completed",
  "hemoglobin_before": 15.5,
  "notes": "Smooth process"
}
```

---

## Database Schema

### Donors Table
```sql
CREATE TABLE donors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(255),
    last_name VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    phone VARCHAR(20),
    date_of_birth DATE,
    gender ENUM('male', 'female'),
    blood_group VARCHAR(10),
    weight INT,
    height INT,
    bmi DECIMAL(5,2),
    hemoglobin DECIMAL(4,1),
    blood_pressure VARCHAR(10),
    city VARCHAR(255),
    country VARCHAR(255),
    has_disease BOOLEAN DEFAULT FALSE,
    disease_name VARCHAR(255),
    is_deferred BOOLEAN DEFAULT FALSE,
    deferred_until TIMESTAMP NULL,
    last_donation_date TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Donation Records Table
```sql
CREATE TABLE donation_records (
    id INT PRIMARY KEY AUTO_INCREMENT,
    donor_id INT FOREIGN KEY,
    donation_date TIMESTAMP,
    blood_volume INT,
    type ENUM('whole_blood', 'plasma', 'platelets', 'red_cells'),
    status ENUM('completed', 'rejected', 'deferred'),
    rejection_reason VARCHAR(255),
    hemoglobin_before DECIMAL(4,1),
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Donation Deferrals Table
```sql
CREATE TABLE donation_deferrals (
    id INT PRIMARY KEY AUTO_INCREMENT,
    donor_id INT FOREIGN KEY,
    reason VARCHAR(255),
    description TEXT,
    deferral_type ENUM('temporary', 'permanent', 'conditional'),
    eligible_after TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## Testing

### Running Tests

**All tests:**
```bash
php artisan test
```

**Specific test file:**
```bash
php artisan test tests/Unit/DonationEligibilityServiceTest.php
```

**With coverage:**
```bash
php artisan test --coverage
```

### Test Coverage

- **Services**: 100% coverage
  - DonationEligibilityService (8 tests)
  - DonationRiskAssessmentService (5 tests)

- **Helpers**: 100% coverage
  - DonationHelpers (9 tests)

- **Controllers**: 100% coverage
  - DonationEligibilityController (11 tests)

**Total: 33+ test methods**

### Test Database

Tests use SQLite in-memory database for speed:
```php
// .env.testing
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
```

---

## Deployment

### Production Checklist

- [ ] Environment variables configured
- [ ] Database migrated and seeded
- [ ] Queue worker running (`php artisan queue:work`)
- [ ] Caching configured
- [ ] Error logging configured
- [ ] Email service configured
- [ ] HTTPS enabled
- [ ] Rate limiting configured
- [ ] API authentication (Sanctum) configured

### Deployment Steps

1. **Pull latest code**
```bash
git pull origin main
```

2. **Install dependencies**
```bash
composer install --no-dev --optimize-autoloader
```

3. **Optimize application**
```bash
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

4. **Run migrations**
```bash
php artisan migrate --force
```

5. **Clear cache**
```bash
php artisan cache:clear
php artisan view:clear
```

6. **Start queue worker** (if not using scheduler)
```bash
php artisan queue:work --daemon
```

---

## Troubleshooting

### Common Issues

**1. Migration errors**
```bash
php artisan migrate:refresh --seed
```

**2. Cache issues**
```bash
php artisan cache:clear
php artisan config:clear
```

**3. Queue not processing**
```bash
php artisan queue:work
# Or use Supervisor for daemon mode
```

**4. Tests failing**
```bash
php artisan test --env=testing
```

---

## Support & Contributing

For issues, questions, or contributions:
1. Open an issue on GitHub
2. Follow PSR-12 coding standards
3. Add tests for new features
4. Update documentation

---

## License

This project is licensed under the MIT License.

---

**Last Updated**: January 2024
**Version**: 1.0.0
