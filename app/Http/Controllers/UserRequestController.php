<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirebaseService;

class UserRequestController extends Controller
{
    protected $firestore;

    public function __construct(FirebaseService $firebase)
    {
        $this->firestore = $firebase->getFirestore();
    }

    public function index()
    {
        // Set timeout to prevent execution timeout
        set_time_limit(60);
        
        $user = session('user');
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to access user request management.');
        }
        
        // Initialize requests as empty array (view expects $requests)
        $requests = [];
        
        try {
            \Log::info('UserRequestController - Fetching user requests for user: ' . $user['id'] . ' with role: ' . $user['role']);
            
            // Get user requests from user's sub-collection
            $userRequestsQuery = $this->firestore
                ->collection($user['role'])
                ->document($user['id'])
                ->collection('userRequests')
                ->limit(50) // Limit results to prevent timeout
                ->documents();

            $count = 0;
            foreach ($userRequestsQuery as $doc) {
                if ($doc->exists()) {
                    $requests[] = array_merge($doc->data(), ['id' => $doc->id()]);
                    $count++;
                }
            }
            
            \Log::info('UserRequestController - Found ' . $count . ' user requests');

            return view('pages.user-requests.index', compact('requests'));
        } catch (\Exception $e) {
            \Log::error('Error fetching user requests: ' . $e->getMessage());
            return view('pages.user-requests.index', compact('requests'))->with('error', 'Error loading user requests data. Please try again.');
        }
    }

    // Approve a user request
    public function approve($id)
    {
        try {
            $this->firestore
                ->collection("barangay/{$this->barangayId}/userRequests")
                ->document($id)
                ->update([
                    ['path' => 'status', 'value' => 'approved'],
                    ['path' => 'approvedAt', 'value' => now()->toDateTimeString()],
                    ['path' => 'approvedBy', 'value' => session('user.id')]
                ]);

            return redirect()->back()->with('success', 'User request approved successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to approve request: ' . $e->getMessage());
        }
    }

    // Decline a user request
    public function decline($id)
    {
        try {
            $this->firestore
                ->collection("barangay/{$this->barangayId}/userRequests")
                ->document($id)
                ->update([
                    ['path' => 'status', 'value' => 'declined'],
                    ['path' => 'declinedAt', 'value' => now()->toDateTimeString()],
                    ['path' => 'declinedBy', 'value' => session('user.id')]
                ]);

            return redirect()->back()->with('success', 'User request declined successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to decline request: ' . $e->getMessage());
        }
    }

    // Show request details
    public function show($id)
    {
        try {
            $requestDoc = $this->firestore
                ->collection("barangay/{$this->barangayId}/userRequests")
                ->document($id)
                ->snapshot();

            if (!$requestDoc->exists()) {
                return redirect()->route('user-requests.index')->with('error', 'Request not found.');
            }

            $request = array_merge($requestDoc->data(), ['id' => $id]);
            return view('pages.user-requests.show', compact('request'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load request details: ' . $e->getMessage());
        }
    }
} 