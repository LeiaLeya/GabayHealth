<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\FirebaseService;
use App\Mail\BarangayAccountSetupEmail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class BarangayAccountSetupController extends Controller
{
    /**
     * Show the account setup form
     */
    public function showSetupForm($token)
    {
        // Find the token in database
        $setupToken = DB::table('barangay_setup_tokens')
            ->where('token', $token)
            ->first();

        // Verify token exists, hasn't expired, and hasn't been used
        if (!$setupToken) {
            return redirect()->route('login')->withErrors(['error' => 'Invalid or expired setup link.']);
        }

        if ($setupToken->used_at) {
            return redirect()->route('login')->withErrors(['error' => 'This setup link has already been used.']);
        }

        if (Carbon::now()->isAfter($setupToken->expires_at)) {
            return redirect()->route('login')->withErrors(['error' => 'This setup link has expired. Please contact your Rural Health Unit administrator.']);
        }

        return view('auth.barangay-setup', [
            'token' => $token,
            'email' => $setupToken->email,
        ]);
    }

    /**
     * Handle password setup and account activation
     */
    public function handleSetup(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'password' => 'required|min:8|confirmed',
        ]);

        // Find and verify the token
        $setupToken = DB::table('barangay_setup_tokens')
            ->where('token', $request->token)
            ->first();

        if (!$setupToken) {
            return back()->withErrors(['error' => 'Invalid setup link.']);
        }

        if ($setupToken->used_at) {
            return back()->withErrors(['error' => 'This setup link has already been used.']);
        }

        if (Carbon::now()->isAfter($setupToken->expires_at)) {
            return back()->withErrors(['error' => 'This setup link has expired.']);
        }

        try {
            $firebaseService = app(FirebaseService::class);
            $firestore = $firebaseService->getFirestore();
            $auth = $firebaseService->getAuth();

            // Get barangay document to get username
            $barangayDoc = $firestore->collection('barangay')->document($setupToken->barangay_id)->snapshot();

            if (!$barangayDoc->exists()) {
                \Log::error('Barangay document not found during setup', ['barangay_id' => $setupToken->barangay_id]);
                return back()->withErrors(['error' => 'Barangay account not found.']);
            }

            $barangayData = $barangayDoc->data();
            $username = $barangayData['username'] ?? '';

            if (!$username) {
                \Log::error('Barangay username not found', ['barangay_id' => $setupToken->barangay_id]);
                return back()->withErrors(['error' => 'Barangay account is incomplete.']);
            }

            // Update password in Firebase Auth
            $auth->updateUser($barangayData['uid'], [
                'password' => $request->password,
            ]);

            // Update barangay status to active in Firestore
            $firestore->collection('barangay')->document($setupToken->barangay_id)->update([
                ['path' => 'status', 'value' => 'active'],
                ['path' => 'password_setup_at', 'value' => now()->toDateTimeString()],
            ]);

            // Mark token as used
            DB::table('barangay_setup_tokens')
                ->where('id', $setupToken->id)
                ->update(['used_at' => now()]);

            \Log::info('Barangay account activated', [
                'barangay_id' => $setupToken->barangay_id,
                'username' => $username,
            ]);

            return redirect()->route('login')->with('success', 'Your account is now active! Please login with your username and new password.');

        } catch (\Kreait\Firebase\Exception\Auth\UserNotFound $e) {
            \Log::error('Firebase user not found during setup', ['barangay_id' => $setupToken->barangay_id, 'error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Account setup failed. Please contact your Rural Health Unit administrator.']);
        } catch (\Exception $e) {
            \Log::error('Error during barangay account setup', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'An error occurred. Please try again.']);
        }
    }

    /**
     * Send account setup email (called by RHU admin)
     */
    public static function sendSetupEmail($barangayId, $barangayEmail, $barangayName, $username)
    {
        try {
            // Generate unique token
            $token = Str::random(60);

            // Store token in database (expires in 24 hours)
            DB::table('barangay_setup_tokens')->insert([
                'barangay_id' => $barangayId,
                'email' => $barangayEmail,
                'token' => $token,
                'expires_at' => now()->addDay(),
                'created_at' => now(),
            ]);

            // Generate setup URL
            $setupUrl = route('barangay.setup-password', ['token' => $token]);

            // Send email
            Mail::to($barangayEmail)->send(new BarangayAccountSetupEmail(
                $barangayName,
                $username,
                $setupUrl,
                now()->addDay()
            ));

            \Log::info('Barangay setup email sent', [
                'barangay_id' => $barangayId,
                'email' => $barangayEmail,
                'username' => $username,
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Error sending barangay setup email', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
