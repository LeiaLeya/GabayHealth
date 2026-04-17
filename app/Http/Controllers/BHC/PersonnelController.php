<?php

namespace App\Http\Controllers\BHC;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasRoleContext;
use Illuminate\Http\Request;
use App\Services\FirebaseService;
use Cloudinary\Cloudinary;

class PersonnelController extends Controller
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
            return redirect()->route('login')->with('error', 'Please login to access personnel management.');
        }
        
        $personnel = [];
        $availablePersonnel = [];
        
        try {
            \Log::info('BHC PersonnelController - Fetching personnel for user: ' . $user['id'] . ' with role: ' . $user['role']);
            
            $personnelQuery = $this->firestore
                ->collection($user['role'])
                ->document($user['id'])
                ->collection('personnel')
                ->limit(50)
                ->documents();

            $count = 0;
            foreach ($personnelQuery as $doc) {
                if ($doc->exists()) {
                    $personnel[] = array_merge($doc->data(), ['id' => $doc->id()]);
                    $count++;
                }
            }
            
            \Log::info('BHC PersonnelController - Found ' . $count . ' personnel');

            // Get staff accounts from account management for dropdown
            try {
                $staffAccountsQuery = $this->firestore
                    ->collection($user['role'])
                    ->document($user['id'])
                    ->collection('accounts')
                    ->documents();

                foreach ($staffAccountsQuery as $doc) {
                    if ($doc->exists()) {
                        $data = $doc->data();
                        $availablePersonnel[] = array_merge(['id' => $doc->id()], $data);
                    }
                }
                
                \Log::info('BHC PersonnelController - Found ' . count($availablePersonnel) . ' available staff accounts');
            } catch (\Exception $e) {
                \Log::error('Error fetching staff accounts: ' . $e->getMessage());
            }

            return $this->view('personnel.index', compact('personnel', 'availablePersonnel'));
        } catch (\Exception $e) {
            \Log::error('Error fetching personnel: ' . $e->getMessage());
            return $this->view('personnel.index', compact('personnel', 'availablePersonnel'))->with('error', 'Error loading personnel data. Please try again.');
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'image' => 'nullable|image|max:2048',
            'image_data' => 'nullable|string',
        ]);

        $user = session('user');
        $barangayId = $this->getBarangayId();

        if (!$barangayId) {
            return redirect()->route('bhc.personnel.index')->with('error', 'Barangay ID not found.');
        }

        $personnelData = [
            'name' => $request->name,
            'position' => $request->position,
            'address' => $request->address,
            'created_at' => now()->toISOString(),
            'updated_at' => now()->toISOString(),
        ];

        $imageUrl = $this->uploadPersonnelImage($request);
        if ($imageUrl === false) {
            return redirect()->route('bhc.personnel.index')->with('error', 'Failed to upload image. Please try again.');
        }
        if ($imageUrl) {
            $personnelData['image_url'] = $imageUrl;
        }

        $this->firestore
            ->collection("barangay/{$barangayId}/personnel")
            ->add($personnelData);

        return redirect()->route('bhc.personnel.index')->with('success', 'Personnel added successfully!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'image' => 'nullable|image|max:2048',
            'image_data' => 'nullable|string',
        ]);

        $user = session('user');
        $barangayId = $this->getBarangayId();

        if (!$barangayId) {
            return redirect()->route('bhc.personnel.index')->with('error', 'Barangay ID not found.');
        }

        $personnelData = [
            'name' => $request->name,
            'position' => $request->position,
            'address' => $request->address,
            'updated_at' => now()->toISOString(),
        ];

        $imageUrl = $this->uploadPersonnelImage($request);
        if ($imageUrl === false) {
            return redirect()->route('bhc.personnel.index')->with('error', 'Failed to upload image. Please try again.');
        }
        if ($imageUrl) {
            $personnelData['image_url'] = $imageUrl;
        }

        $this->firestore
            ->collection("barangay/{$barangayId}/personnel")
            ->document($id)
            ->set($personnelData, ['merge' => true]);

        return redirect()->route('bhc.personnel.index')->with('success', 'Personnel updated successfully!');
    }

    public function destroy($id)
    {
        $user = session('user');
        $barangayId = $this->getBarangayId();

        if (!$barangayId) {
            return redirect()->route('bhc.personnel.index')->with('error', 'Barangay ID not found.');
        }

        $this->firestore
            ->collection("barangay/{$barangayId}/personnel")
            ->document($id)
            ->delete();

        return redirect()->route('bhc.personnel.index')->with('success', 'Personnel deleted successfully!');
    }

    /**
     * Upload personnel image from file or base64 (cropped). Returns URL or false on error.
     */
    protected function uploadPersonnelImage(Request $request): ?string
    {
        $filePath = null;
        $deleteAfterUpload = false;

        if ($request->filled('image_data')) {
            $base64 = $request->image_data;
            if (preg_match('/^data:image\/(\w+);base64,/', $base64, $matches)) {
                $data = substr($base64, strpos($base64, ',') + 1);
                $data = base64_decode($data);
                if ($data !== false) {
                    $tmpFile = tempnam(sys_get_temp_dir(), 'personnel_');
                    file_put_contents($tmpFile, $data);
                    $filePath = $tmpFile;
                    $deleteAfterUpload = true;
                }
            }
        } elseif ($request->hasFile('image')) {
            $filePath = $request->file('image')->getRealPath();
        }

        if (!$filePath) {
            return null;
        }

        try {
            $cloudinary = new Cloudinary([
                'cloud' => [
                    'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                    'api_key' => env('CLOUDINARY_API_KEY'),
                    'api_secret' => env('CLOUDINARY_API_SECRET'),
                ],
            ]);
            $result = $cloudinary->uploadApi()->upload($filePath, [
                'folder' => 'personnel',
                'public_id' => uniqid(),
            ]);
            if ($deleteAfterUpload && $filePath) {
                @unlink($filePath);
            }
            return $result['secure_url'];
        } catch (\Exception $e) {
            \Log::error('Cloudinary upload error: ' . $e->getMessage());
            if ($deleteAfterUpload && $filePath) {
                @unlink($filePath);
            }
            return false;
        }
    }
}

