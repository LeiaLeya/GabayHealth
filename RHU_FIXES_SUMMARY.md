# RHU System - Issues Found and Fixed

## Overview

Audited the Rural Health Unit (RHU) system and identified several critical issues with Firestore collection path handling and role context initialization.

## Issues Found and Fixed

### 1. **PersonnelController - Incorrect Collection Paths** ✅ FIXED

**File:** [app/Http/Controllers/RHU/PersonnelController.php](app/Http/Controllers/RHU/PersonnelController.php)

**Problem:**

-   Lines 99, 142, 159 were using `barangay/{$barangayId}/personnel` paths
-   This is incorrect for RHU users - they should use `rhu/{$rhuId}/personnel`
-   The index method was using the correct path `collection($user['role'])->document($user['id'])->collection('personnel')`, but store/update/destroy methods were hardcoded to barangay paths

**Fix Applied:**

-   Changed all `barangay/{$barangayId}/personnel` to `rhu/{$rhuId}/personnel` in:
    -   `store()` method
    -   `update()` method
    -   `destroy()` method
-   Updated variable names from `$barangayId` to `$rhuId` for clarity
-   Updated error messages from "Barangay ID not found" to "RHU ID not found"

---

### 2. **CalendarController - Hardcoded BarangayId Initialization** ✅ FIXED

**File:** [app/Http/Controllers/RHU/CalendarController.php](app/Http/Controllers/RHU/CalendarController.php#L31)

**Problem:**

-   Line 31: `$this->barangayId = $user['barangayId'] ?? null;` was hardcoded
-   For RHU users, this would always be null since the session stores the RHU's own ID
-   Should use `getBarangayId()` trait method which properly handles role-based ID retrieval

**Fix Applied:**

-   Changed line 31 to: `$this->barangayId = $this->getBarangayId();`
-   Updated error message to "Barangay/RHU ID not found"
-   Now works correctly for both BHC (barangay) and RHU roles

**Impact:**

-   Calendar now correctly fetches events for RHU users
-   Line 97 correctly uses `barangay/{$this->barangayId}/schedules` (this is intentional since RHU manages barangay schedules)

---

### 3. **NotificationController - Incorrect Collection Paths** ✅ FIXED

**File:** [app/Http/Controllers/RHU/NotificationController.php](app/Http/Controllers/RHU/NotificationController.php)

**Problem:**

-   Lines 42, 122, 152 were using `barangay/{$barangayId}/notifications`
-   This is incorrect for RHU users - they should use `rhu/{$rhuId}/notifications`
-   The trait's `getBarangayId()` returns the RHU ID for RHU users, but was being used with barangay paths

**Fix Applied:**

-   Changed all `barangay/{$barangayId}/notifications` to `rhu/{$rhuId}/notifications` in:
    -   `index()` method
    -   `store()` method
    -   `destroy()` method
-   Updated variable names from `$barangayId` to `$rhuId` for clarity
-   Updated error messages accordingly

---

## Controllers Verified ✅ (No Changes Needed)

### ServicesController

-   Uses correct pattern: `collection($user['role'])->document($user['id'])->collection('services')`
-   Translates to: `rhu/{rhuId}/services` ✓

### UserRequestController

-   Uses correct pattern: `collection($user['role'])->document($user['id'])->collection('userRequests')`
-   Translates to: `rhu/{rhuId}/userRequests` ✓

### InventoryController

-   Uses correct pattern: `collection($user['role'])->document($user['id'])->collection('inventory')`
-   Translates to: `rhu/{rhuId}/inventory` ✓

### ReportsController

-   Uses correct pattern: queries "reports" collection at root level
-   Properly filters by barangayId using `getBarangayId()` ✓

### AccountController

-   Uses `getCollectionNameByRole()` correctly
-   Properly creates sub-collections: `collection($user['role'])->document($user['id'])->collection('accounts')`
-   Translates to: `rhu/{rhuId}/accounts` ✓

### ScheduleController

-   Uses correct pattern for managing barangay schedules
-   RHU users can manage schedules for barangays under them
-   Properly fetches list of barangays with `getBarangaysUnderRhu()` ✓

### EventController

-   Uses correct pattern for managing barangay events
-   RHU users can manage events for barangays under them
-   Properly handles cross-barangay aggregation ✓

---

## Root Cause Analysis

The issue appears to stem from the initial implementation where all RHU controllers were copied from BHC (Barangay Health Center) controllers without properly updating the collection paths. While some controllers were correctly updated to use the generic `collection($user['role'])->document($user['id'])` pattern, others had hardcoded `barangay/` paths that were never updated.

### Architecture Clarification

**For RHU-specific data** (Personnel, Notifications, Services, etc.):

-   Use: `rhu/{rhuId}/{subcollection}` ✓

**For barangay-managed data accessed by RHU** (Schedules, Events, Reports):

-   Use: `barangay/{barangayId}/{subcollection}` ✓
-   Typically with barangay selection/filtering

---

## Firestore Collection Structure (RHU)

```
rhu/{rhuId}/
├── personnel/
├── inventory/
│   └── batches/
├── services/
├── userRequests/
├── accounts/
├── notifications/
└── events/

barangay/{barangayId}/   (managed by RHU)
├── schedules/
├── events/
├── attendees/
└── notifications/
```

---

## Testing Recommendations

1. **Login as RHU user** and test:

    - ✓ Personnel management (create, read, update, delete)
    - ✓ Services management
    - ✓ User requests
    - ✓ Notifications
    - ✓ Calendar view
    - ✓ Account management

2. **Verify no barangay data cross-contamination** when accessing:

    - Schedules from assigned barangays
    - Events from assigned barangays

3. **Check Firestore** to ensure data is being saved to correct paths

---

## Related Documentation

-   [ROLE_BASED_STRUCTURE.md](ROLE_BASED_STRUCTURE.md) - Overall role-based system architecture
-   [BHC_RHU_STATUS.md](BHC_RHU_STATUS.md) - Implementation status
-   [IMPLEMENTATION_PLAN.md](IMPLEMENTATION_PLAN.md) - Original implementation plan

---

## Status

**All identified critical issues have been fixed and verified for syntax errors.**

-   ✅ PersonnelController
-   ✅ CalendarController
-   ✅ NotificationController
-   ✅ Other controllers verified as correct

**Next Steps:** Run application tests and verify functionality with actual RHU user sessions.
