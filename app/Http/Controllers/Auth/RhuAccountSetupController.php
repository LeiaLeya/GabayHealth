<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\FirebaseService;
use App\Mail\RhuAccountSetupEmail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class RhuAccountSetupController extends Controller
{
    /**
     * Show the account setup form
     */
    public function showSetupForm($token)
    {
        // Find the token in database
        $setupToken = DB::table('rhu_setup_tokens')
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
            return redirect()->route('login')->withErrors(['error' => 'This setup link has expired. Please contact the administrator.']);
        }

        return view('auth.rhu-setup', [
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
        $setupToken = DB::table('rhu_setup_tokens')
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

            // Get RHU document to get username
            $rhuDoc = $firestore->collection('rhu')->document($setupToken->rhu_id)->snapshot();

            if (!$rhuDoc->exists()) {
                \Log::error('RHU document not found during setup', ['rhu_id' => $setupToken->rhu_id]);
                return back()->withErrors(['error' => 'RHU account not found.']);
            }

            $rhuData = $rhuDoc->data();
            $username = $rhuData['username'] ?? '';

            if (!$username) {
                \Log::error('RHU username not found', ['rhu_id' => $setupToken->rhu_id]);
                return back()->withErrors(['error' => 'RHU account is incomplete.']);
            }

            // Update password in Firebase Auth
            $auth->updateUser($rhuData['uid'], [
                'password' => $request->password,
            ]);

            // Update Firestore with bcrypt hash (used by LoginController::password_verify),
            // status, and setup timestamp
            $firestore->collection('rhu')->document($setupToken->rhu_id)->update([
                ['path' => 'password', 'value' => bcrypt($request->password)],
                ['path' => 'status', 'value' => 'approved'],
                ['path' => 'password_setup_at', 'value' => now()->toDateTimeString()],
            ]);

            // Mark token as used
            DB::table('rhu_setup_tokens')
                ->where('id', $setupToken->id)
                ->update(['used_at' => now()]);

            \Log::info('RHU account activated', [
                'rhu_id' => $setupToken->rhu_id,
                'username' => $username,
            ]);

            return redirect()->route('login')->with('success', 'Your account is now active! Please login with your username and new password.');

        } catch (\Kreait\Firebase\Exception\Auth\UserNotFound $e) {
            \Log::error('Firebase user not found during setup', ['rhu_id' => $setupToken->rhu_id, 'error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Account setup failed. Please contact the administrator.']);
        } catch (\Exception $e) {
            \Log::error('Error during RHU account setup', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'An error occurred. Please try again.']);
        }
    }

    /**
     * Send account setup email (called by admin)
     */
    public static function sendSetupEmail($rhuId, $rhuEmail, $rhuName, $username)
    {
        try {
            // Generate unique token
            $token = Str::random(60);

            // Store token in database (expires in 24 hours)
            DB::table('rhu_setup_tokens')->insert([
                'rhu_id' => $rhuId,
                'email' => $rhuEmail,
                'token' => $token,
                'expires_at' => now()->addDay(),
                'created_at' => now(),
            ]);

            // Generate setup URL
            $setupUrl = route('rhu.setup-password', ['token' => $token]);

            // Send email
            Mail::to($rhuEmail)->send(new RhuAccountSetupEmail(
                $rhuName,
                $username,
                $setupUrl,
                now()->addDay()
            ));

            \Log::info('Setup email sent', [
                'rhu_id' => $rhuId,
                'email' => $rhuEmail,
                'username' => $username,
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Error sending setup email', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
