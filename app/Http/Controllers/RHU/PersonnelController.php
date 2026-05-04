<?php

namespace App\Http\Controllers\RHU;

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
            $personnelQuery = $this->firestore
                ->collection($user['role'])
                ->document($user['id'])
                ->collection('personnel')
                ->limit(50)
                ->documents();

            foreach ($personnelQuery as $doc) {
                if ($doc->exists()) {
                    $personnel[] = array_merge($doc->data(), ['id' => $doc->id()]);
                }
            }

            try {
                $staffAccountsQuery = $this->firestore
                    ->collection($user['role'])
                    ->document($user['id'])
                    ->collection('accounts')
                    ->documents();

                foreach ($staffAccountsQuery as $doc) {
                    if ($doc->exists()) {
                        $availablePersonnel[] = array_merge(['id' => $doc->id()], $doc->data());
                    }
                }
            } catch (\Exception $e) {
                // continue with empty available personnel
            }

            return $this->view('personnel.index', compact('personnel', 'availablePersonnel'));
        } catch (\Exception $e) {
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

        $imageUrl = $this->uploadPersonnelImage($request);
        if ($imageUrl === false) {
            return redirect()->route('rhu.personnel.index')->with('error', 'Failed to upload image. Please try again.');
        }
        if ($imageUrl) {
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
            'address' => 'required|string|max:500',
            'image' => 'nullable|image|max:2048',
            'image_data' => 'nullable|string',
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

        $imageUrl = $this->uploadPersonnelImage($request);
        if ($imageUrl === false) {
            return redirect()->route('rhu.personnel.index')->with('error', 'Failed to upload image. Please try again.');
        }
        if ($imageUrl) {
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
            if ($deleteAfterUpload && $filePath) {
                @unlink($filePath);
            }
            return false;
        }
    }
}
