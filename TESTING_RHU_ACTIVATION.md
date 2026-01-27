# Testing RHU Account Activation Workflow

## Quick Start Testing

### Prerequisites

Before testing, ensure:

1. **Laravel Application Running**

    ```bash
    php artisan serve
    # Access at: http://localhost:8000
    ```

2. **Mailtrap Account Setup**
    - Account created at mailtrap.io
    - Inbox ID: 4342209
    - API Token configured in `.env`

3. **Database Migrations Run**

    ```bash
    php artisan migrate
    # Verify rhu_setup_tokens table exists
    ```

4. **Firebase Configured**
    - Firebase credentials in `.env`
    - Firebase Auth enabled
    - Firestore database ready

5. **Admin Account Created**
    - Email: admin@gabayhealth.test
    - Password: Your admin password
    - Role: admin
    - Firestore document exists

6. **Test RHU Application Ready**
    - Create a pending RHU application via application form
    - Or manually insert into Firestore
    - Status: "pending"
    - Example data:
        ```
        {
          "rhuName": "Test Rural Health Unit",
          "email": "test-rhu@example.com",
          "status": "pending",
          "phone": "555-0000",
          "address": "123 Test Street"
        }
        ```

## Step-by-Step Testing Guide

### 1. Admin Approval Flow

**Step 1.1: Log in as Admin**

```
URL: http://localhost:8000/login
Username: admin
Email: admin@gabayhealth.test
Password: [your admin password]
```

Expected result: Redirected to admin dashboard

**Step 1.2: Navigate to System Admin Dashboard**

```
URL: http://localhost:8000/admin/system-admin/dashboard
```

Expected result:

- Page displays "System Admin Dashboard"
- Shows list of pending RHU applications
- Each RHU has an "Approve" button

**Step 1.3: Locate Test RHU**

- Find the RHU with name "Test Rural Health Unit"
- Verify email shows as "test-rhu@example.com"
- Note the RHU ID (visible in data attributes or logs)

**Step 1.4: Click Approve Button**

```
Action: Click the "Approve" button for the test RHU
```

Expected result:

- Button shows loading state
- Success message appears: "RHU approved! Setup email has been sent to test-rhu@example.com"
- Generated username displayed (e.g., "RHU_A1B2C3D4")
- RHU removed from pending list OR status updated

**Step 1.5: Verify in Console**

Check browser console (F12) for network request:

```
POST /admin/system-admin/approve-rhu/{rhu-id}
Status: 200
Response:
{
  "success": true,
  "message": "RHU approved! Setup email has been sent...",
  "username": "RHU_XXXXXXXX",
  "email": "test-rhu@example.com"
}
```

### 2. Email Delivery Verification

**Step 2.1: Access Mailtrap Inbox**

```
URL: https://mailtrap.io/inboxes/4342209/emails
```

**Step 2.2: Find Approval Email**

Expected email properties:

- **From:** noreply@gabayhealth.test
- **To:** test-rhu@example.com
- **Subject:** Account Approval & Setup Invitation
- **Sent Time:** Within last 60 seconds

**Step 2.3: Verify Email Content**

Open the email and verify:

1. **Header Content:**
    - Greeting with RHU name: "Hello, Test Rural Health Unit"
    - Message: "Your application has been approved!"

2. **Account Information:**
    - Username displayed: "RHU_XXXXXXXX"
    - Clear instruction to set password

3. **Action Button:**
    - Button text: "Set Your Password"
    - Button link format: `https://localhost:8000/setup-account/[60-character-token]`

4. **Footer:**
    - Warning: "This link expires in 24 hours"
    - Support contact information

**Step 2.4: Extract Setup Token**

Copy the setup link from the email:

```
https://localhost:8000/setup-account/[TOKEN]
```

The token is the last part of the URL (60 random characters).

### 3. Account Setup Flow

**Step 3.1: Access Setup Form**

```
Action: Click the setup link from email
OR manually navigate to:
URL: http://localhost:8000/setup-account/[TOKEN]
```

Expected result:

- Page displays "Create Your Account"
- Form shows:
    - Email field (pre-filled, read-only): "test-rhu@example.com"
    - Password field (empty)
    - Confirm Password field (empty)
    - "Create Account" button

**Step 3.2: Verify Token Validation**

Test that token validation works:

A) **Valid Token:**

- Status: 200 OK
- Form displays normally

B) **Invalid Token:**

- Try: `http://localhost:8000/setup-account/invalid-token-here`
- Expected: Redirect to login page with error
- Message: "Invalid or expired setup link."

C) **Expired Token:**

- Query database: Check a token with `expires_at` in the past
- Try to access that token's setup page
- Expected: Redirect with error message

### 3.3: Submit Setup Form

**Fill in the form:**

```
Email: test-rhu@example.com (read-only, already filled)
Password: SecureTestPass123!
Confirm Password: SecureTestPass123!
```

**Click "Create Account"**

Expected result:

- Form submitted (POST /setup-account)
- Brief loading state
- Redirect to login page
- Success message: "Your account is now active! Please login with your username and new password."

**Step 3.4: Verify Form Validation**

Test password validation by trying invalid submissions:

A) **Password Too Short:**

```
Password: pass123
Confirm Password: pass123
Expected Error: "Password must be at least 8 characters."
```

B) **Password Mismatch:**

```
Password: SecureTestPass123!
Confirm Password: DifferentPass123!
Expected Error: "Password confirmation does not match."
```

C) **Missing Fields:**

```
Password: [empty]
Confirm Password: [empty]
Expected Error: Field is required
```

### 4. Login Verification

**Step 4.1: Log in with New Credentials**

```
URL: http://localhost:8000/login
Username: RHU_XXXXXXXX (from approval notification)
Password: SecureTestPass123! (what you entered in setup)
```

Expected result:

- Login successful
- Redirect to RHU dashboard
- Session shows logged-in state
- Can access RHU-only pages

**Step 4.2: Verify Account Status**

Check Firestore RHU document:

```
Collection: rhu
Document: [RHU-ID]
Fields to verify:
- status: "active"
- username: "RHU_XXXXXXXX"
- uid: [Firebase UID from auth]
- password_setup_at: [recent timestamp]
```

### 5. Database Verification

**Step 5.1: Check Setup Token Table**

Access MySQL/database directly:

```sql
SELECT * FROM rhu_setup_tokens
WHERE rhu_id = '[test-rhu-id]';
```

Expected result:

- One record for the test RHU
- `used_at` is NOT NULL (marked as used)
- `expires_at` is > NOW() (token is valid duration)

**Step 5.2: Check Firebase Auth**

In Firebase Console:

1. Go to Authentication → Users
2. Search for email: test-rhu@example.com
3. Verify:
    - User created
    - Email verified: false (initially)
    - Custom claims: empty or has role info
    - Last sign in: recent timestamp

**Step 5.3: Check Firestore Document**

In Firebase Console:

1. Go to Firestore Database → Collections → rhu
2. Find document by email or ID
3. Verify fields:
    ```
    {
      "rhuName": "Test Rural Health Unit",
      "email": "test-rhu@example.com",
      "status": "active",
      "username": "RHU_XXXXXXXX",
      "uid": "[firebase-uid]",
      "approved_by": "[admin-user-id]",
      "approved_at": "[timestamp]",
      "password_setup_at": "[timestamp]"
    }
    ```

### 6. Edge Cases & Error Testing

#### Test 6.1: Reuse Setup Token

**Attempt:**

```
Access setup form again with same token
URL: http://localhost:8000/setup-account/[USED-TOKEN]
```

**Expected:**

- Redirect to login
- Error: "This setup link has already been used."
- Should NOT display form

#### Test 6.2: Token Expiration

**Simulate:**

Create a test token with past expiration:

```php
// In tinker or test script
DB::table('rhu_setup_tokens')->insert([
    'rhu_id' => 'test-id',
    'email' => 'test@example.com',
    'token' => 'expired-token-test',
    'expires_at' => Carbon::now()->subHours(2),
    'created_at' => Carbon::now(),
]);
```

**Attempt:**

```
Access setup form
URL: http://localhost:8000/setup-account/expired-token-test
```

**Expected:**

- Redirect to login
- Error: "This setup link has expired. Please contact the administrator."

#### Test 6.3: Admin Re-approval

**Scenario:** Admin approves same RHU twice

**Expected:**

- New token generated
- New setup email sent
- Old token still marked as used
- RHU can use new link to reset password

#### Test 6.4: Firebase Auth Creation Failure

**Simulate:** Email already exists in Firebase

**Attempt:**

1. Create first RHU and approve (succeeds)
2. Try to create another RHU with same email
3. Approve second RHU

**Expected:**

- Error response: "Email already registered in system"
- Status code: 422
- Firestore not updated
- No setup email sent

### 7. Automated Testing

#### Test 7.1: Run Unit Tests

```bash
php artisan test tests/Feature/RhuActivationTest.php
```

Expected: All tests pass

#### Test 7.2: Create Feature Test

Create file: `tests/Feature/RhuActivationTest.php`

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RhuActivationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_access_setup_form_with_valid_token()
    {
        // Create setup token
        $token = 'valid-test-token-1234567890';
        DB::table('rhu_setup_tokens')->insert([
            'rhu_id' => 'test-rhu-1',
            'email' => 'test@example.com',
            'token' => $token,
            'expires_at' => Carbon::now()->addDay(),
            'created_at' => Carbon::now(),
        ]);

        // Access setup form
        $response = $this->get('/setup-account/' . $token);

        // Assertions
        $response->assertStatus(200);
        $response->assertViewIs('auth.rhu-setup');
        $response->assertViewHas('token', $token);
        $response->assertSee('test@example.com');
    }

    /** @test */
    public function cannot_access_setup_form_with_expired_token()
    {
        // Create expired token
        $token = 'expired-token-1234567890';
        DB::table('rhu_setup_tokens')->insert([
            'rhu_id' => 'test-rhu-2',
            'email' => 'test@example.com',
            'token' => $token,
            'expires_at' => Carbon::now()->subHours(1),
            'created_at' => Carbon::now(),
        ]);

        // Try to access setup form
        $response = $this->get('/setup-account/' . $token);

        // Assertions
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('error');
    }

    /** @test */
    public function can_submit_setup_form_with_valid_password()
    {
        // Setup token
        $token = 'valid-password-token';
        $rhuId = 'test-rhu-3';
        $email = 'setup@example.com';

        DB::table('rhu_setup_tokens')->insert([
            'rhu_id' => $rhuId,
            'email' => $email,
            'token' => $token,
            'expires_at' => Carbon::now()->addDay(),
            'created_at' => Carbon::now(),
        ]);

        // Submit setup form
        $response = $this->post('/setup-account', [
            'token' => $token,
            'password' => 'ValidPassword123!',
            'password_confirmation' => 'ValidPassword123!',
        ]);

        // Assertions
        $response->assertRedirect('/login');
        $response->assertSessionHas('success');

        // Verify token marked as used
        $tokenRecord = DB::table('rhu_setup_tokens')
            ->where('token', $token)
            ->first();

        $this->assertNotNull($tokenRecord->used_at);
    }

    /** @test */
    public function cannot_submit_setup_form_with_short_password()
    {
        $token = 'short-password-token';
        DB::table('rhu_setup_tokens')->insert([
            'rhu_id' => 'test-rhu-4',
            'email' => 'short@example.com',
            'token' => $token,
            'expires_at' => Carbon::now()->addDay(),
            'created_at' => Carbon::now(),
        ]);

        $response = $this->post('/setup-account', [
            'token' => $token,
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors('password');
    }
}
```

**Run the test:**

```bash
php artisan test tests/Feature/RhuActivationTest.php --verbose
```

## Debugging

### Check Laravel Logs

```bash
tail -f storage/logs/laravel.log
```

Look for entries:

- "Setup email sent"
- "RHU account activated"
- "Error sending setup email"

### Check Database Logs

Enable query logging in `.env`:

```
LOG_QUERIES=true
```

Verify `rhu_setup_tokens` operations:

```bash
grep "rhu_setup_tokens" storage/logs/laravel.log
```

### Check Mailtrap Logs

1. Go to Mailtrap inbox: https://mailtrap.io/inboxes/4342209
2. View email details for debugging
3. Check "Failed" tab if emails not arriving

### PHP Artisan Tinker

Debug in interactive shell:

```php
php artisan tinker

// Check database
>>> DB::table('rhu_setup_tokens')->first();
>>> DB::table('rhu_setup_tokens')->where('email', 'test@example.com')->first();

// Check Firestore
>>> $firestore = app('firebase.firestore');
>>> $doc = $firestore->collection('rhu')->document('test-rhu-id')->snapshot();
>>> $doc->data();

// Check Firebase Auth
>>> $auth = app('firebase.auth');
>>> $user = $auth->getUserByEmail('test@example.com');
>>> $user->email;
```

## Common Issues & Solutions

### Issue 1: Email Not Being Sent

**Symptoms:** Mailtrap inbox is empty after approval

**Solution:**

1. Check `.env` for Mailtrap configuration

    ```bash
    grep MAILTRAP .env
    ```

2. Check Laravel logs for errors

    ```bash
    grep -i "mail\|error\|exception" storage/logs/laravel.log | tail -50
    ```

3. Verify Mailable class exists

    ```bash
    ls -la app/Mail/RhuAccountSetupEmail.php
    ```

4. Test email sending in Tinker
    ```php
    php artisan tinker
    >>> Mail::to('test@example.com')->send(new App\Mail\RhuAccountSetupEmail('Test', 'RHU_TEST', url('/'), now()->addDay()));
    ```

### Issue 2: Token Not Found in Database

**Symptoms:** "Invalid or expired setup link" immediately after approval

**Solution:**

1. Verify table exists

    ```bash
    php artisan migrate:status | grep rhu_setup_tokens
    ```

2. Check table contents

    ```bash
    php artisan tinker
    >>> DB::table('rhu_setup_tokens')->get();
    ```

3. Re-run migration if needed
    ```bash
    php artisan migrate:refresh --path=database/migrations/2025_01_27_130000_create_rhu_setup_tokens_table.php
    ```

### Issue 3: Firebase Auth Creation Fails

**Symptoms:** Error "Email already registered in system"

**Solution:**

1. Check Firebase Console for duplicate accounts
2. Delete test user from Firebase
3. Try approval again

### Issue 4: Password Setup Form Won't Submit

**Symptoms:** Form submits but redirects back with no error

**Solution:**

1. Check browser console for JavaScript errors (F12)
2. Verify form has hidden token field
3. Check Laravel logs for validation errors
4. Verify CSRF token is present and valid

## Performance Testing

Test system under load:

```bash
# Generate 100 pending RHUs
php artisan tinker

>>> for ($i = 0; $i < 100; $i++) {
    DB::table('rhu')->insert([...]);
}

# Load test approval endpoint
ab -n 100 -c 10 http://localhost:8000/admin/system-admin/dashboard
```

Expected:

- Setup emails generated within reasonable time
- Database inserts successful
- No duplicate tokens

## Checklist for Complete Testing

- [ ] Admin can approve pending RHU
- [ ] Email received in Mailtrap within 60 seconds
- [ ] Email contains valid setup link
- [ ] Setup link is accessible and displays form
- [ ] Form accepts valid password and submits
- [ ] Form rejects invalid passwords
- [ ] Account activates after successful setup
- [ ] RHU can log in with new credentials
- [ ] Token marked as used in database
- [ ] RHU status changed to "active" in Firestore
- [ ] Expired token rejected
- [ ] Already used token rejected
- [ ] Email address pre-filled on setup form
- [ ] Email address is read-only
- [ ] Logout and re-login works
- [ ] Admin cannot see setup tokens in dashboard
- [ ] Multiple RHUs can be approved sequentially
- [ ] Database has no orphaned tokens after tests

## Next Steps

After successful testing:

1. **Add to Admin Dashboard UI**
    - Add "Approve" button to pending RHUs
    - Display success/error messages
    - Update RHU status in real-time

2. **Monitor Activation**
    - Dashboard showing pending setup RHUs
    - Time elapsed since approval
    - Option to resend email

3. **Audit Trail**
    - Log all approvals
    - Log all password setups
    - Track failed attempts

4. **Production Deployment**
    - Switch from Mailtrap to real email service
    - Update email templates with branding
    - Test full workflow in staging
    - Monitor email delivery in production
