<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Firestore;

class FirebaseService
{
    protected $firestore;
    protected $storage;

    public function __construct()
    {
        $serviceAccount = storage_path('app/firebase/firebase_credentials.json');
        $factory = (new Factory)->withServiceAccount($serviceAccount);
        $this->firestore = $factory->createFirestore()->database();
        $this->storage = $factory->createStorage();
    }

    public function getFirestore()
    {
        return $this->firestore;
    }

    public function getStorage()
    {
        return $this->storage;
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
