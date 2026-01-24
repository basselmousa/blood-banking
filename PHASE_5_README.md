# Phase 5: Mobile & Offline Capabilities - Complete Implementation Guide

## Overview

Phase 5 transforms the blood banking SaaS platform into a fully mobile-enabled, offline-first system. This phase introduces native mobile support, offline data synchronization with conflict resolution, push notifications, biometric authentication, and QR code scanning capabilities.

**Key Achievements:**
- ✅ 5 new database tables (mobile_devices, offline_syncs, push_notifications, biometric_auths, qr_code_scans)
- ✅ 5 comprehensive models with relationships and business logic
- ✅ 5 services handling mobile device management, offline sync, push notifications, biometric auth, and QR scanning
- ✅ 5 API controllers with 50+ endpoints
- ✅ Advanced offline-first architecture with conflict resolution
- ✅ Multi-channel push notifications (Firebase Cloud Messaging + Apple Push Notification Service)
- ✅ Secure biometric authentication with encryption and lockout protection
- ✅ QR code scanning and audit trail system

---

## Architecture Overview

### System Components

```
┌─────────────────────────────────────────────────────────────┐
│                    MOBILE & OFFLINE SYSTEM                   │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  ┌──────────────────────────────────────────────────────┐   │
│  │  MOBILE APPS (iOS, Android, Web)                     │   │
│  │  - Progressive Web App (PWA)                         │   │
│  │  - Native iOS with Swift                            │   │
│  │  - Native Android with Kotlin/Java                  │   │
│  └────────────────────┬─────────────────────────────────┘   │
│                       │                                       │
│                       ▼                                       │
│  ┌──────────────────────────────────────────────────────┐   │
│  │  CLIENT-SIDE (Device Storage)                        │   │
│  │  - IndexedDB / SQLite for local caching             │   │
│  │  - Service Workers for offline mode                 │   │
│  │  - Local change tracking                            │   │
│  └────────────────────┬─────────────────────────────────┘   │
│                       │                                       │
│  ┌────────────────────▼─────────────────────────────────┐   │
│  │  DEVICE MANAGEMENT LAYER                             │   │
│  │  - Device Registration (MobileDeviceService)         │   │
│  │  - Token Management (FCM, APNS)                      │   │
│  │  - Device Status Tracking                            │   │
│  └────────────────────┬─────────────────────────────────┘   │
│                       │                                       │
│  ┌────────────────────▼─────────────────────────────────┐   │
│  │  SYNCHRONIZATION LAYER                               │   │
│  │  - Offline Change Recording (OfflineSyncService)     │   │
│  │  - Conflict Resolution Engine                        │   │
│  │  - Automatic Sync when Online                        │   │
│  │  - Retry Logic with Exponential Backoff              │   │
│  └────────────────────┬─────────────────────────────────┘   │
│                       │                                       │
│  ┌────────────────────▼─────────────────────────────────┐   │
│  │  NOTIFICATION & SECURITY LAYER                       │   │
│  │  - Push Notifications (PushNotificationService)      │   │
│  │  - Biometric Auth (BiometricAuthService)            │   │
│  │  - QR Code Scanning (QrCodeScanService)             │   │
│  └────────────────────┬─────────────────────────────────┘   │
│                       │                                       │
│  ┌────────────────────▼─────────────────────────────────┐   │
│  │  DATABASE LAYER (Multi-Tenant)                       │   │
│  │  - mobile_devices table (device registry)            │   │
│  │  - offline_syncs table (change tracking)             │   │
│  │  - push_notifications table (notification log)       │   │
│  │  - biometric_auths table (secure auth tokens)        │   │
│  │  - qr_code_scans table (scan audit trail)           │   │
│  └──────────────────────────────────────────────────────┘   │
│                                                               │
└─────────────────────────────────────────────────────────────┘
```

---

## Database Schema

### 1. Mobile Devices Table

Tracks all registered mobile devices for push notifications and device management.

```sql
CREATE TABLE mobile_devices (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL FOREIGN KEY,
    tenant_id BIGINT NOT NULL FOREIGN KEY,
    device_id VARCHAR(255) UNIQUE NOT NULL,
    device_type ENUM('ios','android','web') NOT NULL,
    os_version VARCHAR(50) NOT NULL,
    app_version VARCHAR(50) NOT NULL,
    device_name VARCHAR(255) NULL,
    
    -- Push Notification Tokens
    fcm_token VARCHAR(255) NULL (Firebase Cloud Messaging for Android/Web),
    apns_token VARCHAR(255) NULL (Apple Push Notification Service for iOS),
    
    -- Device Preferences & Status
    notifications_enabled BOOLEAN DEFAULT true,
    biometric_enabled BOOLEAN DEFAULT false,
    last_active_at TIMESTAMP NULL,
    last_sync_at TIMESTAMP NULL,
    
    -- Metadata
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Key Fields:**
- `device_id`: Unique identifier generated by mobile client
- `device_type`: ios | android | web
- `fcm_token`: Firebase token for Android/Web push notifications
- `apns_token`: Apple token for iOS push notifications
- `last_active_at`: Track device activity for cleanup
- `last_sync_at`: Monitor offline sync completion

**Use Cases:**
- Device registration when user logs in on mobile
- Updating push notification tokens
- Tracking device status and activity
- Cleaning up inactive devices

---

### 2. Offline Syncs Table

Records all offline changes and manages synchronization with conflict resolution.

```sql
CREATE TABLE offline_syncs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL FOREIGN KEY,
    mobile_device_id BIGINT NOT NULL FOREIGN KEY,
    tenant_id BIGINT NOT NULL FOREIGN KEY,
    
    -- Change Data
    entity_type VARCHAR(50) NOT NULL (donor, patient, appointment, etc.),
    entity_id BIGINT NOT NULL,
    action ENUM('create','update','delete') NOT NULL,
    data LONGTEXT JSON NOT NULL (complete entity data),
    
    -- Sync Status
    status ENUM('pending','synced','failed','conflict') DEFAULT 'pending',
    synced_at TIMESTAMP NULL,
    
    -- Conflict Resolution
    conflict_data LONGTEXT JSON NULL (server version of data),
    error_message TEXT NULL,
    
    -- Retry Logic
    retry_count INT DEFAULT 0,
    last_retry_at TIMESTAMP NULL,
    
    -- Metadata
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Key Fields:**
- `status`: pending | synced | failed | conflict
- `data`: Full entity JSON sent from mobile (mobile version)
- `conflict_data`: Server version if conflict detected
- `retry_count`: Auto-retry up to 5 times
- `error_message`: Reason for failure

**Sync States:**
1. **pending**: Waiting to be synced
2. **synced**: Successfully applied to server
3. **failed**: Failed to apply (manual retry needed)
4. **conflict**: Change conflicts with server version (manual resolution needed)

**Conflict Resolution Strategies:**
- `server`: Use server's data (discard mobile changes)
- `mobile`: Use mobile's data (overwrite server)

**Use Cases:**
- Record changes made offline
- Apply changes when device comes online
- Handle conflicts when same record modified both places
- Audit trail of offline operations

---

### 3. Push Notifications Table

Maintains push notification history and delivery status.

```sql
CREATE TABLE push_notifications (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL FOREIGN KEY,
    mobile_device_id BIGINT NOT NULL FOREIGN KEY,
    tenant_id BIGINT NOT NULL FOREIGN KEY,
    
    -- Notification Content
    title VARCHAR(200) NOT NULL,
    body TEXT NOT NULL,
    action_url VARCHAR(500) NULL,
    notification_type ENUM(
        'appointment_reminder',
        'eligibility_update',
        'donation_request',
        'system_alert',
        'custom'
    ) NOT NULL,
    data LONGTEXT JSON NULL (custom payload),
    
    -- Delivery Status
    status ENUM('pending','sent','failed','read') DEFAULT 'pending',
    sent_at TIMESTAMP NULL,
    read_at TIMESTAMP NULL,
    
    -- Metadata
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Notification Types:**
- `appointment_reminder`: Upcoming appointment notification
- `eligibility_update`: Donor eligibility status changed
- `donation_request`: Request for new donation
- `system_alert`: Critical system notifications
- `custom`: Custom messages

**Status Flow:**
- pending → sent → read
- pending → sent → failed (for delivery failures)

**Use Cases:**
- Send appointment reminders
- Notify donors of eligibility changes
- Request donations
- System alerts and announcements
- Custom messages from admins

---

### 4. Biometric Auths Table

Securely stores biometric authentication tokens with lockout protection.

```sql
CREATE TABLE biometric_auths (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL FOREIGN KEY,
    mobile_device_id BIGINT NOT NULL FOREIGN KEY,
    
    -- Biometric Type & Token
    auth_type ENUM('fingerprint','face_recognition','iris') NOT NULL,
    encrypted_token LONGTEXT NOT NULL (Laravel encrypted),
    
    -- Security
    failed_attempts INT DEFAULT 0,
    locked_until TIMESTAMP NULL (15 minutes after 5 failures),
    
    -- Management
    is_enabled BOOLEAN DEFAULT true,
    is_primary BOOLEAN DEFAULT false (primary auth method),
    last_used_at TIMESTAMP NULL,
    
    -- Metadata
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Auth Types:**
- `fingerprint`: Fingerprint recognition
- `face_recognition`: Face recognition (FaceID/FaceDetection)
- `iris`: Iris recognition

**Security Features:**
- 5 failed attempts trigger 15-minute lockout
- Encrypted token storage (AES-256)
- Track last successful use
- Primary biometric designation
- Enable/disable per biometric

**Use Cases:**
- Mobile app authentication
- Device security enhancement
- Two-factor authentication
- Biometric login

---

### 5. QR Code Scans Table

Audit trail for QR code scanning operations.

```sql
CREATE TABLE qr_code_scans (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL FOREIGN KEY,
    mobile_device_id BIGINT NOT NULL FOREIGN KEY,
    tenant_id BIGINT NOT NULL FOREIGN KEY,
    
    -- QR Code Data
    entity_type VARCHAR(50) NOT NULL (donor, patient, appointment, camp, inventory),
    entity_id BIGINT NOT NULL,
    qr_code_value VARCHAR(255) NOT NULL (e.g., "DONOR:123"),
    
    -- Scan Status
    scan_status ENUM(
        'success',
        'failed',
        'not_found',
        'invalid'
    ) NOT NULL,
    
    -- Metadata
    scan_metadata LONGTEXT JSON (location, timestamp, device info),
    
    -- Audit
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Scan Statuses:**
- `success`: Valid QR code for existing entity
- `failed`: Scanning failed (technical issue)
- `not_found`: Valid format but entity doesn't exist
- `invalid`: Invalid QR code format

**QR Code Format:**
- Standard: `ENTITY_TYPE:ENTITY_ID`
- Example: `DONOR:12345`, `PATIENT:67890`, `APPOINTMENT:11111`

**Metadata Contents:**
```json
{
    "timestamp": "2024-01-24T10:30:00Z",
    "device_id": "DEVICE-abc123",
    "os": "ios",
    "location": {
        "latitude": 40.7128,
        "longitude": -74.0060
    }
}
```

**Use Cases:**
- Scan donor cards to verify identity
- Quick patient lookup at donation camps
- Appointment check-in
- Inventory tracking
- Audit trail of all scanning operations

---

## Models & Relationships

### MobileDevice Model

```php
class MobileDevice extends Model {
    use BelongsToTenant;
    
    // Relationships
    belongsTo User
    belongsTo Tenant
    hasMany OfflineSync
    hasMany PushNotification
    hasMany BiometricAuth
    hasMany QrCodeScan
    
    // Methods
    markAsActive()              // Update last_active_at
    markAsSynced()              // Update last_sync_at
    updateTokens($fcm, $apns)   // Update push tokens
    
    // Scopes
    withNotificationsEnabled()  // notifications_enabled = true
    ofType($type)               // device_type filter
    recentlyActive($minutes)    // Last active within X minutes
}
```

### OfflineSync Model

```php
class OfflineSync extends Model {
    use BelongsToTenant;
    
    // Relationships
    belongsTo User
    belongsTo MobileDevice
    belongsTo Tenant
    
    // Methods
    getEntity()                 // Get actual entity from data
    markAsSynced()              // Mark as synced
    markAsFailed($error)        // Mark as failed
    markAsConflict($conflict)   // Mark as conflict
    shouldRetry()               // Check if should retry
    
    // Scopes
    pending()                   // status = pending
    failed()                    // status = failed
    conflicts()                 // status = conflict
    forEntity($type, $id)       // Filter by entity
}
```

### PushNotification Model

```php
class PushNotification extends Model {
    use BelongsToTenant;
    
    // Relationships
    belongsTo User
    belongsTo MobileDevice
    belongsTo Tenant
    
    // Methods
    markAsSent()                // Mark as sent
    markAsRead()                // Mark as read
    markAsFailed($reason)       // Mark as failed
    shouldRetry()               // Check if should retry
    
    // Scopes
    pending()                   // status = pending
    sent()                      // status = sent
    read()                      // status = read
    ofType($type)               // Filter by type
    forUser($userId)            // Filter by user
}
```

### BiometricAuth Model

```php
class BiometricAuth extends Model {
    // Relationships
    belongsTo User
    belongsTo MobileDevice
    
    // Methods
    getDecryptedToken()         // Decrypt token
    setEncryptedToken($token)   // Encrypt and store
    recordFailedAttempt()       // Increment failures, lock if >= 5
    recordSuccessfulAuth()      // Clear failures
    isLocked()                  // Check if locked
    unlock()                    // Clear lockout
    setPrimary()                // Set as primary
    
    // Scopes
    enabled()                   // is_enabled = true
    primary()                   // is_primary = true
    ofType($type)               // Filter by auth_type
    forUser($userId)            // Filter by user
}
```

### QrCodeScan Model

```php
class QrCodeScan extends Model {
    use BelongsToTenant;
    
    // Relationships
    belongsTo User
    belongsTo MobileDevice
    belongsTo Tenant
    
    // Methods
    getScannedEntity()          // Get actual entity
    
    // Scopes
    success()                   // scan_status = success
    failed()                    // scan_status = failed
    forEntity($type, $id)       // Filter by entity
    recentScans($hours)         // Last X hours
}
```

---

## Services Layer

### MobileDeviceService (250+ lines)

**Device Registration & Management**

```php
// Register a new device
registerDevice($userId, $tenantId, $deviceData)
// Returns: MobileDevice instance

// Update FCM/APNS tokens
updateDeviceTokens($deviceId, $fcmToken, $apnsToken)
// Returns: Updated MobileDevice

// Get all devices for user
getDevicesForUser($userId)
// Returns: Collection of MobileDevices

// Get all devices for tenant
getDevicesForTenant($tenantId)
// Returns: Collection of MobileDevices

// Mark device as active
markDeviceActive($deviceId)
// Returns: Updated MobileDevice

// Mark device as synced
markDeviceSynced($deviceId)
// Returns: Updated MobileDevice

// Toggle notifications
toggleNotifications($deviceId, $enabled)
// Returns: Updated MobileDevice

// Toggle biometric
toggleBiometric($deviceId, $enabled)
// Returns: Updated MobileDevice

// Unregister device
unregisterDevice($deviceId)
// Removes device and related data

// Get active devices
getActiveDevices($tenantId, $minutes = 30)
// Returns: Recently active devices

// Cleanup inactive devices
cleanupInactiveDevices($days = 90)
// Deletes devices inactive for X days

// Get device statistics
getDeviceStats($tenantId)
// Returns: {
//   'total_devices': 150,
//   'ios_devices': 60,
//   'android_devices': 80,
//   'web_devices': 10,
//   'notifications_enabled': 145,
//   'biometric_enabled': 120,
//   'active_24h': 140,
//   'last_sync': '2024-01-24T10:30:00Z'
// }
```

---

### OfflineSyncService (300+ lines)

**Offline Change Recording & Synchronization**

```php
// Record an offline change
recordOfflineChange(
    $userId,
    $deviceId,
    $entityType,
    $entityId,
    $action,        // create|update|delete
    $data,          // Entity data
    $changes        // Change details
)
// Returns: OfflineSync instance

// Get pending syncs
getPendingSyncs($userId, $deviceId)
// Returns: Collection of pending syncs

// Get sync status
getSyncStatus($userId, $deviceId)
// Returns: {
//   'pending': 5,
//   'synced': 50,
//   'failed': 2,
//   'conflicts': 1
// }

// Process all pending syncs
processPendingSyncs($userId, $deviceId)
// Returns: {
//   'successful': 50,
//   'failed': 2,
//   'conflicts': 1
// }

// Apply a single sync
applySync($sync)
// Creates/updates/deletes entity based on sync data
// Returns: boolean

// Resolve conflict
resolveConflict(
    $syncId,
    $resolution,    // 'server' or 'mobile'
    $serverData
)
// Returns: Resolved OfflineSync

// Retry failed syncs
retryFailedSyncs($userId, $deviceId)
// Retries failed syncs up to 5 times
// Returns: Count of retried syncs

// Get sync history
getSyncHistory($userId, $deviceId, $limit = 50)
// Returns: Recent syncs (sorted by date)

// Clear old syncs
clearOldSyncs($days = 90)
// Deletes syncs older than X days
```

**Conflict Resolution Logic:**
1. When server data exists and differs from mobile data
2. Mark sync as 'conflict' status
3. Store both versions (data + conflict_data)
4. Wait for manual resolution
5. Resolution options:
   - `server`: Discard mobile changes, use server version
   - `mobile`: Overwrite server with mobile changes

---

### PushNotificationService (350+ lines)

**Multi-Channel Push Notifications (FCM + APNS)**

```php
// Send notification to single device
sendNotification(
    $userId,
    $deviceId,      // Optional
    $title,
    $body,
    $type,
    $actionUrl,
    $data
)
// Returns: PushNotification instance

// Send to multiple users
sendBulkNotifications(
    $userIds,
    $tenantId,
    $title,
    $body,
    $type,
    $actionUrl,
    $data
)
// Returns: {
//   'sent': 50,
//   'failed': 2,
//   'total': 52
// }

// Send to all tenant users
sendToTenantUsers(
    $tenantId,
    $title,
    $body,
    $type,
    $actionUrl,
    $excludeUserIds,
    $data
)
// Returns: {
//   'sent': 150,
//   'failed': 5,
//   'total': 155
// }

// Send appointment reminder
sendAppointmentReminder($appointmentId)
// Automatically generates reminder notification

// Send eligibility update
sendEligibilityUpdate($donorId, $isEligible)
// Notifies donor of eligibility change

// Send via FCM (Firebase Cloud Messaging)
sendViaFCM($notification, $device)
// Android & Web push notifications
// Returns: boolean

// Send via APNS (Apple Push Notification Service)
sendViaAPNS($notification, $device)
// iOS push notifications
// Returns: boolean

// Mark notification as read
markAsRead($notificationId)
// Returns: Updated notification

// Get user notifications
getUserNotifications($userId, $limit = 50)
// Returns: User's notifications

// Get unread count
getUnreadCount($userId)
// Returns: Integer count

// Get notification statistics
getNotificationStats($tenantId)
// Returns: {
//   'total': 1000,
//   'sent': 980,
//   'failed': 20,
//   'read': 850,
//   'unread': 130,
//   'by_type': {...},
//   'success_rate': 98%
// }

// Delete old notifications
deleteOldNotifications($days = 90)
// Deletes notifications older than X days
```

**Push Provider Integration:**

**Firebase Cloud Messaging (FCM) - Android/Web:**
```
POST https://fcm.googleapis.com/fcm/send
Authorization: key=FIREBASE_API_KEY

{
    "to": "fcm_token",
    "notification": {
        "title": "Appointment Reminder",
        "body": "You have an appointment tomorrow"
    },
    "data": {
        "action_url": "/appointments/123",
        "type": "appointment_reminder"
    }
}
```

**Apple Push Notification Service (APNS) - iOS:**
```
Method: POST
Host: api.push.apple.com or api.sandbox.push.apple.com
Path: /3/device/{device_token}

{
    "aps": {
        "alert": {
            "title": "Appointment Reminder",
            "body": "You have an appointment tomorrow"
        },
        "sound": "default",
        "badge": 1
    }
}
```

---

### BiometricAuthService (200+ lines)

**Biometric Authentication Management**

```php
// Register biometric authentication
registerBiometric(
    $userId,
    $deviceId,
    $authType,          // fingerprint|face_recognition|iris
    $biometricData
)
// Returns: {
//   'success': true,
//   'biometric_id': 123,
//   'auth_type': 'fingerprint'
// }

// Authenticate with biometric
authenticateWithBiometric($userId, $deviceId, $authType)
// Returns: {
//   'success': true,
//   'biometric_id': 123
// }

// Record failed attempt
recordFailedAttempt($biometricId)
// Increments failed_attempts
// Locks if >= 5 failures
// Returns: Lockout info

// Get biometrics for user
getBiometricsForUser($userId, $deviceId = null)
// Returns: Collection of BiometricAuth

// Disable biometric
disableBiometric($biometricId)
// Returns: Updated BiometricAuth

// Set as primary
setPrimaryBiometric($biometricId)
// Returns: Updated BiometricAuth

// Unlock biometric (after lockout)
unlockBiometric($biometricId)
// Clears locked_until
// Returns: Updated BiometricAuth

// Delete biometric
deleteBiometric($biometricId)
// Returns: boolean

// Verify availability
verifyBiometricAvailability($userId, $deviceId, $authType)
// Returns: {
//   'available': true,
//   'biometric_id': 123
// }

// Get biometric statistics
getBiometricStats($tenantId)
// Returns: {
//   'total': 150,
//   'enabled': 140,
//   'fingerprint': 90,
//   'face_recognition': 40,
//   'iris': 20
// }
```

**Security Features:**
- 5 failed attempts = 15-minute lockout
- Encrypted token storage (AES-256)
- Per-device biometric registration
- Primary biometric designation
- Failed attempt logging for audit trail

---

### QrCodeScanService (250+ lines)

**QR Code Scanning & Entity Linking**

```php
// Record a single QR scan
recordScan($userId, $deviceId, $qrCodeValue, $scanMetadata)
// Returns: {
//   'success': true|false,
//   'scan_id': 123,
//   'scan_status': 'success|failed|not_found|invalid',
//   'entity_type': 'DONOR',
//   'entity_id': 456,
//   'entity': {/* entity data */}
// }

// Record multiple scans
bulkRecordScans($userId, $deviceId, $qrCodeValues, $scanMetadata)
// Returns: {
//   'total': 10,
//   'successful': 9,
//   'failed': 1,
//   'scans': [...]
// }

// Parse QR code value
parseQrCode($qrCodeValue)
// Format: "TYPE:ID"
// Returns: {
//   'entity_type': 'DONOR',
//   'entity_id': 123
// }

// Get entity being scanned
getEntity($entityType, $entityId)
// Returns: Eloquent model or null

// Get scan history for device
getScanHistory($deviceId, $entityType, $limit)
// Returns: Collection of scans

// Get scans for entity
getScansForEntity($entityType, $entityId, $limit)
// Returns: All scans for that entity

// Get recent scans
getRecentScans($tenantId, $hours, $limit)
// Returns: Scans from last X hours

// Get failed scans
getFailedScans($tenantId, $limit)
// Returns: All failed/invalid scans

// Get scan statistics
getScanStats($tenantId)
// Returns: {
//   'total_scans': 5000,
//   'successful_scans': 4950,
//   'failed_scans': 30,
//   'not_found_scans': 10,
//   'invalid_scans': 10,
//   'by_entity_type': {
//       'donors': 2000,
//       'patients': 1500,
//       'appointments': 1000,
//       'camps': 350,
//       'inventory': 150
//   }
// }

// Generate QR code value
generateQrCode($entityType, $entityId)
// Returns: "DONOR:123"

// Validate QR code
validateQrForEntity($qrCodeValue, $expectedType, $expectedId)
// Returns: {
//   'valid': true|false,
//   'message': 'error message if invalid',
//   'entity': {/* entity data */}
// }

// Get user scan history
getScansForUser($userId, $limit)
// Returns: User's scans

// Get scans by status
getScansByStatus($tenantId, $scanStatus)
// Returns: Collection of scans with given status
```

**QR Code Format:**
- Standard: `ENTITY_TYPE:ENTITY_ID`
- Examples:
  - `DONOR:12345` - Scan donor card
  - `PATIENT:67890` - Lookup patient
  - `APPOINTMENT:11111` - Check-in appointment
  - `CAMP:22222` - Register donation camp
  - `INVENTORY:33333` - Track blood inventory

---

## API Endpoints (50+ endpoints)

### Base URL
```
/api/v1/saas
```

### 1. Mobile Device Management (11 endpoints)

```
POST   /mobile-devices/register           Register new device
GET    /mobile-devices                    List user's devices
GET    /mobile-devices/{id}               Get device details
PUT    /mobile-devices/{id}               Update device info
PUT    /mobile-devices/{id}/tokens        Update FCM/APNS tokens
POST   /mobile-devices/{id}/mark-active   Mark as active
POST   /mobile-devices/{id}/mark-synced   Mark as synced
POST   /mobile-devices/{id}/toggle-notifications
POST   /mobile-devices/{id}/toggle-biometric
GET    /mobile-devices/active             Get active devices
GET    /mobile-devices/stats              Get device statistics
DELETE /mobile-devices/{id}               Unregister device
```

### 2. Offline Synchronization (10 endpoints)

```
POST   /offline-syncs/record              Record offline changes
POST   /offline-syncs/process             Process pending syncs
POST   /offline-syncs/retry               Retry failed syncs
GET    /offline-syncs/pending             Get pending syncs
GET    /offline-syncs/status              Get sync status
GET    /offline-syncs/history             Get sync history
GET    /offline-syncs/conflicts           Get conflicts
GET    /offline-syncs/{id}                Get sync details
POST   /offline-syncs/{id}/resolve        Resolve conflict
POST   /offline-syncs/{id}/mark-synced    Mark as synced
DELETE /offline-syncs/{id}                Delete sync record
```

### 3. Push Notifications (12 endpoints)

```
POST   /push-notifications/send           Send to single user
POST   /push-notifications/send-bulk      Send to multiple users
POST   /push-notifications/send-tenant    Send to all tenant users
POST   /push-notifications/appointment-reminder
POST   /push-notifications/eligibility-update
GET    /push-notifications                List user notifications
GET    /push-notifications/{id}           Get notification details
POST   /push-notifications/{id}/mark-read Mark as read
GET    /push-notifications/unread/count   Get unread count
GET    /push-notifications/stats          Get statistics
DELETE /push-notifications/{id}           Delete notification
```

### 4. Biometric Authentication (11 endpoints)

```
POST   /biometric-auth/register           Register biometric
POST   /biometric-auth/authenticate       Authenticate with biometric
GET    /biometric-auth                    List user's biometrics
GET    /biometric-auth/{id}               Get biometric details
GET    /biometric-auth/verify             Verify availability
POST   /biometric-auth/{id}/set-primary   Set as primary
POST   /biometric-auth/{id}/disable       Disable biometric
POST   /biometric-auth/{id}/unlock        Unlock (after lockout)
POST   /biometric-auth/{id}/record-failure
GET    /biometric-auth/stats              Get statistics
DELETE /biometric-auth/{id}               Delete biometric
```

### 5. QR Code Scanning (12 endpoints)

```
POST   /qr-scans                          Scan QR code
POST   /qr-scans/bulk                     Scan multiple QR codes
GET    /qr-scans                          Get scan history
GET    /qr-scans/recent                   Get recent scans
GET    /qr-scans/failed                   Get failed scans
GET    /qr-scans/user                     Get user's scans
GET    /qr-scans/{id}                     Get scan details
GET    /qr-scans/entity/{type}/{id}       Get scans for entity
POST   /qr-scans/validate                 Validate QR code
POST   /qr-scans/generate                 Generate QR value
GET    /qr-scans/stats                    Get statistics
DELETE /qr-scans/{id}                     Delete scan record
```

---

## API Request/Response Examples

### Example 1: Register Mobile Device

**Request:**
```bash
POST /api/v1/saas/mobile-devices/register
Authorization: Bearer {token}
Content-Type: application/json

{
    "device_id": "DEVICE-abc123def456",
    "device_type": "ios",
    "os_version": "15.0",
    "app_version": "1.0.0",
    "fcm_token": "fcm_token_here",
    "apns_token": "apns_token_here"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Device registered successfully",
    "device": {
        "id": 1,
        "device_id": "DEVICE-abc123def456",
        "device_type": "ios",
        "os_version": "15.0",
        "app_version": "1.0.0",
        "notifications_enabled": true,
        "biometric_enabled": false,
        "fcm_token": "fcm_token_here",
        "apns_token": "apns_token_here",
        "last_active_at": "2024-01-24T10:30:00Z",
        "last_sync_at": null,
        "created_at": "2024-01-24T10:30:00Z"
    }
}
```

---

### Example 2: Record Offline Changes

**Request:**
```bash
POST /api/v1/saas/offline-syncs/record
Authorization: Bearer {token}
Content-Type: application/json

{
    "device_id": "DEVICE-abc123def456",
    "changes": [
        {
            "entity_type": "donor",
            "entity_id": 123,
            "action": "update",
            "data": {
                "name": "John Doe",
                "email": "john@example.com",
                "phone": "555-1234"
            }
        },
        {
            "entity_type": "appointment",
            "entity_id": 456,
            "action": "create",
            "data": {
                "donor_id": 123,
                "appointment_date": "2024-02-01",
                "appointment_time": "14:00"
            }
        }
    ]
}
```

**Response:**
```json
{
    "success": true,
    "message": "Offline changes recorded",
    "recorded": 2,
    "changes": [
        {
            "id": 1,
            "status": "pending",
            "entity_type": "donor",
            "entity_id": 123,
            "action": "update"
        },
        {
            "id": 2,
            "status": "pending",
            "entity_type": "appointment",
            "entity_id": 456,
            "action": "create"
        }
    ]
}
```

---

### Example 3: Send Push Notification

**Request:**
```bash
POST /api/v1/saas/push-notifications/send
Authorization: Bearer {token}
Content-Type: application/json

{
    "user_id": 5,
    "title": "Appointment Reminder",
    "body": "You have an appointment tomorrow at 2:00 PM",
    "notification_type": "appointment_reminder",
    "action_url": "/appointments/789",
    "data": {
        "appointment_id": 789,
        "clinic": "Central Blood Bank"
    }
}
```

**Response:**
```json
{
    "success": true,
    "message": "Push notification sent",
    "notification": {
        "id": 101,
        "user_id": 5,
        "title": "Appointment Reminder",
        "body": "You have an appointment tomorrow at 2:00 PM",
        "notification_type": "appointment_reminder",
        "status": "sent",
        "sent_at": "2024-01-24T10:30:00Z",
        "read_at": null
    }
}
```

---

### Example 4: Register Biometric Authentication

**Request:**
```bash
POST /api/v1/saas/biometric-auth/register
Authorization: Bearer {token}
Content-Type: application/json

{
    "device_id": "DEVICE-abc123def456",
    "auth_type": "fingerprint",
    "biometric_data": "base64_encoded_fingerprint_data"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Biometric registered successfully",
    "biometric": {
        "biometric_id": 1,
        "auth_type": "fingerprint",
        "success": true
    }
}
```

---

### Example 5: Scan QR Code

**Request:**
```bash
POST /api/v1/saas/qr-scans
Authorization: Bearer {token}
Content-Type: application/json

{
    "device_id": "DEVICE-abc123def456",
    "qr_code_value": "DONOR:12345",
    "scan_metadata": {
        "location": {
            "latitude": 40.7128,
            "longitude": -74.0060
        }
    }
}
```

**Response:**
```json
{
    "success": true,
    "message": "QR code scanned",
    "scan": {
        "scan_id": 501,
        "scan_status": "success",
        "entity_type": "DONOR",
        "entity_id": 12345,
        "entity": {
            "id": 12345,
            "name": "John Doe",
            "blood_type": "O+",
            "eligibility_status": "eligible"
        }
    }
}
```

---

## Client Integration Guide

### Progressive Web App (PWA)

**Service Worker Registration:**
```javascript
// Register service worker
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js')
        .then(registration => {
            console.log('Service Worker registered');
        });
}

// Handle background sync
if ('sync' in ServiceWorkerRegistration.prototype) {
    navigator.serviceWorker.ready.then(registration => {
        registration.sync.register('sync-offline-data');
    });
}
```

**IndexedDB for Local Caching:**
```javascript
// Store data locally
const db = await openDB('blood-banking', 1, {
    upgrade(db) {
        db.createObjectStore('donors');
        db.createObjectStore('appointments');
        db.createObjectStore('pendingChanges');
    }
});

// Record offline change
await db.put('pendingChanges', {
    entityType: 'donor',
    entityId: 123,
    action: 'update',
    data: {...},
    timestamp: Date.now()
});
```

---

### Native Mobile App (iOS/Android)

**Device Registration (React Native):**
```javascript
import * as Device from 'expo-device';
import * as Notifications from 'expo-notifications';

const registerDevice = async (token) => {
    const deviceId = Device.modelId;
    
    const response = await fetch('/api/v1/saas/mobile-devices/register', {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            device_id: deviceId,
            device_type: Device.osName.toLowerCase(),
            os_version: Device.osVersion,
            app_version: '1.0.0',
            fcm_token: fcmToken,
            apns_token: apnsToken
        })
    });
    
    return response.json();
};
```

**Biometric Authentication (React Native):**
```javascript
import * as LocalAuthentication from 'expo-local-authentication';

const authenticateWithBiometric = async (authType) => {
    try {
        const result = await LocalAuthentication.authenticateAsync({
            disableDeviceFallback: false,
            reason: 'Authenticate to access the app'
        });
        
        if (result.success) {
            const response = await fetch('/api/v1/saas/biometric-auth/authenticate', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    device_id: deviceId,
                    auth_type: authType
                })
            });
            
            return response.json();
        }
    } catch (error) {
        console.error('Biometric auth failed:', error);
    }
};
```

---

## Configuration & Environment

### Environment Variables

```env
# Push Notification Configuration
FIREBASE_API_KEY=your_firebase_api_key
FIREBASE_PROJECT_ID=your_project_id

# Apple Push Notification Service
APNS_KEY_ID=your_key_id
APNS_TEAM_ID=your_team_id
APNS_BUNDLE_ID=com.bloodbanking.app
APNS_CERTIFICATE_PATH=/path/to/certificate.pem
```

### Laravel Configuration

```php
// config/saas.php
return [
    'mobile' => [
        'device_inactivity_days' => 90,
        'cleanup_enabled' => true,
    ],
    
    'offline_sync' => [
        'max_retries' => 5,
        'retry_delay_seconds' => 60,
        'cleanup_days' => 90,
    ],
    
    'push_notifications' => [
        'firebase' => [
            'enabled' => true,
            'api_key' => env('FIREBASE_API_KEY'),
        ],
        'apns' => [
            'enabled' => true,
            'key_id' => env('APNS_KEY_ID'),
            'team_id' => env('APNS_TEAM_ID'),
        ],
    ],
    
    'biometric' => [
        'failed_attempts_limit' => 5,
        'lockout_minutes' => 15,
    ],
];
```

---

## Data Migration

### Running Phase 5 Migrations

```bash
# Run migrations
php artisan migrate --path=database/migrations/2026_01_24_400001_*.php

# Or run all at once
php artisan migrate

# Seed Phase 5 data
php artisan db:seed --class="Database\\Seeders\\SaaS\\Phase5Seeder"
```

---

## Testing Phase 5

### Testing Mobile Device Registration

```bash
# Test device registration
curl -X POST http://localhost:8000/api/v1/saas/mobile-devices/register \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "device_id": "TEST-123",
    "device_type": "ios",
    "os_version": "15.0",
    "app_version": "1.0.0"
  }'
```

### Testing Offline Sync

```bash
# Test recording offline changes
curl -X POST http://localhost:8000/api/v1/saas/offline-syncs/record \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "device_id": "TEST-123",
    "changes": [{
      "entity_type": "donor",
      "entity_id": 1,
      "action": "update",
      "data": {"name": "Updated Name"}
    }]
  }'
```

---

## Performance Optimization

### Database Indexes

```sql
-- Mobile Devices
CREATE INDEX idx_mobile_devices_user_id ON mobile_devices(user_id);
CREATE INDEX idx_mobile_devices_tenant_id ON mobile_devices(tenant_id);
CREATE INDEX idx_mobile_devices_device_id ON mobile_devices(device_id);
CREATE INDEX idx_mobile_devices_last_active ON mobile_devices(last_active_at);

-- Offline Syncs
CREATE INDEX idx_offline_syncs_user_id ON offline_syncs(user_id);
CREATE INDEX idx_offline_syncs_device_id ON offline_syncs(mobile_device_id);
CREATE INDEX idx_offline_syncs_status ON offline_syncs(status);
CREATE INDEX idx_offline_syncs_entity ON offline_syncs(entity_type, entity_id);

-- Push Notifications
CREATE INDEX idx_push_notifications_user_id ON push_notifications(user_id);
CREATE INDEX idx_push_notifications_status ON push_notifications(status);
CREATE INDEX idx_push_notifications_type ON push_notifications(notification_type);

-- Biometric Auths
CREATE INDEX idx_biometric_auths_user_id ON biometric_auths(user_id);
CREATE INDEX idx_biometric_auths_device_id ON biometric_auths(mobile_device_id);
CREATE INDEX idx_biometric_auths_type ON biometric_auths(auth_type);

-- QR Code Scans
CREATE INDEX idx_qr_code_scans_user_id ON qr_code_scans(user_id);
CREATE INDEX idx_qr_code_scans_entity ON qr_code_scans(entity_type, entity_id);
CREATE INDEX idx_qr_code_scans_status ON qr_code_scans(scan_status);
```

### Caching Strategies

```php
// Cache device list
$devices = Cache::remember(
    "user.{$userId}.devices",
    3600, // 1 hour
    function () use ($userId) {
        return MobileDevice::where('user_id', $userId)->get();
    }
);

// Cache pending syncs count
$count = Cache::remember(
    "syncs.pending.{$userId}",
    300, // 5 minutes
    function () use ($userId) {
        return OfflineSync::where('user_id', $userId)
            ->where('status', 'pending')
            ->count();
    }
);
```

---

## Security Considerations

### 1. Authentication & Authorization

```php
// Only users can access their own devices
$this->authorize('view', $mobileDevice);

// Tenant isolation
$devices = MobileDevice::where('tenant_id', auth()->user()->tenant_id)->get();
```

### 2. Data Encryption

```php
// Biometric tokens stored encrypted
$biometric->update([
    'encrypted_token' => encrypt($token)
]);

// Decryption
$token = decrypt($biometric->encrypted_token);
```

### 3. Rate Limiting

```php
Route::middleware('throttle:60,1')->group(function () {
    Route::post('push-notifications/send', [...]);
    Route::post('offline-syncs/record', [...]);
});
```

### 4. Validation

```php
$validated = $request->validate([
    'qr_code_value' => 'required|string|max:255',
    'device_id' => 'required|string|exists:mobile_devices,device_id',
    'notification_type' => 'required|in:appointment_reminder,eligibility_update,...'
]);
```

---

## Troubleshooting

### Device Not Receiving Push Notifications

1. Check device tokens are valid and up to date
2. Verify FCM/APNS credentials in environment
3. Check notification status in database
4. Review push service logs

### Offline Sync Conflicts

1. Review conflict data in `conflict_data` field
2. Choose resolution: `server` or `mobile`
3. Call resolve endpoint with chosen strategy
4. Monitor sync status and retry if needed

### Biometric Authentication Failing

1. Check `failed_attempts` count
2. Verify device is not locked (`locked_until`)
3. Confirm biometric is enabled (`is_enabled`)
4. Review user's available biometrics

### QR Code Scan Issues

1. Verify QR format: `ENTITY_TYPE:ENTITY_ID`
2. Check entity exists in database
3. Review scan_status for specific error
4. Check scan_metadata for location/device info

---

## Future Enhancements

### Phase 6 - Advanced Analytics

- Mobile app usage analytics
- Offline sync performance metrics
- Push notification engagement tracking
- Biometric authentication success rates
- QR code scanning patterns

### Phase 7 - Machine Learning

- Predictive donor targeting
- Offline sync conflict prediction
- Notification delivery optimization
- Biometric authentication improvement

### Phase 8 - Advanced Security

- Multi-factor authentication
- Encryption at rest for offline data
- Secure enclave for biometric tokens
- Zero-knowledge architecture

---

## Deployment Checklist

- [ ] Run migrations: `php artisan migrate`
- [ ] Seed data: `php artisan db:seed --class="Database\\Seeders\\SaaS\\Phase5Seeder"`
- [ ] Set environment variables (Firebase, APNS)
- [ ] Configure push notification service
- [ ] Test mobile device registration
- [ ] Test offline sync
- [ ] Test push notifications
- [ ] Test biometric authentication
- [ ] Test QR code scanning
- [ ] Run tests: `php artisan test`
- [ ] Deploy to production

---

## Files Created in Phase 5

```
Database/
  ├── migrations/
  │   ├── 2026_01_24_400001_create_mobile_devices_table.php
  │   ├── 2026_01_24_400002_create_offline_syncs_table.php
  │   ├── 2026_01_24_400003_create_push_notifications_table.php
  │   ├── 2026_01_24_400004_create_biometric_auths_table.php
  │   └── 2026_01_24_400005_create_qr_code_scans_table.php
  └── seeders/SaaS/
      └── Phase5Seeder.php

App/
  ├── Models/
  │   ├── MobileDevice.php
  │   ├── OfflineSync.php
  │   ├── PushNotification.php
  │   ├── BiometricAuth.php
  │   └── QrCodeScan.php
  ├── Services/
  │   ├── MobileDeviceService.php
  │   ├── OfflineSyncService.php
  │   ├── PushNotificationService.php
  │   ├── BiometricAuthService.php
  │   └── QrCodeScanService.php
  ├── Http/Controllers/API/SaaS/
  │   ├── MobileDeviceController.php
  │   ├── OfflineSyncController.php
  │   ├── PushNotificationController.php
  │   ├── BiometricAuthController.php
  │   └── QrCodeScanController.php
  └── Jobs/SaaS/
      ├── SendPushNotificationJob.php
      └── ProcessOfflineSyncJob.php

Routes/
  └── saas-phase5.php

Documentation/
  └── PHASE_5_README.md (this file)
```

---

## Summary

Phase 5 successfully delivers mobile and offline capabilities to the blood banking SaaS platform:

**5 New Tables:** device management, offline sync, push notifications, biometric auth, QR scanning
**5 Models:** Comprehensive ORM with relationships and business methods
**5 Services:** Device management, offline sync, push notifications, biometric auth, QR scanning
**5 Controllers:** 50+ REST API endpoints
**2 Job Classes:** Async processing for push notifications and offline sync
**Comprehensive Documentation:** Full integration guide and troubleshooting

**Key Features:**
✅ Mobile device registration and management
✅ Offline-first architecture with sync
✅ Conflict resolution for offline changes
✅ Multi-channel push notifications (FCM + APNS)
✅ Secure biometric authentication
✅ QR code scanning with audit trail
✅ Full REST API with 50+ endpoints
✅ Multi-tenant isolation throughout
✅ Async job processing

**Impact:**
- Enables field team operations
- Supports offline/unreliable networks
- Improves user engagement via push notifications
- Enhances security with biometrics
- Streamlines data entry with QR codes

Phase 5 marks a major milestone in transforming blood banking into a truly mobile-first platform!
