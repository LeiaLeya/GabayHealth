<?php

namespace App\Http\Controllers\RHU;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasRoleContext;
use Illuminate\Http\Request;
use App\Services\FirebaseService;

class UserRequestController extends Controller
{
    use HasRoleContext;

    protected $firestore;

    public function __construct(FirebaseService $firebase)
    {
        $this->firestore = $firebase->getFirestore();
    }

    public function index()
    {
        set_time_limit(60);
        
        $user = session('user');
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to access user request management.');
        }
        
        $requests = [];
        
        try {
            \Log::info('RHU UserRequestController - Fetching user requests for user: ' . $user['id'] . ' with role: ' . $user['role']);
            
            $userRequestsQuery = $this->firestore
                ->collection($user['role'])
                ->document($user['id'])
                ->collection('userRequests')
                ->limit(50)
                ->documents();

            $count = 0;
            foreach ($userRequestsQuery as $doc) {
                if ($doc->exists()) {
                    $requests[] = array_merge($doc->data(), ['id' => $doc->id()]);
                    $count++;
                }
            }
            
            \Log::info('RHU UserRequestController - Found ' . $count . ' user requests');

            return $this->view('user-requests.index', compact('requests'));
        } catch (\Exception $e) {
            \Log::error('Error fetching user requests: ' . $e->getMessage());
            return $this->view('user-requests.index', compact('requests'))->with('error', 'Error loading user requests data. Please try again.');
        }
    }

    public function approve($id)
    {
        $user = session('user');
        
        if (!$user) {
            return redirect()->back()->with('error', 'Please login to approve requests.');
        }
        
        $barangayId = $this->getBarangayId();
        
        if (!$barangayId) {
            return redirect()->back()->with('error', 'Barangay ID not found.');
        }
        
        try {
            $this->firestore
                ->collection($user['role'])
                ->document($user['id'])
                ->collection('userRequests')
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

    public function decline($id)
    {
        $user = session('user');
        
        if (!$user) {
            return redirect()->back()->with('error', 'Please login to decline requests.');
        }
        
        $barangayId = $this->getBarangayId();
        
        if (!$barangayId) {
            return redirect()->back()->with('error', 'Barangay ID not found.');
        }
        
        try {
            $this->firestore
                ->collection($user['role'])
                ->document($user['id'])
                ->collection('userRequests')
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

    public function show($id)
    {
        $user = session('user');
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to view request details.');
        }
        
        try {
            $requestDoc = $this->firestore
                ->collection($user['role'])
                ->document($user['id'])
                ->collection('userRequests')
                ->document($id)
                ->snapshot();

            if (!$requestDoc->exists()) {
                return redirect()->route('rhu.user-requests.index')->with('error', 'Request not found.');
            }

            $request = array_merge($requestDoc->data(), ['id' => $id]);
            return $this->view('user-requests.show', compact('request'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load request details: ' . $e->getMessage());
        }
    }
}


