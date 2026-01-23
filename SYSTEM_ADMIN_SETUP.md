# System Administrator Setup & QA Guide

## Quick Start

### 1. Create System Admin Account

Run this command in your terminal to create a System Administrator account:

```bash
php artisan admin:create-system-admin --username=sysadmin --password=SecurePass123!
```

Or with interactive password prompt:

```bash
php artisan admin:create-system-admin --username=sysadmin
```

**Default Credentials (after running the command):**

- Username: `sysadmin`
- Password: `SecurePass123!` (or your chosen password)
- Role: `admin`

### 2. Access System Admin Dashboard

1. Go to `/login`
2. Enter your System Admin credentials
3. You'll be redirected to `/admin/system-admin/dashboard`

---

## System Administrator Features

### Dashboard Overview

- **Pending Applications**: Shows all RHUs waiting for approval
- **Statistics Cards**: Quick view of application counts
- **Quick Actions**: Approve, view, or manage applications

### Main Features

#### 1. View Pending Applications

- See all RHUs that have applied
- View their registration information
- Check email, location, and application date

#### 2. Approve RHU Application

- **Actions:**
    - Click "Approve" button next to pending RHU
    - Or click "View" to see full details first
    - Click "Generate & Send Credentials"
- **What Happens:**
    - ✓ Unique username generated (e.g., `RHU_A1B2C3D4`)
    - ✓ Temporary password created
    - ✓ Firebase Auth account created with email
    - ✓ Credentials stored in Firestore
    - ✓ RHU status changed to `credentials_sent`
    - ✓ Email would be sent (configure SMTP for production)

#### 3. Reject Application

- Click "View" on an application
- Scroll to "Reject Application" section
- Enter rejection reason
- Click "Reject"
- RHU cannot login; rejection reason recorded

#### 4. Resend Credentials

- Navigate to "Approved RHUs" section
- Click "Resend" button next to any approved RHU
- Credentials email will be resent (when email configured)

#### 5. View All RHUs

- Click "View All RHUs" button
- See tabbed view: All, Pending, Credentials Sent, Rejected
- Filter by status easily

---

## QA Testing Workflow

### Test Scenario 1: Complete Approval Flow

**Prerequisites:**

- System Admin account created
- At least one pending RHU application

**Steps:**

1. Login as System Admin with credentials
2. View Dashboard → Should show pending RHUs
3. Click "View" on a pending RHU
4. Verify all information is displayed correctly:
    - ✓ RHU name, email, location
    - ✓ Logo displayed
    - ✓ Status shows "pending"
5. Click "Generate & Send Credentials"
6. Verify success alert appears with:
    - Generated username
    - Email address
7. Dashboard should update (pending count decreases)
8. Check "Approved RHUs" → RHU should appear with status badge

**Expected Results:**

- ✓ RHU moved to "credentials_sent" status
- ✓ Username and credentials stored in Firestore
- ✓ Firebase Auth account created
- ✓ Dashboard statistics updated

---

### Test Scenario 2: Rejection Flow

**Steps:**

1. From Dashboard, click "View" on a pending RHU
2. Scroll to "Reject Application" section
3. Enter rejection reason: "Incomplete documentation"
4. Click "Reject"
5. Confirm action
6. Verify success message
7. Check Dashboard → pending count should decrease
8. Check "All RHUs" → application should be in "Rejected" tab

**Expected Results:**

- ✓ RHU status changed to "rejected"
- ✓ Rejection reason recorded
- ✓ Timestamp recorded
- ✓ Removed from pending list

---

### Test Scenario 3: Resend Credentials

**Prerequisites:**

- At least one approved RHU (credentials_sent status)

**Steps:**

1. Click "Approved RHUs" button
2. Find an approved RHU
3. Click "Resend" button next to it
4. Confirm action
5. Verify success message: "Credentials resent"
6. Check "credentials_resent_at" timestamp updated in Firestore

**Expected Results:**

- ✓ Success message displayed
- ✓ Timestamp updated in database
- ✓ Credentials can be re-sent to RHU

---

### Test Scenario 4: Filter by Status

**Steps:**

1. Click "View All RHUs"
2. Click different tabs: All, Pending, Approved, Rejected
3. Verify correct RHUs appear in each tab
4. Verify counts match

**Expected Results:**

- ✓ Tab filtering works correctly
- ✓ RHUs grouped by status
- ✓ Counts displayed accurately

---

## Database Structure

### Admin Collection

```json
{
    "username": "sysadmin",
    "email": "sysadmin@gabay-health-admin.local",
    "uid": "firebase-uid-here",
    "password": "bcrypt-hash",
    "name": "System Administrator",
    "role": "admin",
    "status": "active",
    "created_at": "2026-01-21 10:00:00"
}
```

### RHU Collection - Pending Status

```json
{
    "email": "rhu@example.com",
    "rhuName": "Rural Health Unit Name",
    "fullAddress": "123 Main St, City",
    "region": "Region Code",
    "province": "Province Code",
    "city": "City Code",
    "logo_url": "https://...",
    "status": "pending",
    "created_at": "2026-01-21 09:00:00",
    "username": null,
    "uid": null
}
```

### RHU Collection - After Approval

```json
{
    "email": "rhu@example.com",
    "rhuName": "Rural Health Unit Name",
    "username": "RHU_A1B2C3D4",
    "uid": "firebase-auth-uid",
    "status": "credentials_sent",
    "credentials_generated_at": "2026-01-21 10:30:00",
    "credentials_sent_at": "2026-01-21 10:30:00",
    "approved_by": "admin-uid",
    "temp_password": "Temp_Secure_123!"
}
```

---

## Troubleshooting

### Issue: "Invalid username or password" on login

**Solution:**

- Verify System Admin account was created successfully
- Check username and password match exactly
- Run command again to recreate account

### Issue: System Admin sees blank dashboard

**Solution:**

- Ensure pending RHUs exist (check Firestore)
- Verify RHUs have status = "pending"
- Check browser console for JavaScript errors

### Issue: "Unauthorized access" error

**Solution:**

- Verify user role is "admin" in session
- Check middleware: `role:admin` is applied
- Clear session and re-login

### Issue: Credentials not showing on approval

**Solution:**

- Check Firestore `admin` collection exists
- Verify Firebase Auth account was created
- Check server logs for Firebase errors

---

## Next Steps for Production

1. **Configure Email**
    - Set up SMTP in `.env`
    - Create Mailable for RHU credentials
    - Test email sending

2. **Security Enhancements**
    - Enable two-factor authentication
    - Add audit logging for all approvals
    - Implement IP whitelisting

3. **Notifications**
    - Email notifications to RHU on approval
    - Email notifications on rejection
    - Admin notifications for new applications

4. **Reports**
    - Add approval statistics/charts
    - Export RHU list to PDF/CSV
    - Track approval turnaround times

---

## File Locations

- Controller: `app/Http/Controllers/Admin/SystemAdminController.php`
- Command: `app/Console/Commands/CreateSystemAdmin.php`
- Views:
    - Dashboard: `resources/views/admin/system-admin/dashboard.blade.php`
    - View Application: `resources/views/admin/system-admin/view-application.blade.php`
    - All RHUs: `resources/views/admin/system-admin/all-rhus.blade.php`
    - Approved RHUs: `resources/views/admin/system-admin/approved-rhus.blade.php`
- Routes: `routes/web.php` (search for "SYSTEM_ADMIN")
