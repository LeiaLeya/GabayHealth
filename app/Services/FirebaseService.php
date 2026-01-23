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

        $factory = (new Factory)->withServiceAccount($credentialsPath);
        $this->firestore = $factory->createFirestore()->database();
        $this->storage = $factory->createStorage();
        $this->auth = $factory->createAuth();
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
