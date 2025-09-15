<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\FirebaseService;
use Carbon\Carbon;

class HealthReportSeeder extends Seeder
{
    public function run()
    {
        $firebase = app(FirebaseService::class);
        $firestore = $firebase->getFirestore();
        
        // Sample barangay IDs (you can adjust these based on your actual barangay IDs)
        $barangayIds = [
            '65ca697c8fe9435a9cff', // Example barangay ID
        ];
        
        // Sample health reports data
        $sampleReports = [
            [
                'barangay' => 'Cadulawan',
                'condition' => 'fever',
                'cases' => 15,
                'date' => Carbon::now()->subDays(rand(1, 30))->format('Y-m-d'),
                'description' => 'High fever cases reported',
                'reported_by' => 'Dr. Santos',
                'status' => 'verified'
            ],
            [
                'barangay' => 'Vito',
                'condition' => 'dengue',
                'cases' => 8,
                'date' => Carbon::now()->subDays(rand(1, 30))->format('Y-m-d'),
                'description' => 'Dengue cases detected',
                'reported_by' => 'Dr. Garcia',
                'status' => 'verified'
            ],
            [
                'barangay' => 'Tubod',
                'condition' => 'dengue',
                'cases' => 12,
                'date' => Carbon::now()->subDays(rand(1, 30))->format('Y-m-d'),
                'description' => 'Dengue outbreak in Tubod',
                'reported_by' => 'Dr. Martinez',
                'status' => 'verified'
            ],
            [
                'barangay' => 'Linao',
                'condition' => 'fever',
                'cases' => 20,
                'date' => Carbon::now()->subDays(rand(1, 30))->format('Y-m-d'),
                'description' => 'Fever cases in Linao',
                'reported_by' => 'Dr. Rodriguez',
                'status' => 'verified'
            ],
            [
                'barangay' => 'PAKIGNE',
                'condition' => 'rash',
                'cases' => 5,
                'date' => Carbon::now()->subDays(rand(1, 30))->format('Y-m-d'),
                'description' => 'Skin rash cases',
                'reported_by' => 'Dr. Lopez',
                'status' => 'verified'
            ],
            [
                'barangay' => 'Manduang',
                'condition' => 'diarrhea',
                'cases' => 18,
                'date' => Carbon::now()->subDays(rand(1, 30))->format('Y-m-d'),
                'description' => 'Diarrhea cases in Manduang',
                'reported_by' => 'Dr. Cruz',
                'status' => 'verified'
            ],
            [
                'barangay' => 'Camp 7',
                'condition' => 'fever',
                'cases' => 10,
                'date' => Carbon::now()->subDays(rand(1, 30))->format('Y-m-d'),
                'description' => 'Fever cases in Camp 7',
                'reported_by' => 'Dr. Reyes',
                'status' => 'verified'
            ],
            [
                'barangay' => 'Cuanos',
                'condition' => 'dengue',
                'cases' => 6,
                'date' => Carbon::now()->subDays(rand(1, 30))->format('Y-m-d'),
                'description' => 'Dengue cases in Cuanos',
                'reported_by' => 'Dr. Torres',
                'status' => 'verified'
            ],
            [
                'barangay' => 'Tunghaan',
                'condition' => 'diarrhea',
                'cases' => 14,
                'date' => Carbon::now()->subDays(rand(1, 30))->format('Y-m-d'),
                'description' => 'Diarrhea outbreak',
                'reported_by' => 'Dr. Fernandez',
                'status' => 'verified'
            ],
            [
                'barangay' => 'Pob. Ward I',
                'condition' => 'fever',
                'cases' => 25,
                'date' => Carbon::now()->subDays(rand(1, 30))->format('Y-m-d'),
                'description' => 'High fever cases in Pob. Ward I',
                'reported_by' => 'Dr. Gonzales',
                'status' => 'verified'
            ],
            [
                'barangay' => 'Pob. Ward II',
                'condition' => 'diarrhea',
                'cases' => 16,
                'date' => Carbon::now()->subDays(rand(1, 30))->format('Y-m-d'),
                'description' => 'Diarrhea cases in Pob. Ward II',
                'reported_by' => 'Dr. Perez',
                'status' => 'verified'
            ],
            [
                'barangay' => 'Calajoan',
                'condition' => 'rash',
                'cases' => 7,
                'date' => Carbon::now()->subDays(rand(1, 30))->format('Y-m-d'),
                'description' => 'Skin rash cases in Calajoan',
                'reported_by' => 'Dr. Morales',
                'status' => 'verified'
            ],
            [
                'barangay' => 'Guindarohan',
                'condition' => 'fever',
                'cases' => 12,
                'date' => Carbon::now()->subDays(rand(1, 30))->format('Y-m-d'),
                'description' => 'Fever cases in Guindarohan',
                'reported_by' => 'Dr. Ramos',
                'status' => 'verified'
            ],
            [
                'barangay' => 'Pob. Ward III',
                'condition' => 'dengue',
                'cases' => 9,
                'date' => Carbon::now()->subDays(rand(1, 30))->format('Y-m-d'),
                'description' => 'Dengue cases in Pob. Ward III',
                'reported_by' => 'Dr. Jimenez',
                'status' => 'verified'
            ],
            [
                'barangay' => 'Tulay',
                'condition' => 'diarrhea',
                'cases' => 11,
                'date' => Carbon::now()->subDays(rand(1, 30))->format('Y-m-d'),
                'description' => 'Diarrhea cases in Tulay',
                'reported_by' => 'Dr. Herrera',
                'status' => 'verified'
            ],
            [
                'barangay' => 'Camp B',
                'condition' => 'fever',
                'cases' => 8,
                'date' => Carbon::now()->subDays(rand(1, 30))->format('Y-m-d'),
                'description' => 'Fever cases in Camp B',
                'reported_by' => 'Dr. Silva',
                'status' => 'verified'
            ],
            [
                'barangay' => 'Pob. Ward IV',
                'condition' => 'rash',
                'cases' => 6,
                'date' => Carbon::now()->subDays(rand(1, 30))->format('Y-m-d'),
                'description' => 'Skin rash cases in Pob. Ward IV',
                'reported_by' => 'Dr. Vargas',
                'status' => 'verified'
            ],
            [
                'barangay' => 'Tungkap',
                'condition' => 'dengue',
                'cases' => 4,
                'date' => Carbon::now()->subDays(rand(1, 30))->format('Y-m-d'),
                'description' => 'Dengue cases in Tungkap',
                'reported_by' => 'Dr. Castro',
                'status' => 'verified'
            ]
        ];
        
        foreach ($barangayIds as $barangayId) {
            foreach ($sampleReports as $report) {
                try {
                    $firestore
                        ->collection("barangay/{$barangayId}/healthReports")
                        ->add($report);
                    
                    $this->command->info("Added health report for {$report['barangay']} - {$report['condition']}");
                } catch (\Exception $e) {
                    $this->command->error("Error adding health report: " . $e->getMessage());
                }
            }
        }
    }
} 