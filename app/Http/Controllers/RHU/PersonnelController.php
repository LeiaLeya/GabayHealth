<?php

namespace App\Http\Controllers\RHU;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasRoleContext;
use Illuminate\Http\Request;
use App\Services\FirebaseService;

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
            \Log::info('RHU PersonnelController - Fetching personnel for user: ' . $user['id'] . ' with role: ' . $user['role']);
            
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
            
            \Log::info('RHU PersonnelController - Found ' . $count . ' personnel');

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
            'address' => 'nullable|string|max:500',
            'image' => 'nullable|image|max:2048',
        ]);

        $user = session('user');

        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to access personnel management.');
        }

        $personnelData = [
            'name' => $request->name,
            'position' => $request->position,
            'address' => $request->address,
            'created_at' => now()->toISOString(),
            'updated_at' => now()->toISOString(),
        ];

        if ($request->hasFile('image')) {
            $bucket = $this->firestore->getStorage()->getBucket();
            $file = $request->file('image');
            $fileName = 'personnel/' . uniqid() . '.' . $file->getClientOriginalExtension();
            $bucket->upload(
                fopen($file->getRealPath(), 'r'),
                ['name' => $fileName]
            );
            $projectId = env('FIREBASE_PROJECT_ID');
            $imageUrl = "https://firebasestorage.googleapis.com/v0/b/{$projectId}.appspot.com/o/" . rawurlencode($fileName) . "?alt=media";
            $personnelData['image_url'] = $imageUrl;
        }

        $this->firestore
            ->collection($user['role'])
            ->document($user['id'])
            ->collection('personnel')
            ->add($personnelData);

        return redirect()->route('rhu.personnel.index')->with('success', 'Personnel added successfully!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'image' => 'nullable|image|max:2048',
        ]);

        $user = session('user');

        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to access personnel management.');
        }

        $personnelData = [
            'name' => $request->name,
            'position' => $request->position,
            'address' => $request->address,
            'updated_at' => now()->toISOString(),
        ];

        if ($request->hasFile('image')) {
            $bucket = $this->firestore->getStorage()->getBucket();
            $file = $request->file('image');
            $fileName = 'personnel/' . uniqid() . '.' . $file->getClientOriginalExtension();
            $bucket->upload(
                fopen($file->getRealPath(), 'r'),
                ['name' => $fileName]
            );
            $projectId = env('FIREBASE_PROJECT_ID');
            $imageUrl = "https://firebasestorage.googleapis.com/v0/b/{$projectId}.appspot.com/o/" . rawurlencode($fileName) . "?alt=media";
            $personnelData['image_url'] = $imageUrl;
        }

        $this->firestore
            ->collection($user['role'])
            ->document($user['id'])
            ->collection('personnel')
            ->document($id)
            ->set($personnelData, ['merge' => true]);

        return redirect()->route('rhu.personnel.index')->with('success', 'Personnel updated successfully!');
    }

    public function destroy($id)
    {
        $user = session('user');

        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to access personnel management.');
        }

        $this->firestore
            ->collection($user['role'])
            ->document($user['id'])
            ->collection('personnel')
            ->document($id)
            ->delete();

        return redirect()->route('rhu.personnel.index')->with('success', 'Personnel deleted successfully!');
    }
}


