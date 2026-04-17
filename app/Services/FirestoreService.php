<?php

namespace App\Services;

use Google\Cloud\Firestore\FirestoreClient;

class FirestoreService
{
    public $db;

    public function __construct()
    {
        $this->db = new FirestoreClient([
            'keyFilePath' => base_path(env('FIREBASE_CREDENTIALS', 'firebase_credentials.json')),
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