<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Firestore;
use Kreait\Firebase\Auth;

class FirebaseService
{
    protected $firestore;
    protected $storage;
    protected $auth;
    protected $factory;

    public function __construct()
    {
        // Construct the absolute path to the credentials file
        // Get the project root by going up from app/Services to project root
        $projectRoot = dirname(dirname(dirname(__FILE__)));
        $credentialsPath = $projectRoot . '/storage/app/firebase/firebase_credentials.json';

        if (!file_exists($credentialsPath)) {
            throw new \Exception(
                'Firebase credentials file not found at: ' . $credentialsPath
            );
        }

        try {
            // Load and validate credentials
            $credentialsJson = file_get_contents($credentialsPath);
            $credentials = json_decode($credentialsJson, true);
            
            if (!$credentials || empty($credentials['project_id'])) {
                throw new \Exception('Invalid Firebase credentials: missing or empty project_id');
            }

            // Create factory with credentials
            $this->factory = (new Factory)->withServiceAccount($credentialsPath);
            
            // Create Firestore instance - createFirestore() returns the Firestore service
            // which has a database() method to get the default database
            $firestoreService = $this->factory->createFirestore();
            $this->firestore = $firestoreService->database();
            
            $this->storage = $this->factory->createStorage();
            $this->auth = $this->factory->createAuth();
            
        } catch (\Exception $e) {
            \Log::error('Firebase initialization error: ' . $e->getMessage());
            throw new \Exception('Failed to initialize Firebase: ' . $e->getMessage());
        }
    }

    public function getFirestore()
    {
        return $this->firestore;
    }

    public function getStorage()
    {
        return $this->storage;
    }

    public function getAuth()
    {
        return $this->auth;
    }

    public function getCollection($collectionName)
    {
        return $this->firestore->collection($collectionName)->documents();
    }

    public function addDocument($collectionName, $data)
    {
        return $this->firestore->collection($collectionName)->add($data);
    }

    public function updateDocument($collectionName, $documentId, $data)
    {
        return $this->firestore->collection($collectionName)->document($documentId)->update($data);
    }

    public function deleteDocument($collectionName, $documentId)
    {
        return $this->firestore->collection($collectionName)->document($documentId)->delete();
    }
}
