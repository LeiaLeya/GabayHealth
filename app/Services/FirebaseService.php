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
}
