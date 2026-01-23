<?php

namespace App\Services;

use Google\Cloud\Firestore\FirestoreClient;

class FirestoreService
{
    public $db;

    public function __construct()
    {
        // Use the same path as FirebaseService
        $projectRoot = dirname(dirname(dirname(__FILE__)));
        $credentialsPath = $projectRoot . '/storage/app/firebase/firebase_credentials.json';

        if (!file_exists($credentialsPath)) {
            throw new \Exception(
                'Firebase credentials file not found at: ' . $credentialsPath
            );
        }

        $this->db = new FirestoreClient([
            'keyFilePath' => $credentialsPath,
            'projectId' => env('FIREBASE_PROJECT_ID'),
        ]);
    }

    public function getCollection($collection)
    {
        return $this->db->collection($collection)->documents();
    }

    public function addDocument($collection, array $data)
    {
        return $this->db->collection($collection)->add($data);
    }
}