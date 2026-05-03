<?php

namespace App\Http\Controllers\BHC;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasRoleContext;
use App\Http\Controllers\Traits\PreparesUserRequestRows;
use Illuminate\Http\Request;
use App\Services\FirebaseService;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRequestController extends Controller
{
    use HasRoleContext;
    use PreparesUserRequestRows;

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
        $totalRequests = 0;
        $pendingCount = 0;
        $approvedCount = 0;
        $declinedCount = 0;
        $search = trim((string) request('q', ''));
        
        try {
            \Log::info('BHC UserRequestController - Fetching user requests for user: ' . $user['id'] . ' with role: ' . $user['role']);
            
            $userRequestsQuery = $this->firestore
                ->collection($user['role'])
                ->document($user['id'])
                ->collection('userRequests')
                ->limit(50)
                ->documents();

            $count = 0;
            foreach ($userRequestsQuery as $doc) {
                if ($doc->exists()) {
                    $requests[] = $this->prepareUserRequestRow($doc->data(), $doc->id());
                    $count++;
                }
            }
            
            \Log::info('BHC UserRequestController - Found ' . $count . ' user requests');

            $totalRequests = count($requests);
            $pendingCount = count(array_filter($requests, fn($r) => ($r['status'] ?? '') === 'pending'));
            $approvedCount = count(array_filter($requests, fn($r) => ($r['status'] ?? '') === 'approved'));
            $declinedCount = count(array_filter($requests, fn($r) => ($r['status'] ?? '') === 'declined'));

            if ($search !== '') {
                $requests = array_values(array_filter(
                    $requests,
                    fn ($r) => $this->userRequestMatchesSearch($r, $search)
                ));
            }

            $perPage = 7;
            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $requestsCollection = collect($requests)
                ->sortByDesc(fn ($r) => $this->userRequestCreatedSortTimestamp($r))
                ->values();
            $requests = new LengthAwarePaginator(
                $requestsCollection->forPage($currentPage, $perPage)->values(),
                $requestsCollection->count(),
                $perPage,
                $currentPage,
                [
                    'path' => request()->url(),
                    'query' => request()->query(),
                ]
            );

            return $this->view('user-requests.index', compact(
                'requests',
                'totalRequests',
                'pendingCount',
                'approvedCount',
                'declinedCount',
                'search'
            ));
        } catch (\Exception $e) {
            \Log::error('Error fetching user requests: ' . $e->getMessage());
            return $this->view('user-requests.index', compact(
                'requests',
                'totalRequests',
                'pendingCount',
                'approvedCount',
                'declinedCount',
                'search'
            ))->with('error', 'Error loading user requests data. Please try again.');
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

    public function show(Request $httpRequest, $id)
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
                if ($httpRequest->wantsJson()) {
                    return response()->json(['message' => 'Request not found.'], 404);
                }

                return redirect()->route('bhc.user-requests.index')->with('error', 'Request not found.');
            }

            $request = $this->prepareUserRequestRow($requestDoc->data(), $id);

            if ($httpRequest->wantsJson()) {
                return response()->json($request);
            }

            return $this->view('user-requests.show', compact('request'));
        } catch (\Exception $e) {
            if ($httpRequest->wantsJson()) {
                return response()->json(['message' => 'Failed to load request details.'], 500);
            }

            return redirect()->back()->with('error', 'Failed to load request details: ' . $e->getMessage());
        }
    }
}

