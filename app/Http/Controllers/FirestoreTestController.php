<?php

namespace App\Http\Controllers;

use App\Services\FirestoreService;

class FirestoreTestController extends Controller
{
    public function index(FirestoreService $firestore)
    {
        $documents = $firestore->getCollection('rhu'); 
        foreach ($documents as $doc) {
            dump($doc->id(), $doc->data());
        }
        return 'Check your debug output!';
    }
}