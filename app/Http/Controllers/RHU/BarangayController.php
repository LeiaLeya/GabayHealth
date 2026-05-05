<?php

namespace App\Http\Controllers\RHU;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasRoleContext;
use Illuminate\Http\Request;
use App\Services\FirebaseService;
use App\Mail\BarangayArchivedEmail;
use App\Mail\BarangayRestoredEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class BarangayController extends Controller
{
    use HasRoleContext;

    protected $firestore;
    protected $auth;

    public function __construct(FirebaseService $firebase)
    {
        $this->firestore = $firebase->getFirestore();
        $this->auth = $firebase->getAuth();
    }

    public function index()
    {
        $user = session('user');

        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to view barangays.');
        }

        $rhuId = $this->getBarangayId();

        if (!$rhuId) {
            return redirect()->back()->with('error', 'RHU ID not found. Please contact administrator.');
        }

        $barangays = [];

        try {
            $barangayDocs = $this->firestore
                ->collection('barangay')
                ->where('rhuId', '=', $rhuId)
                ->documents();

            foreach ($barangayDocs as $doc) {
                if ($doc->exists()) {
                    $data = $doc->data();

                    $barangayName = 'Unknown Barangay';
                    $location = 'Unknown Location';
                    if (!empty($data['location']['name'])) {
                        $locationParts = explode(',', $data['location']['name']);
                        $barangayName = trim($locationParts[0]);
                        $location = trim($data['location']['name']);
                    }

                    $appliedDate = $data['createdAt'] ?? $data['created_at'] ?? $data['approved_at'] ?? null;
                    if ($appliedDate && is_string($appliedDate)) {
                        try {
                            $appliedDate = \Carbon\Carbon::parse($appliedDate)->format('M d, Y');
                        } catch (\Exception $e) {
                            $appliedDate = 'N/A';
                        }
                    } else {
                        $appliedDate = 'N/A';
                    }

                    $barangays[] = [
                        'id' => $doc->id(),
                        'barangayName' => $barangayName,
                        'healthCenterName' => $data['healthCenterName'] ?? 'Health Center',
                        'email' => $data['email'] ?? 'N/A',
                        'logo_url' => $data['logo_url'] ?? null,
                        'status' => $data['status'] ?? 'unknown',
                        'address' => $data['address'] ?? $location,
                        'location' => $location,
                        'appliedDate' => $appliedDate,
                    ];
                }
            }

            usort($barangays, function ($a, $b) {
                return strcmp($a['healthCenterName'], $b['healthCenterName']);
            });
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load barangays.');
        }

        return view('rhu.barangays.index', compact('barangays'));
    }

    public function show($barangayId)
    {
        $user = session('user');

        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to view barangay details.');
        }

        $rhuId = $this->getBarangayId();
        if (!$rhuId) {
            return redirect()->back()->with('error', 'RHU ID not found. Please contact administrator.');
        }

        try {
            $barangayDoc = $this->firestore
                ->collection('barangay')
                ->document($barangayId)
                ->snapshot();

            if (!$barangayDoc->exists()) {
                return redirect()->route('rhu.barangays.index')->with('error', 'Barangay not found.');
            }

            $barangayData = $barangayDoc->data();

            if (($barangayData['rhuId'] ?? '') !== $rhuId) {
                return redirect()->route('rhu.barangays.index')->with('error', 'You do not have permission to view this barangay.');
            }

            $barangayName = 'Unknown Barangay';
            if (!empty($barangayData['location']) && is_array($barangayData['location']) && !empty($barangayData['location']['name'])) {
                $locationParts = explode(',', $barangayData['location']['name']);
                $barangayName = trim($locationParts[0]);
            }

            $barangay = array_merge(['id' => $barangayId], $barangayData);
            $barangay['barangayName'] = $barangayName;

            if (isset($barangayData['region']) && isset($barangayData['province']) && isset($barangayData['city'])) {
                $location = $this->getLocationFromPSGC($barangayData['region'], $barangayData['province'], $barangayData['city']);
                if ($location) {
                    $barangay['displayLocation'] = $location;
                }

                $barangay['displayRegion'] = $this->getLocationNameFromCode('regions', $barangayData['region']);
                $barangay['displayProvince'] = $this->getLocationNameFromCode('provinces', $barangayData['province']);
                $barangay['displayCity'] = $this->getLocationNameFromCode('cities', $barangayData['city']);
            }

            return view('rhu.barangays.show', compact('barangay'));
        } catch (\Exception $e) {
            return redirect()->route('rhu.barangays.index')->with('error', 'Failed to load barangay details.');
        }
    }

    public function approve($barangayId)
    {
        $user = session('user');

        if (!$user) {
            return back()->with('error', 'Unauthorized.');
        }

        $rhuId = $this->getBarangayId();
        if (!$rhuId) {
            return back()->with('error', 'RHU ID not found.');
        }

        try {
            $barangayDoc = $this->firestore
                ->collection('barangay')
                ->document($barangayId)
                ->snapshot();

            if (!$barangayDoc->exists()) {
                return back()->with('error', 'Barangay not found.');
            }

            $barangayData = $barangayDoc->data();

            if (($barangayData['rhuId'] ?? '') !== $rhuId) {
                return back()->with('error', 'You do not have permission to manage this barangay.');
            }

            $this->firestore->collection('barangay')->document($barangayId)->update([
                ['path' => 'status', 'value' => 'approved'],
                ['path' => 'approved_by', 'value' => $user['id']],
                ['path' => 'approved_at', 'value' => now()->toDateTimeString()],
            ]);

            return back()->with('success', 'Barangay has been approved.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to approve barangay: ' . $e->getMessage());
        }
    }

    public function sendCredentials($barangayId)
    {
        $user = session('user');

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $rhuId = $this->getBarangayId();
        if (!$rhuId) {
            return response()->json(['error' => 'RHU ID not found'], 400);
        }

        try {
            $barangayDoc = $this->firestore
                ->collection('barangay')
                ->document($barangayId)
                ->snapshot();

            if (!$barangayDoc->exists()) {
                return response()->json(['error' => 'Barangay not found'], 404);
            }

            $barangayData = $barangayDoc->data();

            if (($barangayData['rhuId'] ?? '') !== $rhuId) {
                return response()->json(['error' => 'You do not have permission to manage this barangay'], 403);
            }

            if (isset($barangayData['status']) && $barangayData['status'] === 'active') {
                return response()->json(['error' => 'This barangay account is already active.'], 400);
            }

            $barangayEmail = $barangayData['email'] ?? null;
            $healthCenterName = $barangayData['healthCenterName'] ?? 'Health Center';

            if (!$barangayEmail) {
                return response()->json(['error' => 'Barangay email is not set.'], 400);
            }

            $username = 'BHC_' . strtoupper(substr(Str::uuid(), 0, 8));

            $uid = null;
            try {
                $authUser = $this->auth->createUser([
                    'email' => $barangayEmail,
                    'displayName' => $healthCenterName,
                    'emailVerified' => false,
                ]);
                $uid = $authUser->uid;
            } catch (\Kreait\Firebase\Exception\Auth\EmailExists $e) {
                try {
                    $existingUser = $this->auth->getUserByEmail($barangayEmail);
                    $uid = $existingUser->uid;
                } catch (\Exception $getUserException) {
                    return response()->json(['error' => 'Email already registered in system'], 400);
                }
            }

            $this->firestore->collection('barangay')->document($barangayId)->update([
                ['path' => 'username', 'value' => $username],
                ['path' => 'uid', 'value' => $uid],
                ['path' => 'status', 'value' => 'pending_setup'],
                ['path' => 'approved_by', 'value' => $user['id']],
                ['path' => 'approved_at', 'value' => now()->toDateTimeString()],
            ]);

            $setupController = new \App\Http\Controllers\Auth\BarangayAccountSetupController();
            $emailSent = $setupController::sendSetupEmail($barangayId, $barangayEmail, $healthCenterName, $username);

            if (!$emailSent) {
                throw new \Exception('Failed to send setup email');
            }

            return response()->json([
                'success' => true,
                'message' => 'Credentials sent successfully! Setup email has been sent to ' . $barangayEmail,
                'username' => $username,
                'email' => $barangayEmail,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to send credentials: ' . $e->getMessage()], 500);
        }
    }

    public function archive(Request $request, $barangayId)
    {
        $user = session('user');
        if (!$user) return back()->with('error', 'Unauthorized.');

        $rhuId = $this->getBarangayId();
        if (!$rhuId) return back()->with('error', 'RHU ID not found.');

        try {
            $doc = $this->firestore->collection('barangay')->document($barangayId)->snapshot();
            if (!$doc->exists()) return back()->with('error', 'Barangay not found.');

            $data = $doc->data();
            if (($data['rhuId'] ?? '') !== $rhuId) return back()->with('error', 'Permission denied.');

            $healthCenterName = $data['healthCenterName'] ?? '';
            $confirmed = strtolower(trim($request->input('confirm_name', '')));
            if ($confirmed !== strtolower(trim($healthCenterName))) {
                return back()->with('error', 'Name does not match. Archive cancelled.');
            }

            $this->firestore->collection('barangay')->document($barangayId)->update([
                ['path' => 'status',      'value' => 'archived'],
                ['path' => 'archived_at', 'value' => now()->toDateTimeString()],
                ['path' => 'archived_by', 'value' => $user['id']],
            ]);

            $barangayEmail = $data['email'] ?? null;
            if ($barangayEmail) {
                $rhuDoc = $this->firestore->collection('rhu')->document($rhuId)->snapshot();
                $rhuName = $rhuDoc->exists() ? ($rhuDoc->data()['healthCenterName'] ?? 'Your RHU') : 'Your RHU';
                $rhuEmail = $rhuDoc->exists() ? ($rhuDoc->data()['email'] ?? config('mail.from.address')) : config('mail.from.address');

                try {
                    Mail::to($barangayEmail)->send(new BarangayArchivedEmail(
                        $healthCenterName,
                        $rhuName,
                        now()->format('F d, Y g:i A'),
                        $rhuEmail,
                    ));
                } catch (\Exception $e) {
                    // email failed silently — archive still succeeded
                }
            }

            return redirect()->route('rhu.barangays.show', $barangayId)
                ->with('success', $healthCenterName . ' has been archived. A notification email was sent to the barangay.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to archive barangay: ' . $e->getMessage());
        }
    }

    public function restore(Request $request, $barangayId)
    {
        $user = session('user');
        if (!$user) return back()->with('error', 'Unauthorized.');

        $rhuId = $this->getBarangayId();
        if (!$rhuId) return back()->with('error', 'RHU ID not found.');

        try {
            $doc = $this->firestore->collection('barangay')->document($barangayId)->snapshot();
            if (!$doc->exists()) return back()->with('error', 'Barangay not found.');

            $data = $doc->data();
            if (($data['rhuId'] ?? '') !== $rhuId) return back()->with('error', 'Permission denied.');

            $this->firestore->collection('barangay')->document($barangayId)->update([
                ['path' => 'status',      'value' => 'active'],
                ['path' => 'archived_at', 'value' => null],
                ['path' => 'archived_by', 'value' => null],
            ]);

            $name          = $data['healthCenterName'] ?? 'Barangay';
            $barangayEmail = $data['email'] ?? null;
            if ($barangayEmail) {
                $rhuDoc  = $this->firestore->collection('rhu')->document($rhuId)->snapshot();
                $rhuName  = $rhuDoc->exists() ? ($rhuDoc->data()['healthCenterName'] ?? 'Your RHU') : 'Your RHU';
                $rhuEmail = $rhuDoc->exists() ? ($rhuDoc->data()['email'] ?? config('mail.from.address')) : config('mail.from.address');

                try {
                    Mail::to($barangayEmail)->send(new BarangayRestoredEmail(
                        $name,
                        $rhuName,
                        now()->format('F d, Y g:i A'),
                        $rhuEmail,
                    ));
                } catch (\Exception $e) {
                    // email failed silently — restore still succeeded
                }
            }

            return redirect()->route('rhu.barangays.show', $barangayId)
                ->with('success', $name . ' has been restored and is now active. A notification email was sent to the barangay.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to restore barangay: ' . $e->getMessage());
        }
    }

    private function getLocationNameFromCode($type, $code)
    {
        if (!$code) {
            return null;
        }

        try {
            $url = "https://psgc.gitlab.io/api/{$type}/{$code}.json";
            $response = Http::get($url);

            if ($response->successful()) {
                $data = $response->json();
                return $data['name'] ?? null;
            }
        } catch (\Exception $e) {
            // PSGC lookup failed
        }

        return null;
    }

    private function getLocationFromPSGC($regionCode, $provinceCode, $cityCode)
    {
        try {
            $locationParts = [];

            $cityResponse = Http::get("https://psgc.gitlab.io/api/cities/{$cityCode}.json");
            if ($cityResponse->successful()) {
                $cityData = $cityResponse->json();
                $locationParts[] = $cityData['name'] ?? '';
            }

            if ($provinceCode) {
                try {
                    $provinceResponse = Http::get("https://psgc.gitlab.io/api/provinces/{$provinceCode}.json");
                    if ($provinceResponse->successful()) {
                        $provinceData = $provinceResponse->json();
                        $locationParts[] = $provinceData['name'] ?? '';
                    }
                } catch (\Exception $e) {
                    // continue without province
                }
            }

            if ($regionCode) {
                try {
                    $regionResponse = Http::get("https://psgc.gitlab.io/api/regions/{$regionCode}.json");
                    if ($regionResponse->successful()) {
                        $regionData = $regionResponse->json();
                        $locationParts[] = $regionData['name'] ?? '';
                    }
                } catch (\Exception $e) {
                    // continue without region
                }
            }

            return implode(', ', array_filter($locationParts)) ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
