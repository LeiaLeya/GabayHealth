<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirebaseService;

class InventoryController extends Controller
{
    protected $firestore;
    protected $barangayId;

    public function __construct(FirebaseService $firebase)
    {
        $this->firestore = $firebase->getFirestore();
        // Use logged-in user's barangay ID if available
        $this->barangayId = session('user.id', 'sZK52EtUl22SSCKzSPIM');
    }

    // GET: Show all inventory
    public function index(Request $request)
    {
        $search = $request->input('search');
        $documents = $this->firestore
            ->collection("barangay/{$this->barangayId}/inventory")
            ->documents();

        $inventory = [];
        foreach ($documents as $doc) {
            if ($doc->exists()) {
                $inventory[] = array_merge($doc->data(), ['id' => $doc->id()]);
            }
        }

        // Filter by search
        if ($search) {
            $inventory = array_filter($inventory, function ($item) use ($search) {
                $search = strtolower($search);
                return (
                    str_contains(strtolower($item['name'] ?? ''), $search) ||
                    str_contains(strtolower($item['type'] ?? ''), $search) ||
                    str_contains(strtolower($item['unit'] ?? ''), $search) ||
                    str_contains(strtolower($item['status'] ?? ''), $search) ||
                    str_contains(strtolower($item['description'] ?? ''), $search)
                );
            });
        }

        $page = $request->input('page', 1);
        $perPage = 7;
        $total = count($inventory);
        $offset = ($page - 1) * $perPage;
        $paginatedItems = array_slice($inventory, $offset, $perPage);

        $items = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedItems,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('pages.inventory.index', [
            'items' => $items,
            'search' => $search,
        ]);
    }

    // POST: Store new item
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'type' => 'required',
            'unit' => 'nullable',
            'status' => 'required',
            'description' => 'nullable',
        ]);

        $this->firestore
            ->collection("barangay/{$this->barangayId}/inventory")
            ->add([
                'name' => $request->name,
                'type' => $request->type,
                'unit' => $request->unit,
                'status' => $request->status,
                'description' => $request->description,
            ]);

        return redirect()->back()->with('success', 'Item added successfully!');
    }

    // PUT: Update item
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'type' => 'required',
            'unit' => 'nullable',
            'status' => 'required',
            'description' => 'nullable',
        ]);

        $this->firestore
            ->collection("barangay/{$this->barangayId}/inventory")
            ->document($id)
            ->set([
                'name' => $request->name,
                'type' => $request->type,
                'unit' => $request->unit,
                'status' => $request->status,
                'description' => $request->description,
            ]);

        return redirect()->back()->with('success', 'Item updated successfully!');
    }

    // DELETE: Delete item
    public function destroy($id)
    {
        $this->firestore
            ->collection("barangay/{$this->barangayId}/inventory")
            ->document($id)
            ->delete();

        return redirect()->back()->with('success', 'Item deleted successfully!');
    }
}
