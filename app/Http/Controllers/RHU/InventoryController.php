<?php

namespace App\Http\Controllers\RHU;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasRoleContext;
use Illuminate\Http\Request;
use App\Services\FirebaseService;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Kreait\Firebase\Exception\AuthException;
use Kreait\Firebase\Exception\FirebaseException;

class InventoryController extends Controller
{
    use HasRoleContext;

    protected $firestore;
    protected $auth;

    public function __construct(FirebaseService $firebase)
    {
        $this->firestore = $firebase->getFirestore();
        $this->auth = $firebase->getAuth();
    }

    public function index(Request $request)
    {
        set_time_limit(60);

        $user = session('user');

        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to access inventory management.');
        }

        $search = $request->get('search', '');
        $filterType = $request->get('type', '');
        $filterStatus = $request->get('status', '');
        $filterUnitType = $request->get('unit_type', '');
        $sortBy = $request->get('sort_by', '');
        $sortDir = strtolower($request->get('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $items = [];
        $inventorySummary = [
            'total_items' => 0,
            'total_quantity' => 0,
            'total_batches' => 0,
            'expiring_soon' => 0,
            'expired' => 0,
            'low_stock' => 0,
            'out_of_stock' => 0,
            'available' => 0,
            'by_type' => [
                'Medicine' => 0,
                'Equipment' => 0,
                'Supplies' => 0,
                'Vaccine' => 0,
                'Other' => 0
            ]
        ];

        try {
            $inventoryQuery = $this->firestore
                ->collection($user['role'])
                ->document($user['id'])
                ->collection('inventory')
                ->limit(1000)
                ->documents();

            foreach ($inventoryQuery as $doc) {
                if ($doc->exists()) {
                    $itemData = array_merge($doc->data(), ['id' => $doc->id()]);

                    if (!empty($search)) {
                        $searchLower = strtolower($search);
                        $itemName = strtolower($itemData['name'] ?? '');
                        $itemType = strtolower($itemData['type'] ?? '');
                        $itemDescription = strtolower($itemData['description'] ?? '');

                        if (strpos($itemName, $searchLower) === false &&
                            strpos($itemType, $searchLower) === false &&
                            strpos($itemDescription, $searchLower) === false) {
                            continue;
                        }
                    }

                    if (!empty($filterType)) {
                        if (strcasecmp($itemData['type'] ?? '', $filterType) !== 0) {
                            continue;
                        }
                    }

                    if (!empty($filterStatus)) {
                        if (strcasecmp($itemData['status'] ?? '', $filterStatus) !== 0) {
                            continue;
                        }
                    }

                    if (!empty($filterUnitType)) {
                        if (strcasecmp($itemData['unit_type'] ?? '', $filterUnitType) !== 0) {
                            continue;
                        }
                    }

                    $soonestExpirationDays = null;
                    $hasExpirationData = false;
                    $today = Carbon::now()->startOfDay();

                    try {
                        $batchesQuery = $this->firestore
                            ->collection($user['role'])
                            ->document($user['id'])
                            ->collection('inventory')
                            ->document($doc->id())
                            ->collection('batches')
                            ->documents();

                        foreach ($batchesQuery as $batchDoc) {
                            if ($batchDoc->exists()) {
                                $batchData = $batchDoc->data();
                                $inventorySummary['total_batches']++;

                                if (!empty($batchData['expiration_date'])) {
                                    $hasExpirationData = true;

                                    $expirationDate = Carbon::parse($batchData['expiration_date'])->startOfDay();
                                    $diffInDays = $today->diffInDays($expirationDate, false);

                                    if ($diffInDays <= 0) {
                                        $inventorySummary['expired']++;
                                    } elseif ($diffInDays <= 30) {
                                        $inventorySummary['expiring_soon']++;
                                    }

                                    if ($soonestExpirationDays === null || $diffInDays < $soonestExpirationDays) {
                                        $soonestExpirationDays = $diffInDays;
                                    }
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        // continue without batch data
                    }

                    $itemData['soonest_expiration_days'] = $hasExpirationData ? $soonestExpirationDays : null;

                    $items[] = $itemData;

                    $inventorySummary['total_items']++;
                    $inventorySummary['total_quantity'] += $itemData['quantity'] ?? 0;

                    $type = $itemData['type'] ?? 'Other';
                    if (isset($inventorySummary['by_type'][$type])) {
                        $inventorySummary['by_type'][$type]++;
                    } else {
                        $inventorySummary['by_type']['Other']++;
                    }

                    $status = $itemData['status'] ?? 'available';
                    switch ($status) {
                        case 'low_stock':
                            $inventorySummary['low_stock']++;
                            break;
                        case 'out_of_stock':
                            $inventorySummary['out_of_stock']++;
                            break;
                        default:
                            $inventorySummary['available']++;
                            break;
                    }
                }
            }

            $sortedItems = $items;

            if (!empty($sortBy)) {
                usort($sortedItems, function ($a, $b) use ($sortBy, $sortDir) {
                    $direction = $sortDir === 'desc' ? -1 : 1;
                    switch ($sortBy) {
                        case 'name':
                            return $direction * strcasecmp($a['name'] ?? '', $b['name'] ?? '');
                        case 'quantity':
                            $qa = (int)($a['quantity'] ?? 0);
                            $qb = (int)($b['quantity'] ?? 0);
                            if ($qa === $qb) return 0;
                            return $direction * (($qa < $qb) ? -1 : 1);
                        default:
                            return 0;
                    }
                });
            }

            $medicineGroupsMap = [];
            $materialItems = [];

            foreach ($sortedItems as $item) {
                $type = $item['type'] ?? '';
                $isMedicine = in_array($type, ['Medicine', 'Vaccine']);

                if ($isMedicine) {
                    $genericRaw = trim($item['generic_name'] ?? '');
                    $genericName = $genericRaw !== '' ? $genericRaw : 'Unassigned Generic Name';
                    $genericKey = strtolower($genericName);

                    if (!isset($medicineGroupsMap[$genericKey])) {
                        $medicineGroupsMap[$genericKey] = [
                            'generic_name' => $genericName,
                            'description' => $item['generic_description'] ?? '',
                            'items' => [],
                            'total_quantity' => 0,
                            'soonest_expiration_days' => $item['soonest_expiration_days'] ?? null,
                        ];
                    }

                    $medicineGroupsMap[$genericKey]['items'][] = $item;
                    $medicineGroupsMap[$genericKey]['total_quantity'] += $item['quantity'] ?? 0;

                    if (empty($medicineGroupsMap[$genericKey]['description']) && !empty($item['generic_description'])) {
                        $medicineGroupsMap[$genericKey]['description'] = $item['generic_description'];
                    }

                    $itemExpiration = $item['soonest_expiration_days'] ?? null;
                    $currentGroupExpiration = $medicineGroupsMap[$genericKey]['soonest_expiration_days'];

                    if ($itemExpiration !== null && ($currentGroupExpiration === null || $itemExpiration < $currentGroupExpiration)) {
                        $medicineGroupsMap[$genericKey]['soonest_expiration_days'] = $itemExpiration;
                    }
                } else {
                    $materialItems[] = $item;
                }
            }

            foreach ($medicineGroupsMap as &$group) {
                usort($group['items'], function ($a, $b) {
                    return strcasecmp($a['name'] ?? '', $b['name'] ?? '');
                });
            }
            unset($group);

            $medicineGroups = array_values($medicineGroupsMap);
            usort($medicineGroups, function ($a, $b) {
                return strcasecmp($a['generic_name'], $b['generic_name']);
            });

            $perPage = 10;
            $currentPage = $request->get('page', 1);
            $offset = ($currentPage - 1) * $perPage;
            $paginatedMaterials = array_slice($materialItems, $offset, $perPage);

            $materials = new LengthAwarePaginator(
                $paginatedMaterials,
                count($materialItems),
                $perPage,
                $currentPage,
                [
                    'path' => request()->url(),
                    'pageName' => 'page',
                    'query' => request()->query()
                ]
            );

            return $this->view('inventory.index', [
                'medicineGroups' => $medicineGroups,
                'materials' => $materials,
                'inventorySummary' => $inventorySummary,
                'search' => $search,
                'filterType' => $filterType,
                'filterStatus' => $filterStatus,
                'filterUnitType' => $filterUnitType,
                'sortBy' => $sortBy,
                'sortDir' => $sortDir,
            ]);
        } catch (\Exception $e) {
            return $this->view('inventory.index', [
                'medicineGroups' => [],
                'materials' => new LengthAwarePaginator([], 0, 10, 1, [
                    'path' => request()->url(),
                    'pageName' => 'page',
                ]),
                'inventorySummary' => $inventorySummary,
                'search' => $search,
                'filterType' => $filterType,
                'filterStatus' => $filterStatus,
                'filterUnitType' => $filterUnitType,
                'sortBy' => $sortBy,
                'sortDir' => $sortDir,
            ])->with('error', 'Error loading inventory data. Please try again.');
        }
    }

    public function show($id)
    {
        $user = session('user');
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to access inventory management.');
        }

        $parentItem = $this->firestore
            ->collection($user['role'])
            ->document($user['id'])
            ->collection('inventory')
            ->document($id)
            ->snapshot();

        if (!$parentItem->exists()) {
            return redirect()->route('rhu.inventory.index')->with('error', 'Item not found.');
        }

        $parentData = array_merge(['id' => $parentItem->id()], $parentItem->data());

        $childDocuments = $this->firestore
            ->collection($user['role'])
            ->document($user['id'])
            ->collection('inventory')
            ->document($id)
            ->collection('batches')
            ->documents();

        $batches = [];
        foreach ($childDocuments as $doc) {
            if ($doc->exists()) {
                $batches[] = array_merge(['id' => $doc->id()], $doc->data());
            }
        }

        usort($batches, function ($a, $b) {
            return strtotime($a['expiration_date'] ?? '9999-12-31') - strtotime($b['expiration_date'] ?? '9999-12-31');
        });

        $allMedicines = $this->getAllMedicines();

        return $this->view('inventory.show', compact('parentData', 'batches', 'allMedicines'));
    }

    public function showSorted($id, Request $request)
    {
        $user = session('user');
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to access inventory management.');
        }

        $parentItem = $this->firestore
            ->collection($user['role'])
            ->document($user['id'])
            ->collection('inventory')
            ->document($id)
            ->snapshot();

        if (!$parentItem->exists()) {
            return redirect()->route('rhu.inventory.index')->with('error', 'Item not found.');
        }

        $parentData = array_merge(['id' => $parentItem->id()], $parentItem->data());

        $childDocuments = $this->firestore
            ->collection($user['role'])
            ->document($user['id'])
            ->collection('inventory')
            ->document($id)
            ->collection('batches')
            ->documents();

        $batches = [];
        foreach ($childDocuments as $doc) {
            if ($doc->exists()) {
                $batches[] = array_merge(['id' => $doc->id()], $doc->data());
            }
        }

        $sortDirection = $request->get('direction', 'asc');

        usort($batches, function ($a, $b) use ($sortDirection) {
            $dateA = strtotime($a['expiration_date'] ?? '9999-12-31');
            $dateB = strtotime($b['expiration_date'] ?? '9999-12-31');

            if ($sortDirection === 'desc') {
                return $dateB - $dateA;
            } else {
                return $dateA - $dateB;
            }
        });

        $allMedicines = $this->getAllMedicines();

        return $this->view('inventory.show', compact('parentData', 'batches', 'allMedicines', 'sortDirection'));
    }

    public function showAddBatch()
    {
        $user = session('user');
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to access inventory management.');
        }

        $allMedicines = $this->getAllMedicines();

        return $this->view('inventory.add-batch', compact('allMedicines'));
    }

    private function getAllMedicines()
    {
        $user = session('user');
        if (!$user) {
            return [];
        }

        $documents = $this->firestore
            ->collection($user['role'])
            ->document($user['id'])
            ->collection('inventory')
            ->documents();

        $medicines = [];
        foreach ($documents as $doc) {
            if ($doc->exists()) {
                $data = $doc->data();
                $medicines[] = [
                    'id' => $doc->id(),
                    'name' => $data['name'] ?? '',
                    'type' => $data['type'] ?? '',
                    'unit_type' => $data['unit_type'] ?? '',
                    'quantity' => $data['quantity'] ?? 0,
                    'status' => $data['status'] ?? ''
                ];
            }
        }

        return $medicines;
    }

    public function showDistributionHistory($parentId, $batchId)
    {
        $user = session('user');
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to access inventory management.');
        }

        $parentItem = $this->firestore
            ->collection($user['role'])
            ->document($user['id'])
            ->collection('inventory')
            ->document($parentId)
            ->snapshot();

        if (!$parentItem->exists()) {
            return redirect()->route('rhu.inventory.index')->with('error', 'Item not found.');
        }

        $parentData = array_merge(['id' => $parentItem->id()], $parentItem->data());

        $batchDoc = $this->firestore
            ->collection($user['role'])
            ->document($user['id'])
            ->collection('inventory')
            ->document($parentId)
            ->collection('batches')
            ->document($batchId)
            ->snapshot();

        if (!$batchDoc->exists()) {
            return redirect()->route('rhu.inventory.show', $parentId)->with('error', 'Batch not found.');
        }

        $batchData = array_merge(['id' => $batchDoc->id()], $batchDoc->data());

        $distributionDocs = $this->firestore
            ->collection($user['role'])
            ->document($user['id'])
            ->collection('inventory')
            ->document($parentId)
            ->collection('batches')
            ->document($batchId)
            ->collection('distributions')
            ->documents();

        $distributions = [];
        foreach ($distributionDocs as $doc) {
            if ($doc->exists()) {
                $distributions[] = array_merge(['id' => $doc->id()], $doc->data());
            }
        }

        usort($distributions, function ($a, $b) {
            return strtotime($b['distribution_date'] ?? '1970-01-01') - strtotime($a['distribution_date'] ?? '1970-01-01');
        });

        return $this->view('inventory.distribution-history', compact('parentData', 'batchData', 'distributions'));
    }

    public function showReleaseHistory($parentId)
    {
        $user = session('user');
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to access inventory management.');
        }

        $parentItem = $this->firestore
            ->collection($user['role'])
            ->document($user['id'])
            ->collection('inventory')
            ->document($parentId)
            ->snapshot();

        if (!$parentItem->exists()) {
            return redirect()->route('rhu.inventory.index')->with('error', 'Item not found.');
        }

        $parentData = array_merge(['id' => $parentItem->id()], $parentItem->data());

        $batchDocs = $this->firestore
            ->collection($user['role'])
            ->document($user['id'])
            ->collection('inventory')
            ->document($parentId)
            ->collection('batches')
            ->documents();

        $allReleases = [];

        foreach ($batchDocs as $batchDoc) {
            if ($batchDoc->exists()) {
                $batchData = $batchDoc->data();

                $distributionDocs = $this->firestore
                    ->collection($user['role'])
                    ->document($user['id'])
                    ->collection('inventory')
                    ->document($parentId)
                    ->collection('batches')
                    ->document($batchDoc->id())
                    ->collection('distributions')
                    ->documents();

                foreach ($distributionDocs as $distributionDoc) {
                    if ($distributionDoc->exists()) {
                        $distributionData = $distributionDoc->data();

                        $allReleases[] = [
                            'id' => $distributionDoc->id(),
                            'lot_number' => $batchData['lot_number'] ?? 'Unknown',
                            'batch_id' => $batchDoc->id(),
                            'resident_name' => $distributionData['resident_name'] ?? 'Unknown',
                            'resident_id' => $distributionData['resident_id'] ?? null,
                            'quantity_released' => $distributionData['quantity_released'] ?? 0,
                            'release_date' => $distributionData['release_date'] ?? $distributionData['distribution_date'] ?? '',
                            'reason' => $distributionData['reason'] ?? '',
                            'released_at' => $distributionData['released_at'] ?? $distributionData['distributed_at'] ?? '',
                            'released_by' => $distributionData['released_by'] ?? $distributionData['distributed_by'] ?? 'Unknown',
                            'batch_expiration' => $batchData['expiration_date'] ?? ''
                        ];
                    }
                }
            }
        }

        usort($allReleases, function ($a, $b) {
            $dateA = $a['released_at'] ?: $a['release_date'];
            $dateB = $b['released_at'] ?: $b['release_date'];
            return strtotime($dateB) - strtotime($dateA);
        });

        $totalReleased = array_sum(array_column($allReleases, 'quantity_released'));
        $totalRecipients = count(array_unique(array_column($allReleases, 'resident_name')));
        $releaseCount = count($allReleases);

        return $this->view('inventory.release-history', compact('parentData', 'allReleases', 'totalReleased', 'totalRecipients', 'releaseCount'));
    }

    public function store(Request $request)
    {
        $user = session('user');
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to access inventory management.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'quantity' => 'required|integer|min:0',
            'unit_type' => 'required|in:capsules,tablets,pieces,boxes,packs,bottles,vials,sachets',
            'description' => 'nullable|string|max:500',
            'generic_name' => 'nullable|string|max:255',
            'generic_description' => 'nullable|string|max:500',
            'milligrams' => 'nullable|numeric|min:0',
            'therapeutic_classification' => 'nullable|string|max:255',
            'initial_lot_number' => 'nullable|string|max:255',
            'initial_expiration_date' => 'nullable|date|after_or_equal:today',
            'initial_batch_notes' => 'nullable|string|max:500',
        ]);

        $isBatchTrackedType = in_array($request->type, ['Medicine', 'Vaccine']);

        if ($isBatchTrackedType && empty(trim($request->generic_name ?? ''))) {
            return redirect()->back()->withErrors(['generic_name' => 'Generic name is required for medicines and vaccines.'])->withInput();
        }
        if ($isBatchTrackedType && ($request->milligrams === null || $request->milligrams === '')) {
            return redirect()->back()->withErrors(['milligrams' => 'Dosage (mg) is required for medicines and vaccines.'])->withInput();
        }
        if ($isBatchTrackedType && empty(trim($request->initial_lot_number ?? ''))) {
            return redirect()->back()->withErrors(['initial_lot_number' => 'Initial lot number is required for medicines and vaccines.'])->withInput();
        }
        if ($isBatchTrackedType && empty($request->initial_expiration_date)) {
            return redirect()->back()->withErrors(['initial_expiration_date' => 'Initial expiration date is required for medicines and vaccines.'])->withInput();
        }

        $status = $this->calculateStatus($request->quantity, $request->unit_type);

        $parentRef = $this->firestore
            ->collection($user['role'])
            ->document($user['id'])
            ->collection('inventory')
            ->add([
                'name' => $request->name,
                'type' => $request->type,
                'quantity' => $request->quantity,
                'unit_type' => $request->unit_type,
                'status' => $status,
                'description' => $request->description,
                'generic_name' => $isBatchTrackedType ? trim($request->generic_name) : null,
                'generic_description' => $isBatchTrackedType ? ($request->generic_description ?? '') : null,
                'milligrams' => $isBatchTrackedType ? (float)$request->milligrams : null,
                'therapeutic_classification' => $isBatchTrackedType ? ($request->therapeutic_classification ?? null) : null,
                'created_at' => now()->toISOString(),
                'updated_at' => now()->toISOString(),
            ]);

        if ($isBatchTrackedType) {
            $this->firestore
                ->collection($user['role'])
                ->document($user['id'])
                ->collection('inventory')
                ->document($parentRef->id())
                ->collection('batches')
                ->add([
                    'lot_number' => trim($request->initial_lot_number),
                    'quantity' => (int) $request->quantity,
                    'expiration_date' => $request->initial_expiration_date,
                    'notes' => $request->initial_batch_notes,
                    'created_at' => now()->toISOString(),
                    'updated_at' => now()->toISOString(),
                ]);
        }

        return redirect()->back()->with('success', 'Item added successfully!');
    }

    public function storeBatch(Request $request)
    {
        $user = session('user');
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to access inventory management.');
        }

        $request->validate([
            'parent_medicine_id' => 'required|string',
            'lot_number' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'expiration_date' => 'required|date|after:today',
            'notes' => 'nullable|string|max:500',
        ]);

        $parentId = $request->parent_medicine_id;

        $batchData = [
            'lot_number' => $request->lot_number,
            'quantity' => $request->quantity,
            'expiration_date' => $request->expiration_date,
            'notes' => $request->notes,
            'created_at' => now()->toISOString(),
            'updated_at' => now()->toISOString(),
        ];

        $this->firestore
            ->collection($user['role'])
            ->document($user['id'])
            ->collection('inventory')
            ->document($parentId)
            ->collection('batches')
            ->add($batchData);

        $this->updateParentQuantity($parentId);

        return redirect()->route('rhu.inventory.show', $parentId)->with('success', 'Batch added successfully!');
    }

    public function update(Request $request, $id)
    {
        $user = session('user');
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to access inventory management.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'quantity' => 'required|integer|min:0',
            'unit_type' => 'required|in:capsules,tablets,pieces,boxes,packs,bottles,vials,sachets',
            'description' => 'nullable|string|max:500',
            'generic_name' => 'nullable|string|max:255',
            'generic_description' => 'nullable|string|max:500',
            'milligrams' => 'nullable|numeric|min:0',
            'therapeutic_classification' => 'nullable|string|max:255',
        ]);

        if (in_array($request->type, ['Medicine', 'Vaccine']) && empty(trim($request->generic_name ?? ''))) {
            return redirect()->back()->withErrors(['generic_name' => 'Generic name is required for medicines and vaccines.'])->withInput();
        }
        if (in_array($request->type, ['Medicine', 'Vaccine']) && ($request->milligrams === null || $request->milligrams === '')) {
            return redirect()->back()->withErrors(['milligrams' => 'Dosage (mg) is required for medicines and vaccines.'])->withInput();
        }

        $status = $this->calculateStatus($request->quantity, $request->unit_type);

        $this->firestore
            ->collection($user['role'])
            ->document($user['id'])
            ->collection('inventory')
            ->document($id)
            ->set([
                'name' => $request->name,
                'type' => $request->type,
                'quantity' => $request->quantity,
                'unit_type' => $request->unit_type,
                'status' => $status,
                'description' => $request->description,
                'generic_name' => in_array($request->type, ['Medicine', 'Vaccine']) ? trim($request->generic_name) : null,
                'generic_description' => in_array($request->type, ['Medicine', 'Vaccine']) ? ($request->generic_description ?? '') : null,
                'milligrams' => in_array($request->type, ['Medicine', 'Vaccine']) ? (float)$request->milligrams : null,
                'therapeutic_classification' => in_array($request->type, ['Medicine', 'Vaccine']) ? ($request->therapeutic_classification ?? null) : null,
                'updated_at' => now()->toISOString(),
            ]);

        return redirect()->back()->with('success', 'Item updated successfully!');
    }

    public function distributeBatch(Request $request, $parentId, $batchId)
    {
        $user = session('user');
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to access inventory management.');
        }

        $request->validate([
            'resident_name' => 'required|string|max:255',
            'quantity_to_distribute' => 'required|integer|min:1',
            'distribution_date' => 'required|date',
            'reason' => 'nullable|string|max:500',
        ]);

        $batchDoc = $this->firestore
            ->collection($user['role'])
            ->document($user['id'])
            ->collection('inventory')
            ->document($parentId)
            ->collection('batches')
            ->document($batchId)
            ->snapshot();

        if (!$batchDoc->exists()) {
            return redirect()->back()->with('error', 'Batch not found.');
        }

        $batchData = $batchDoc->data();
        $currentQuantity = $batchData['quantity'] ?? 0;
        $requestedQuantity = $request->quantity_to_distribute;

        if ($currentQuantity < $requestedQuantity) {
            return redirect()->back()->with('error', "Insufficient quantity. Available: {$currentQuantity}, Requested: {$requestedQuantity}");
        }

        $newQuantity = $currentQuantity - $requestedQuantity;

        $this->firestore
            ->collection($user['role'])
            ->document($user['id'])
            ->collection('inventory')
            ->document($parentId)
            ->collection('batches')
            ->document($batchId)
            ->update([
                ['path' => 'quantity', 'value' => $newQuantity],
                ['path' => 'updated_at', 'value' => now()->toISOString()],
            ]);

        $distributionData = [
            'resident_name' => $request->resident_name,
            'quantity_distributed' => $requestedQuantity,
            'distribution_date' => $request->distribution_date,
            'reason' => $request->reason,
            'distributed_at' => now()->toISOString(),
            'distributed_by' => session('user.name', 'Health Worker'),
        ];

        $this->firestore
            ->collection($user['role'])
            ->document($user['id'])
            ->collection('inventory')
            ->document($parentId)
            ->collection('batches')
            ->document($batchId)
            ->collection('distributions')
            ->add($distributionData);

        $this->updateParentQuantity($parentId);

        return redirect()->back()->with('success', "Successfully distributed {$requestedQuantity} units to {$request->resident_name}!");
    }

    public function destroy(\Illuminate\Http\Request $request, $id)
    {
        $user = session('user');
        if (!$user) return redirect()->route('login')->with('error', 'Please login to access inventory management.');

        $doc = $this->firestore
            ->collection($user['role'])
            ->document($user['id'])
            ->collection('inventory')
            ->document($id)
            ->snapshot();

        if (!$doc->exists()) {
            return redirect()->back()->with('error', 'Item not found.');
        }

        $data = $doc->data();
        $expected  = strtolower(trim($data['name'] ?? ''));
        $confirmed = strtolower(trim($request->input('confirm_title', '')));

        if ($confirmed !== $expected) {
            return redirect()->back()->with('error', 'Item name did not match. Deletion cancelled.');
        }

        $doc->reference()->delete();

        return redirect()->back()->with('success', 'Item deleted successfully!');
    }

    public function destroyBatch($parentId, $batchId)
    {
        $user = session('user');
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to access inventory management.');
        }

        $this->firestore
            ->collection($user['role'])
            ->document($user['id'])
            ->collection('inventory')
            ->document($parentId)
            ->collection('batches')
            ->document($batchId)
            ->delete();

        $this->updateParentQuantity($parentId);

        return redirect()->back()->with('success', 'Batch deleted successfully!');
    }

    public function updateBatch(Request $request, $parentId, $batchId)
    {
        $user = session('user');
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to access inventory management.');
        }

        $request->validate([
            'lot_number' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'expiration_date' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ]);

        $batchDoc = $this->firestore
            ->collection($user['role'])
            ->document($user['id'])
            ->collection('inventory')
            ->document($parentId)
            ->collection('batches')
            ->document($batchId)
            ->snapshot();

        if (!$batchDoc->exists()) {
            return redirect()->back()->with('error', 'Batch not found.');
        }

        $this->firestore
            ->collection($user['role'])
            ->document($user['id'])
            ->collection('inventory')
            ->document($parentId)
            ->collection('batches')
            ->document($batchId)
            ->update([
                ['path' => 'lot_number', 'value' => $request->lot_number],
                ['path' => 'quantity', 'value' => $request->quantity],
                ['path' => 'expiration_date', 'value' => $request->expiration_date],
                ['path' => 'notes', 'value' => $request->notes],
                ['path' => 'updated_at', 'value' => now()->toISOString()],
            ]);

        $this->updateParentQuantity($parentId);

        return redirect()->back()->with('success', 'Batch updated successfully!');
    }

    // FIFO release: deducts from earliest-expiring batches first
    public function releaseMedicine(Request $request, $parentId)
    {
        $user = session('user');
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to access inventory management.');
        }

        $request->validate([
            'resident_name' => 'required|string|max:255',
            'resident_id' => 'nullable|string',
            'quantity_to_release' => 'required|integer|min:1',
            'release_date' => 'required|date',
            'reason' => 'nullable|string|max:500',
            'released_by' => 'required|string|max:255',
            'personnel_id' => 'nullable|string',
        ]);

        $residentId = $request->resident_id;
        if (empty($residentId)) {
            $residentId = $this->registerNewResident($request->resident_name, $user);
        }

        $batches = $this->firestore
            ->collection($user['role'])
            ->document($user['id'])
            ->collection('inventory')
            ->document($parentId)
            ->collection('batches')
            ->documents();

        $batchData = [];
        foreach ($batches as $batch) {
            if ($batch->exists()) {
                $data = $batch->data();
                $batchData[] = array_merge(['id' => $batch->id()], $data);
            }
        }

        usort($batchData, function ($a, $b) {
            return strtotime($a['expiration_date']) - strtotime($b['expiration_date']);
        });

        $requestedQuantity = $request->quantity_to_release;
        $remainingQuantity = $requestedQuantity;
        $releasedFromBatches = [];
        $totalAvailable = 0;

        foreach ($batchData as $batch) {
            $totalAvailable += $batch['quantity'] ?? 0;
        }

        if ($totalAvailable < $requestedQuantity) {
            return redirect()->back()->with('error', "Insufficient quantity. Available: {$totalAvailable}, Requested: {$requestedQuantity}");
        }

        foreach ($batchData as $batch) {
            if ($remainingQuantity <= 0) break;

            $batchQuantity = $batch['quantity'] ?? 0;
            if ($batchQuantity <= 0) continue;

            $quantityToTake = min($remainingQuantity, $batchQuantity);
            $newQuantity = $batchQuantity - $quantityToTake;

            $this->firestore
                ->collection($user['role'])
                ->document($user['id'])
                ->collection('inventory')
                ->document($parentId)
                ->collection('batches')
                ->document($batch['id'])
                ->update([
                    ['path' => 'quantity', 'value' => $newQuantity],
                    ['path' => 'updated_at', 'value' => now()->toISOString()],
                ]);

            $releaseData = [
                'resident_name' => $request->resident_name,
                'resident_id' => $residentId,
                'quantity_released' => $quantityToTake,
                'release_date' => $request->release_date,
                'reason' => $request->reason,
                'released_at' => now()->toISOString(),
                'released_by' => $request->released_by ?? session('user.name', 'Health Worker'),
                'personnel_id' => $request->personnel_id ?? null,
            ];

            $this->firestore
                ->collection($user['role'])
                ->document($user['id'])
                ->collection('inventory')
                ->document($parentId)
                ->collection('batches')
                ->document($batch['id'])
                ->collection('distributions')
                ->add($releaseData);

            $releasedFromBatches[] = [
                'lot_number' => $batch['lot_number'] ?? 'Unknown',
                'quantity' => $quantityToTake
            ];

            $remainingQuantity -= $quantityToTake;
        }

        $this->updateParentQuantity($parentId);

        $batchDetails = collect($releasedFromBatches)->map(function ($batch) {
            $lotNumber = $batch['lot_number'] ?? 'Unknown';
            return "{$batch['quantity']} from Lot No {$lotNumber}";
        })->join(', ');

        return redirect()->back()->with('success', "Successfully released {$requestedQuantity} units to {$request->resident_name}! (Released: {$batchDetails})");
    }

    private function updateParentQuantity($parentId)
    {
        $user = session('user');
        if (!$user) {
            return;
        }

        $batches = $this->firestore
            ->collection($user['role'])
            ->document($user['id'])
            ->collection('inventory')
            ->document($parentId)
            ->collection('batches')
            ->documents();

        $totalQuantity = 0;
        foreach ($batches as $batch) {
            if ($batch->exists()) {
                $totalQuantity += $batch->data()['quantity'] ?? 0;
            }
        }

        $parentItem = $this->firestore
            ->collection($user['role'])
            ->document($user['id'])
            ->collection('inventory')
            ->document($parentId)
            ->snapshot();

        if ($parentItem->exists()) {
            $parentData = $parentItem->data();
            $unitType = $parentData['unit_type'] ?? 'pieces';
            $status = $this->calculateStatus($totalQuantity, $unitType);

            $this->firestore
                ->collection($user['role'])
                ->document($user['id'])
                ->collection('inventory')
                ->document($parentId)
                ->update([
                    ['path' => 'quantity', 'value' => $totalQuantity],
                    ['path' => 'status', 'value' => $status],
                    ['path' => 'updated_at', 'value' => now()->toISOString()],
                ]);
        }
    }

    private function calculateStatus($quantity, $unitType)
    {
        $thresholds = [
            'capsules' => ['low_stock' => 50, 'out_of_stock' => 0],
            'tablets' => ['low_stock' => 50, 'out_of_stock' => 0],
            'pieces' => ['low_stock' => 10, 'out_of_stock' => 0],
            'boxes' => ['low_stock' => 2, 'out_of_stock' => 0],
            'packs' => ['low_stock' => 5, 'out_of_stock' => 0],
            'bottles' => ['low_stock' => 5, 'out_of_stock' => 0],
            'vials' => ['low_stock' => 10, 'out_of_stock' => 0],
            'sachets' => ['low_stock' => 10, 'out_of_stock' => 0],
        ];

        $threshold = $thresholds[$unitType] ?? $thresholds['pieces'];

        if ($quantity <= $threshold['out_of_stock']) {
            return 'out_of_stock';
        } elseif ($quantity <= $threshold['low_stock']) {
            return 'low_stock';
        } else {
            return 'available';
        }
    }

    public function storeResident(Request $request)
    {
        $user = session('user');
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:120',
            'last_name' => 'required|string|max:120',
            'email' => 'required|email',
            'purok' => 'required|string|max:150',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.confirmed' => 'The password confirmation does not match.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        $firstName = trim($request->first_name);
        $lastName = trim($request->last_name);
        $fullName = trim($firstName . ' ' . $lastName);
        $email = strtolower($request->email);
        $purok = trim($request->purok);
        $barangayName = $user['name'] ?? 'Barangay Health Center';
        $barangayId = $user['barangayId'] ?? $user['id'];
        $createdAt = now()->toIso8601String();
        $username = Str::slug($fullName, '');
        if (empty($username)) {
            $username = 'user' . substr(md5($email . microtime()), 0, 6);
        }

        $fullAddress = "Purok {$purok}, {$barangayName}";
        $uid = null;

        try {
            $authUser = $this->auth->createUser([
                'email' => $email,
                'password' => $request->password,
                'displayName' => $fullName,
                'emailVerified' => false,
            ]);

            $uid = $authUser->uid;

            $userDocument = [
                'uid' => $uid,
                'fullname' => $fullName,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'purok' => $purok,
                'fulladdress' => $fullAddress,
                'barangay' => $barangayName,
                'barangayId' => $barangayId,
                'role' => 'user',
                'status' => 'approved',
                'matchedBarangayId' => $barangayId,
                'accountSource' => 'medicine_release',
                'createdAt' => $createdAt,
                'createdBy' => $user['id'],
            ];

            $this->firestore
                ->collection('users')
                ->document($uid)
                ->set($userDocument);

            $userRequestDocument = [
                'id' => $uid,
                'uid' => $uid,
                'name' => $fullName,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'username' => $username,
                'purok' => $purok,
                'location' => $fullAddress,
                'status' => 'approved',
                'role' => 'user',
                'registered_via' => 'medicine_release',
                'registered_by' => $user['id'],
                'created_at' => $createdAt,
                'approved_at' => $createdAt,
            ];

            $this->firestore
                ->collection($user['role'])
                ->document($user['id'])
                ->collection('userRequests')
                ->document($uid)
                ->set($userRequestDocument);

            return response()->json([
                'message' => 'Resident registered successfully.',
                'id' => $uid,
                'name' => $fullName,
                'email' => $email,
                'username' => $username,
                'location' => $fullAddress,
            ], 201);
        } catch (AuthException $e) {
            $message = 'Failed to create account.';
            if (str_contains($e->getMessage(), 'EMAIL_EXISTS')) {
                $message = 'The email address is already registered.';
                $status = 422;
            } else {
                $status = 500;
            }

            return response()->json(['message' => $message], $status);
        } catch (FirebaseException $e) {
            if ($uid) {
                try {
                    $this->auth->deleteUser($uid);
                } catch (\Throwable $cleanupException) {
                    // cleanup failed, ignore
                }
            }

            return response()->json(['message' => 'Failed to save resident record. Please try again.'], 500);
        } catch (\Throwable $e) {
            if ($uid) {
                try {
                    $this->auth->deleteUser($uid);
                } catch (\Throwable $cleanupException) {
                    // cleanup failed, ignore
                }
            }

            return response()->json(['message' => 'Failed to create resident. Please try again.'], 500);
        }
    }

    public function searchResidents(Request $request)
    {
        $user = session('user');
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $searchTerm = $request->get('q', '');
        $limit = strlen($searchTerm) >= 2 ? 10 : 50;

        try {
            $userRequestsQuery = $this->firestore
                ->collection($user['role'])
                ->document($user['id'])
                ->collection('userRequests')
                ->where('status', '=', 'approved')
                ->limit($limit)
                ->documents();

            $residents = [];
            foreach ($userRequestsQuery as $doc) {
                if ($doc->exists()) {
                    $data = $doc->data();
                    $email = $data['email'] ?? '';
                    $username = $data['username'] ?? '';
                    $address = $data['address'] ?? '';
                    $contactNumber = $data['contact_number'] ?? '';
                    $location = $data['location'] ?? '';

                    $displayName = $username;
                    if (empty($displayName) && !empty($email)) {
                        $displayName = strstr($email, '@', true);
                    }
                    if (empty($displayName)) {
                        $displayName = 'Unknown User';
                    }

                    if (strlen($searchTerm) >= 2) {
                        $searchFields = strtolower($username . ' ' . $email . ' ' . $address . ' ' . $location . ' ' . $contactNumber . ' ' . $displayName);

                        if (!str_contains($searchFields, strtolower($searchTerm))) {
                            continue;
                        }
                    }

                    $residents[] = [
                        'id' => $doc->id(),
                        'name' => $displayName,
                        'email' => $email,
                        'username' => $username,
                        'address' => $address,
                        'contact_number' => $contactNumber,
                        'location' => $location,
                        'display_text' => $displayName . (!empty($email) ? ' (' . $email . ')' : (!empty($username) ? ' (@' . $username . ')' : ''))
                    ];
                }
            }

            return response()->json($residents);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Search failed'], 500);
        }
    }

    public function searchPersonnel(Request $request)
    {
        $user = session('user');
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $searchTerm = $request->get('q', '');
        $limit = strlen($searchTerm) >= 2 ? 10 : 50;

        try {
            $personnelQuery = $this->firestore
                ->collection($user['role'])
                ->document($user['id'])
                ->collection('personnel')
                ->limit($limit)
                ->documents();

            $personnel = [];
            foreach ($personnelQuery as $doc) {
                if ($doc->exists()) {
                    $data = $doc->data();
                    $name = $data['name'] ?? '';
                    $position = $data['position'] ?? '';

                    if (strlen($searchTerm) >= 2) {
                        $searchFields = strtolower($name . ' ' . $position);

                        if (!str_contains($searchFields, strtolower($searchTerm))) {
                            continue;
                        }
                    }

                    $personnel[] = [
                        'id' => $doc->id(),
                        'name' => $name,
                        'position' => $position,
                    ];
                }
            }

            return response()->json($personnel);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Search failed'], 500);
        }
    }

    private function registerNewResident($residentName, $user)
    {
        try {
            $username = strtolower(str_replace(' ', '', $residentName));

            $residentData = [
                'username' => $username,
                'status' => 'approved', // auto-approve residents registered during medicine release
                'created_at' => now()->toISOString(),
                'registered_during_medicine_release' => true,
                'registered_by' => $user['id'],
                'registered_by_name' => $user['name'] ?? 'Health Worker',
            ];

            $docRef = $this->firestore
                ->collection($user['role'])
                ->document($user['id'])
                ->collection('userRequests')
                ->add($residentData);

            return $docRef->id();
        } catch (\Exception $e) {
            return 'temp_' . time();
        }
    }
}
