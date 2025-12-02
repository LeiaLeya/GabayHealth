<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\Hash;

class MigrateAccountToFirebaseAuth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'account:migrate-to-firebase-auth 
                            {username : The username of the account to migrate}
                            {--password= : The password for the account (if not provided, will use existing password from Firestore)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate an old account to Firebase Authentication while preserving all data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $username = $this->argument('username');
        $password = $this->option('password');

        $firebaseService = app(FirebaseService::class);
        $firestore = $firebaseService->getFirestore();
        $auth = $firebaseService->getAuth();

        $collections = [
            ['name' => 'barangay', 'role' => 'barangay'],
            ['name' => 'rhu', 'role' => 'rhu'],
            ['name' => 'admin', 'role' => 'admin'],
        ];

        $userDoc = null;
        $userData = null;
        $collectionName = null;
        $documentId = null;

        // Find the user by username
        foreach ($collections as $col) {
            $docs = $firestore->collection($col['name'])->where('username', '=', $username)->documents();
            foreach ($docs as $doc) {
                if ($doc->exists()) {
                    $data = $doc->data();
                    // Check if user already has Firebase Auth
                    if (isset($data['email']) && isset($data['uid'])) {
                        $this->error("Account '{$username}' already has Firebase Auth (email: {$data['email']}, uid: {$data['uid']})");
                        return 1;
                    }
                    $userDoc = $doc;
                    $userData = $data;
                    $collectionName = $col['name'];
                    $documentId = $doc->id();
                    break 2;
                }
            }
        }

        if (!$userDoc) {
            $this->error("Account with username '{$username}' not found.");
            return 1;
        }

        $this->info("Found account: {$username} in collection '{$collectionName}' with document ID: {$documentId}");

        // Get password
        if (!$password) {
            if (!isset($userData['password'])) {
                $this->error("No password found in Firestore and no password provided. Cannot create Firebase Auth account.");
                return 1;
            }
            
            // For old accounts, password might be bcrypt hashed
            // We need the plain text password to create Firebase Auth account
            $this->warn("Password is stored as hash in Firestore. You need to provide the plain text password.");
            $this->warn("Please run: php artisan account:migrate-to-firebase-auth {$username} --password=YOUR_PLAIN_TEXT_PASSWORD");
            return 1;
        }

        // Generate email from username
        $email = strtolower($username) . '@gabay-health.local';

        try {
            // Check if email already exists in Firebase Auth
            try {
                $existingUser = $auth->getUserByEmail($email);
                $this->warn("Firebase Auth account with email '{$email}' already exists (UID: {$existingUser->uid})");
                $this->info("Updating Firestore document to link with existing Firebase Auth account...");
                
                // Update Firestore document with existing UID
                $updateData = [
                    ['path' => 'email', 'value' => $email],
                    ['path' => 'uid', 'value' => $existingUser->uid],
                ];
                
                // If document ID is different from UID, we might need to move the document
                if ($documentId !== $existingUser->uid) {
                    $this->warn("Document ID ({$documentId}) differs from Firebase UID ({$existingUser->uid})");
                    $this->info("Copying document data to new location with UID as document ID...");
                    
                    // Get all subcollections
                    $subcollections = ['inventory', 'personnel', 'schedules', 'events', 'userRequests', 'accounts', 'notifications'];
                    
                    // Copy main document - merge userData with email and uid
                    $newDocData = array_merge($userData, [
                        'email' => $email,
                        'uid' => $existingUser->uid,
                    ]);
                    $newDocRef = $firestore->collection($collectionName)->document($existingUser->uid);
                    $newDocRef->set($newDocData);
                    
                    // Copy subcollections
                    foreach ($subcollections as $subcol) {
                        try {
                            $subDocs = $firestore->collection($collectionName)->document($documentId)->collection($subcol)->documents();
                            foreach ($subDocs as $subDoc) {
                                if ($subDoc->exists()) {
                                    $newDocRef->collection($subcol)->document($subDoc->id())->set($subDoc->data());
                                }
                            }
                            $this->info("Copied subcollection: {$subcol}");
                        } catch (\Exception $e) {
                            $this->warn("Could not copy subcollection {$subcol}: " . $e->getMessage());
                        }
                    }
                    
                    $this->info("Document copied successfully. Old document at {$documentId} can be deleted manually if needed.");
                } else {
                    // Just update the existing document
                    $firestore->collection($collectionName)->document($documentId)->update($updateData);
                }
                
                $this->info("✓ Account migrated successfully!");
                $this->info("Email: {$email}");
                $this->info("UID: {$existingUser->uid}");
                return 0;
            } catch (\Kreait\Firebase\Exception\Auth\UserNotFound $e) {
                // User doesn't exist, create new one
            }

            // Create Firebase Auth user
            $this->info("Creating Firebase Auth account...");
            $authUser = $auth->createUser([
                'email' => $email,
                'password' => $password,
                'displayName' => $userData['healthCenterName'] ?? $userData['name'] ?? $username,
                'emailVerified' => false,
            ]);

            $uid = $authUser->uid;
            $this->info("✓ Firebase Auth account created (UID: {$uid})");

            // Update Firestore document
            $this->info("Updating Firestore document...");

            // If document ID is different from UID, we need to move the document
            if ($documentId !== $uid) {
                $this->warn("Document ID ({$documentId}) differs from Firebase UID ({$uid})");
                $this->info("Copying document data to new location with UID as document ID...");
                
                // Get all subcollections
                $subcollections = ['inventory', 'personnel', 'schedules', 'events', 'userRequests', 'accounts', 'notifications'];
                
                // Copy main document - merge userData with email and uid
                $newDocData = array_merge($userData, [
                    'email' => $email,
                    'uid' => $uid,
                ]);
                $newDocRef = $firestore->collection($collectionName)->document($uid);
                $newDocRef->set($newDocData);
                
                // Copy subcollections
                foreach ($subcollections as $subcol) {
                    try {
                        $subDocs = $firestore->collection($collectionName)->document($documentId)->collection($subcol)->documents();
                        foreach ($subDocs as $subDoc) {
                            if ($subDoc->exists()) {
                                $newDocRef->collection($subcol)->document($subDoc->id())->set($subDoc->data());
                            }
                        }
                        $this->info("Copied subcollection: {$subcol}");
                    } catch (\Exception $e) {
                        $this->warn("Could not copy subcollection {$subcol}: " . $e->getMessage());
                    }
                }
                
                $this->info("Document copied successfully. Old document at {$documentId} can be deleted manually if needed.");
            } else {
                // Just update the existing document with email and uid
                $updateData = [
                    ['path' => 'email', 'value' => $email],
                    ['path' => 'uid', 'value' => $uid],
                ];
                $firestore->collection($collectionName)->document($documentId)->update($updateData);
            }

            $this->info("✓ Account migrated successfully!");
            $this->info("Email: {$email}");
            $this->info("UID: {$uid}");
            $this->info("You can now login with username: {$username} and your password.");
            
            return 0;
        } catch (\Kreait\Firebase\Exception\Auth\EmailExists $e) {
            $this->error("Email '{$email}' already exists in Firebase Auth. Please check Firebase Console.");
            return 1;
        } catch (\Exception $e) {
            $this->error("Error migrating account: " . $e->getMessage());
            \Log::error("Migration error: " . $e->getMessage());
            return 1;
        }
    }
}

