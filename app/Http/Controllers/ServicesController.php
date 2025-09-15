<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirebaseService;

class ServicesController extends Controller
{
    protected $firestore;

    public function __construct(FirebaseService $firebase)
    {
        $this->firestore = $firebase->getFirestore();
    }

    // Show services management page
    public function index()
    {
        // Set timeout to prevent execution timeout
        set_time_limit(60);
        
        $user = session('user');
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to access services management.');
        }
        
        // Initialize currentServices as empty array
        $currentServices = [];
        $predefinedServices = $this->getPredefinedServices();
        
        try {
            \Log::info('ServicesController - Fetching services for user: ' . $user['id'] . ' with role: ' . $user['role']);
            
            // Get services from user's sub-collection
            $servicesQuery = $this->firestore
                ->collection($user['role'])
                ->document($user['id'])
                ->collection('services')
                ->limit(50) // Limit results to prevent timeout
                ->documents();

            $count = 0;
            foreach ($servicesQuery as $doc) {
                if ($doc->exists()) {
                    $currentServices[] = array_merge($doc->data(), ['id' => $doc->id()]);
                    $count++;
                }
            }
            
            \Log::info('ServicesController - Found ' . $count . ' services');

            return view('pages.services.index', compact('currentServices', 'predefinedServices'));
        } catch (\Exception $e) {
            \Log::error('Error fetching services: ' . $e->getMessage());
            return view('pages.services.index', compact('currentServices', 'predefinedServices'))->with('error', 'Error loading services data. Please try again.');
        }
    }

    // Store new service
    public function store(Request $request)
    {
        \Log::info('ServicesController::store called');
        \Log::info('Request data:', $request->all());
        
        $user = session('user');
        
        if (!$user) {
            \Log::error('No user session found for service storage');
            return redirect()->route('login');
        }

        \Log::info('User ID: ' . $user['id']);

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'category' => 'required|string|max:100',
                'description' => 'nullable|string|max:500',
                'display_name' => 'nullable|string|max:255',
                'is_custom' => 'nullable|string',
                'schedule' => 'nullable|array',
                'schedule.*' => 'nullable|array',
                'schedule.*.*' => 'nullable|string'
            ]);

            \Log::info('Validation passed:', $validated);

            // Process schedule data
            $schedule = [];
            if (isset($validated['schedule'])) {
                foreach ($validated['schedule'] as $day => $times) {
                    // Normalize and filter out empty/whitespace-only entries
                    $filtered = [];
                    if (is_array($times)) {
                        foreach ($times as $time) {
                            $timeStr = is_string($time) ? trim($time) : '';
                            if ($timeStr !== '') {
                                $filtered[] = $timeStr;
                            }
                        }
                    }
                    // Only include the day if it has at least one valid time
                    if (!empty($filtered)) {
                        $schedule[$day] = $filtered;
                    }
                }
            }

            // Get default description for predefined services
            $defaultDescription = '';
            if (($validated['is_custom'] ?? 'false') === 'false') {
                $defaultDescriptions = $this->getDefaultDescriptions();
                $defaultDescription = $defaultDescriptions[$validated['name']] ?? '';
            }

            $serviceData = [
                'name' => $validated['name'], // This will be the simple keyword
                'display_name' => $validated['display_name'] ?? $validated['name'], // Full name for display
                'category' => $validated['category'],
                'description' => $validated['description'] ?? $defaultDescription,
                'is_custom' => ($validated['is_custom'] ?? 'false') === 'true',
                'schedule' => $schedule,
                'is_active' => true, // Services are active by default
                'created_at' => now()->toISOString(),
                'updated_at' => now()->toISOString()
            ];

            \Log::info('Service data to store:', $serviceData);

            $this->firestore
                ->collection($user['role'])
                ->document($user['id'])
                ->collection('services')
                ->add($serviceData);

            \Log::info('Service stored successfully');
            return redirect()->back()->with('success', 'Service added successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed: ' . $e->getMessage());
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('Failed to add service: ' . $e->getMessage());
            \Log::error('Exception trace: ' . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Failed to add service: ' . $e->getMessage());
        }
    }

    // Update service
    public function update(Request $request, $id)
    {
        $user = session('user');
        
        if (!$user) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'display_name' => 'nullable|string|max:255',
            'schedule' => 'nullable|array',
            'schedule.*' => 'nullable|array',
            'schedule.*.*' => 'nullable|string'
        ]);

        // Process schedule data
        $schedule = [];
        if (isset($validated['schedule'])) {
            foreach ($validated['schedule'] as $day => $times) {
                // Normalize and filter out empty/whitespace-only entries
                $filtered = [];
                if (is_array($times)) {
                    foreach ($times as $time) {
                        $timeStr = is_string($time) ? trim($time) : '';
                        if ($timeStr !== '') {
                            $filtered[] = $timeStr;
                        }
                    }
                }
                // Only include the day if it has at least one valid time
                if (!empty($filtered)) {
                    $schedule[$day] = $filtered;
                }
            }
        }

        $serviceData = [
            'name' => $validated['name'],
            'display_name' => $validated['display_name'] ?? $validated['name'],
            'category' => $validated['category'],
            'description' => $validated['description'] ?? '',
            'schedule' => $schedule,
            'updated_at' => now()->toISOString()
        ];

        try {
            $this->firestore
                ->collection($user['role'])
                ->document($user['id'])
                ->collection('services')
                ->document($id)
                ->set($serviceData, ['merge' => true]);

            return redirect()->back()->with('success', 'Service updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update service: ' . $e->getMessage());
        }
    }

    // Toggle service status (suspend/enable)
    public function toggleStatus(Request $request, $id)
    {
        $user = session('user');
        
        if (!$user) {
            return redirect()->route('login');
        }

        try {
            // Get current service data
            $serviceDoc = $this->firestore
                ->collection($user['role'])
                ->document($user['id'])
                ->collection('services')
                ->document($id)
                ->snapshot();

            if (!$serviceDoc->exists()) {
                return redirect()->back()->with('error', 'Service not found.');
            }

            $serviceData = $serviceDoc->data();
            $currentStatus = $serviceData['is_active'] ?? true;
            $newStatus = !$currentStatus;

            // Prepare update payload
            $update = [
                'is_active' => $newStatus,
                'updated_at' => now()->toISOString()
            ];

            if ($newStatus === false) {
                // Disabling: require reason
                $reason = trim((string) $request->input('deactivation_reason', ''));
                if ($reason === '') {
                    return redirect()->back()->with('error', 'Please provide a reason for disabling this service.');
                }
                $update['deactivation_reason'] = $reason;
                $update['deactivated_at'] = now()->toISOString();
            } else {
                // Enabling: clear reason
                $update['deactivation_reason'] = null;
                $update['deactivated_at'] = null;
            }

            // Update the service status and reason
            $this->firestore
                ->collection($user['role'])
                ->document($user['id'])
                ->collection('services')
                ->document($id)
                ->set($update, ['merge' => true]);

            $statusText = $newStatus ? 'enabled' : 'suspended';
            return redirect()->back()->with('success', "Service {$statusText} successfully!");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update service status: ' . $e->getMessage());
        }
    }

    // Delete service
    public function destroy($id)
    {
        $user = session('user');
        
        if (!$user) {
            return redirect()->route('login');
        }

        try {
            $this->firestore
                ->collection($user['role'])
                ->document($user['id'])
                ->collection('services')
                ->document($id)
                ->delete();

            return redirect()->back()->with('success', 'Service deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete service: ' . $e->getMessage());
        }
    }



    // Get predefined service categories and options
    private function getPredefinedServices()
    {
        return [
            'Maternal and Child Health Services' => [
                'prenatal' => 'Prenatal & Postnatal',
                'birthing' => 'Birthing Services',
                'child_immunization' => 'Child Immunization',
                'newborn_screening' => 'Newborn Screening',
                'family_planning' => 'Family Planning',
            ],
            'Basic Medical Consultations and Treatment' => [
                'consultation' => 'Medical Consultation',
                'medicine' => 'Medicine Releasing',
                'wound_care' => 'Wound Care',
                'vital_signs' => 'Vital Signs'
            ],
            'Health Education and Promotion' => [
                'health_education' => 'Health Education',
                'nutrition' => 'Nutrition Education'
            ],
            'Non-Communicable Disease (NCD) Programs' => [
                'diabetes' => 'Diabetes Monitoring'
            ]
        ];
    }

    private function getDefaultDescriptions()
    {
        return [
            'prenatal' => 'Comprehensive prenatal and postnatal care for expectant mothers.',
            'immunization' => 'Essential vaccinations for infants and children to prevent diseases.',
            'growth_monitoring' => 'Regular monitoring of child growth and development milestones.',
            'newborn_screening' => 'Early detection of genetic and metabolic disorders in newborns.',
            'family_planning' => 'Contraceptive services and family planning counseling.',
            'birthing' => 'Birthing services available.',
            'child_immunization' => 'Child immunization services available for babies.',
            'consultation' => 'Professional medical consultation and diagnosis for common illnesses.',
            'medicine' => 'Releasing of basic medicine supplies.',
            'wound_care' => 'Treatment and care for minor injuries and wounds.',
            'vital_signs' => 'Monitoring of blood pressure, temperature, pulse, and respiratory rate.',
            'health_education' => 'Educational sessions on health awareness and wellness.',
            'nutrition' => 'Guidance on proper nutrition and healthy eating habits.',
            'disease_prevention' => 'Preventive measures and awareness campaigns for common diseases.',
            'hypertension' => 'Regular monitoring and management of high blood pressure.',
            'diabetes' => 'Ongoing care and monitoring for diabetes management.'
        ];
    }
} 