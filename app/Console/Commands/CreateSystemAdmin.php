<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FirebaseService;

class CreateSystemAdmin extends Command
{
    protected $signature = 'admin:create-system-admin {--username=sysadmin} {--password=}';
    protected $description = 'Create a System Administrator account for managing RHU applications';

    public function handle()
    {
        $username = $this->option('username');
        $password = $this->option('password');

        if (!$password) {
            $password = $this->secret('Enter System Admin password (min 8 characters)');
        }

        if (strlen($password) < 8) {
            $this->error('Password must be at least 8 characters long.');
            return 1;
        }

        $firebaseService = app(FirebaseService::class);
        $firestore = $firebaseService->getFirestore();
        $auth = $firebaseService->getAuth();

        try {
            // Check if admin already exists
            $existingDocs = $firestore->collection('admin')
                ->where('username', '=', $username)
                ->documents();

            foreach ($existingDocs as $doc) {
                if ($doc->exists()) {
                    $this->error("System Admin with username '$username' already exists.");
                    return 1;
                }
            }

            // Generate email
            $email = strtolower($username) . '@gabay-health-admin.local';

            // Create Firebase Auth user
            $this->info("Creating Firebase Auth user...");
            $authUser = $auth->createUser([
                'email' => $email,
                'password' => $password,
                'displayName' => 'System Administrator',
                'emailVerified' => true,
            ]);

            $uid = $authUser->uid;
            $this->info("✓ Firebase Auth account created (UID: $uid)");

            // Store in Firestore
            $this->info("Creating Firestore document...");
            $firestore->collection('admin')->document($uid)->set([
                'username' => $username,
                'email' => $email,
                'uid' => $uid,
                'password' => bcrypt($password),
                'name' => 'System Administrator',
                'role' => 'admin',
                'status' => 'active',
                'created_at' => now()->toDateTimeString(),
            ]);

            $this->info("✓ Firestore document created");

            // Display credentials
            $this->info("\n" . str_repeat("=", 50));
            $this->info("System Administrator Account Created Successfully");
            $this->info(str_repeat("=", 50));
            $this->line("Username: <fg=green>$username</>");
            $this->line("Email: <fg=green>$email</>");
            $this->line("Firebase UID: <fg=green>$uid</>");
            $this->line("Status: <fg=green>Active</>");
            $this->info(str_repeat("=", 50));
            $this->info("\nYou can now login at: /login");
            $this->info("Then navigate to: /admin/system-admin/dashboard\n");

            return 0;
        } catch (\Kreait\Firebase\Exception\Auth\EmailExists $e) {
            $this->error("Email '$email' already exists in Firebase Auth.");
            return 1;
        } catch (\Exception $e) {
            $this->error("Error creating System Admin: " . $e->getMessage());
            return 1;
        }
    }
}
