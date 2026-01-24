# Donation Eligibility System - API Testing Guide

This guide provides curl commands and examples for testing all API endpoints.

## Base URL
```
http://localhost:8000/api
```

## Authentication
All endpoints require Bearer token authentication:
```
Authorization: Bearer YOUR_TOKEN
```

## 1. Get Dashboard Statistics

**Endpoint**: `GET /donation-eligibility/dashboard`

**Curl Command**:
```bash
curl -X GET "http://localhost:8000/donation-eligibility/dashboard" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

**Expected Response** (200 OK):
```json
{
  "total_donors": 150,
  "eligible_donors": 120,
  "deferred_donors": 15,
  "blood_group_distribution": {
    "O-": 25,
    "O+": 45,
    "A-": 15,
    "A+": 30,
    "B-": 10,
    "B+": 20,
    "AB-": 5,
    "AB+": 25
  },
  "monthly_donations": [
    { "month": "January", "count": 25 },
    { "month": "February", "count": 30 }
  ],
  "recent_donations": [
    {
      "id": 1,
      "donor": "Ahmed Ali",
      "blood_group": "O+",
      "volume": 450,
      "date": "2024-01-15"
    }
  ]
}
```

---

## 2. Check Donor Eligibility

**Endpoint**: `GET /donation-eligibility/check/{donor_id}`

**Curl Command**:
```bash
curl -X GET "http://localhost:8000/donation-eligibility/check/1" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

**Expected Response** (200 OK):
```json
{
  "eligible": true,
  "donor": {
    "id": 1,
    "name": "Ahmed Ali",
    "blood_group": "O+",
    "age": 35,
    "weight": 75,
    "height": 180,
    "bmi": 23.1,
    "hemoglobin": 15.5,
    "blood_pressure": "120/80",
    "city": "Karachi"
  },
  "reasons": [],
  "next_eligible_date": "2024-04-15",
  "risk_assessment": {
    "level": "low",
    "score": 18,
    "risks": []
  },
  "donation_history": [
    {
      "date": "2024-01-15",
      "type": "whole_blood",
      "volume": 450,
      "status": "completed"
    }
  ]
}
```

**Response if Not Eligible** (200 OK):
```json
{
  "eligible": false,
  "donor": { ... },
  "reasons": [
    "Low hemoglobin: 11.5 (minimum required: 13.5 for males)",
    "Last donation was only 2 months ago (minimum 3 months required)"
  ],
  "next_eligible_date": "2024-04-20",
  "risk_assessment": { ... }
}
```

---

## 3. List Eligible Donors by Blood Group

**Endpoint**: `GET /donation-eligibility/list`

**Query Parameters**:
- `blood_group` (required): Blood group code (O+, O-, A+, A-, B+, B-, AB+, AB-)
- `city` (optional): Filter by city

**Curl Commands**:

Without city filter:
```bash
curl -X GET "http://localhost:8000/donation-eligibility/list?blood_group=O%2B" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

With city filter:
```bash
curl -X GET "http://localhost:8000/donation-eligibility/list?blood_group=O%2B&city=Karachi" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

**Expected Response** (200 OK):
```json
[
  {
    "id": 1,
    "name": "Ahmed Ali",
    "email": "ahmed@example.com",
    "phone": "+923001234567",
    "blood_group": "O+",
    "age": 35,
    "city": "Karachi",
    "last_donation_date": "2024-01-15",
    "risk_level": "low",
    "bmi": 23.1,
    "hemoglobin": 15.5
  },
  {
    "id": 5,
    "name": "Fatima Khan",
    "email": "fatima@example.com",
    "phone": "+923009876543",
    "blood_group": "O+",
    "age": 28,
    "city": "Karachi",
    "last_donation_date": "2024-01-10",
    "risk_level": "low",
    "bmi": 21.5,
    "hemoglobin": 14.8
  }
]
```

**Validation Errors** (422 Unprocessable Entity):
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "blood_group": [
      "The blood group field is required.",
      "The selected blood group is invalid."
    ]
  }
}
```

---

## 4. List Deferred Donors

**Endpoint**: `GET /donation-eligibility/deferred`

**Curl Command**:
```bash
curl -X GET "http://localhost:8000/donation-eligibility/deferred" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

**Expected Response** (200 OK):
```json
{
  "total": 15,
  "temporary": 8,
  "permanent": 3,
  "conditional": 4,
  "deferrals": [
    {
      "id": 1,
      "donor": {
        "id": 2,
        "name": "Hassan Ahmed",
        "email": "hassan@example.com",
        "blood_group": "A+"
      },
      "reason": "Low hemoglobin",
      "description": "Hemoglobin level 11.5, minimum required 13.5",
      "deferral_type": "temporary",
      "created_date": "2024-01-20",
      "eligible_after": "2024-02-17",
      "days_remaining": 28,
      "status": "active"
    },
    {
      "id": 2,
      "donor": {
        "id": 3,
        "name": "Aisha Malik",
        "email": "aisha@example.com",
        "blood_group": "B-"
      },
      "reason": "Medication use",
      "description": "Patient is taking antibiotics, must wait until treatment complete",
      "deferral_type": "conditional",
      "created_date": "2024-01-15",
      "eligible_after": null,
      "days_remaining": null,
      "status": "pending_condition_resolution"
    }
  ]
}
```

---

## 5. Defer a Donor

**Endpoint**: `POST /donation-eligibility/defer`

**Request Body**:
```json
{
  "donor_id": 2,
  "reason": "Low hemoglobin",
  "description": "Hemoglobin level 11.5, minimum required 13.5 for males",
  "deferral_type": "temporary",
  "eligible_after": "2024-02-17"
}
```

**Curl Command**:
```bash
curl -X POST "http://localhost:8000/donation-eligibility/defer" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "donor_id": 2,
    "reason": "Low hemoglobin",
    "description": "Hemoglobin level 11.5, minimum required 13.5 for males",
    "deferral_type": "temporary",
    "eligible_after": "2024-02-17"
  }'
```

**Expected Response** (201 Created):
```json
{
  "success": true,
  "message": "Donor deferred successfully",
  "deferral": {
    "id": 25,
    "donor_id": 2,
    "reason": "Low hemoglobin",
    "description": "Hemoglobin level 11.5, minimum required 13.5 for males",
    "deferral_type": "temporary",
    "eligible_after": "2024-02-17",
    "created_at": "2024-01-20T10:30:00Z",
    "updated_at": "2024-01-20T10:30:00Z"
  }
}
```

**Validation Errors** (422 Unprocessable Entity):
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "donor_id": ["The donor id field is required."],
    "reason": ["The reason field is required."],
    "deferral_type": ["The deferral type must be one of: temporary, permanent, conditional."],
    "eligible_after": ["The eligible after field must be a date after today."]
  }
}
```

---

## 6. Record a Donation

**Endpoint**: `POST /donation-eligibility/record`

**Request Body**:
```json
{
  "donor_id": 1,
  "donation_date": "2024-01-20",
  "blood_volume": 450,
  "type": "whole_blood",
  "status": "completed",
  "hemoglobin_before": 15.5,
  "notes": "Smooth donation process, donor felt well"
}
```

**Curl Command**:
```bash
curl -X POST "http://localhost:8000/donation-eligibility/record" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "donor_id": 1,
    "donation_date": "2024-01-20",
    "blood_volume": 450,
    "type": "whole_blood",
    "status": "completed",
    "hemoglobin_before": 15.5,
    "notes": "Smooth donation process"
  }'
```

**Expected Response** (201 Created):
```json
{
  "success": true,
  "message": "Donation recorded successfully",
  "donation": {
    "id": 1,
    "donor_id": 1,
    "donation_date": "2024-01-20T10:00:00Z",
    "blood_volume": 450,
    "type": "whole_blood",
    "status": "completed",
    "rejection_reason": null,
    "hemoglobin_before": 15.5,
    "notes": "Smooth donation process",
    "next_eligible_date": "2024-04-20",
    "created_at": "2024-01-20T10:30:00Z"
  }
}
```

**Response for Rejected Donation** (201 Created):
```json
{
  "success": true,
  "message": "Donation recorded as rejected",
  "donation": {
    "id": 2,
    "donor_id": 2,
    "donation_date": "2024-01-20",
    "blood_volume": 0,
    "type": "plasma",
    "status": "rejected",
    "rejection_reason": "Low hemoglobin level",
    "hemoglobin_before": 12.0,
    "notes": "Donor rejected at screening",
    "next_eligible_date": "2024-02-20",
    "created_at": "2024-01-20T10:35:00Z"
  }
}
```

**Validation Errors** (422 Unprocessable Entity):
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "donation_date": ["The donation date field is required."],
    "blood_volume": ["The blood volume must be between 100 and 500."],
    "status": ["The selected status is invalid."],
    "rejection_reason": ["The rejection reason field is required when status is rejected."]
  }
}
```

---

## 7. Clear Donor Deferral

**Endpoint**: `DELETE /donation-eligibility/clear/{deferral_id}`

**Curl Command**:
```bash
curl -X DELETE "http://localhost:8000/donation-eligibility/clear/1" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

**Expected Response** (200 OK):
```json
{
  "success": true,
  "message": "Deferral cleared successfully",
  "donor": {
    "id": 2,
    "name": "Hassan Ahmed",
    "is_deferred": false,
    "deferred_until": null
  }
}
```

**Not Found Error** (404 Not Found):
```json
{
  "message": "Deferral not found"
}
```

---

## 8. Get Donation Statistics

**Endpoint**: `GET /api/donation-stats`

**Query Parameters** (optional):
- `period`: week, month, quarter, year (default: month)
- `blood_group`: Filter by blood group

**Curl Commands**:

Monthly statistics:
```bash
curl -X GET "http://localhost:8000/api/donation-stats?period=month" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

By blood group:
```bash
curl -X GET "http://localhost:8000/api/donation-stats?blood_group=O%2B" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

**Expected Response** (200 OK):
```json
{
  "period": "month",
  "total_donations": 150,
  "completed_donations": 140,
  "rejected_donations": 10,
  "average_volume": 448.5,
  "by_blood_group": {
    "O+": 45,
    "O-": 25,
    "A+": 30,
    "A-": 15,
    "B+": 20,
    "B-": 10,
    "AB+": 25,
    "AB-": 5
  },
  "by_type": {
    "whole_blood": 120,
    "plasma": 20,
    "platelets": 10,
    "red_cells": 0
  },
  "trend": [
    { "date": "2024-01-01", "count": 5 },
    { "date": "2024-01-02", "count": 6 },
    { "date": "2024-01-03", "count": 4 }
  ]
}
```

---

## Common HTTP Status Codes

| Code | Meaning | Scenario |
|------|---------|----------|
| 200 | OK | Successful GET/DELETE request |
| 201 | Created | Successful POST request |
| 400 | Bad Request | Invalid request format |
| 401 | Unauthorized | Missing/invalid authentication token |
| 403 | Forbidden | User lacks required permissions |
| 404 | Not Found | Resource doesn't exist |
| 422 | Unprocessable Entity | Validation errors |
| 429 | Too Many Requests | Rate limit exceeded |
| 500 | Server Error | Internal server error |

---

## Testing with Postman

### Import Collection

1. Open Postman
2. Click "Import" → "Paste Raw Text"
3. Paste the following collection:

```json
{
  "info": {
    "name": "Donation Eligibility API",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "Dashboard",
      "request": {
        "method": "GET",
        "url": "{{baseUrl}}/donation-eligibility/dashboard",
        "header": [
          {
            "key": "Authorization",
            "value": "Bearer {{token}}"
          }
        ]
      }
    },
    {
      "name": "Check Eligibility",
      "request": {
        "method": "GET",
        "url": "{{baseUrl}}/donation-eligibility/check/1",
        "header": [
          {
            "key": "Authorization",
            "value": "Bearer {{token}}"
          }
        ]
      }
    }
  ]
}
```

### Set Environment Variables

1. Click "Environment" icon
2. Create new environment
3. Set variables:
   - `baseUrl`: http://localhost:8000
   - `token`: YOUR_BEARER_TOKEN

---

## Tips for Testing

1. **Always include Authorization header** with valid token
2. **Validate JSON** before sending POST/PUT requests
3. **Check status codes** for success/error
4. **Review error messages** for validation issues
5. **Use correct date formats** (YYYY-MM-DD or ISO8601)
6. **Handle async notifications** - they may take time to queue and send

---

## Troubleshooting

### 401 Unauthorized
- Ensure token is included in header
- Verify token hasn't expired
- Check token format: `Bearer <token>`

### 422 Validation Error
- Review error messages in response
- Ensure all required fields are provided
- Check data types match expected format
- Verify enum values are correct

### 404 Not Found
- Verify resource ID is correct
- Check resource exists in database
- Ensure URL path is spelled correctly

### 500 Internal Server Error
- Check Laravel logs: `storage/logs/laravel.log`
- Verify database connection
- Check queue worker is running for notifications

---

**Last Updated**: January 2024
