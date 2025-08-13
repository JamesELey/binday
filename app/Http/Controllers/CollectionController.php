<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CollectionController extends Controller
{
    /**
     * Display a listing of all collections
     */
    public function index()
    {
        $collections = $this->getAllCollections();
        return view('collections.index', compact('collections'));
    }

    /**
     * Get all collections from storage
     */
    private function getAllCollections(): array
    {
        $storagePath = storage_path('app/collections.json');
        
        if (!file_exists($storagePath)) {
            return [];
        }
        
        $data = file_get_contents($storagePath);
        return json_decode($data, true) ?: [];
    }

    /**
     * Save collections to storage
     */
    private function saveCollections(array $collections): void
    {
        $storagePath = storage_path('app/collections.json');
        file_put_contents($storagePath, json_encode($collections, JSON_PRETTY_PRINT));
    }

    /**
     * Get next available ID
     */
    private function getNextCollectionId(): int
    {
        $collections = $this->getAllCollections();
        $maxId = 0;
        foreach ($collections as $collection) {
            if ($collection['id'] > $maxId) {
                $maxId = $collection['id'];
            }
        }
        return $maxId + 1;
    }

    /**
     * Show the form for creating a new collection
     */
    public function create()
    {
        // Get all areas to show available bin types
        $areas = $this->getAllowedAreas();
        
        // Get all possible bin types from all areas
        $allBinTypes = [];
        foreach ($areas as $area) {
            if (!empty($area['bin_types'])) {
                $allBinTypes = array_merge($allBinTypes, $area['bin_types']);
            }
        }
        $allBinTypes = array_unique($allBinTypes);
        
        // If no area-specific bin types, use defaults
        if (empty($allBinTypes)) {
            $allBinTypes = \App\Http\Controllers\BinScheduleController::getDefaultBinTypes();
        }
        
        return view('collections.create', compact('allBinTypes', 'areas'));
    }

    /**
     * Store a newly created collection
     */
    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'bin_type' => 'required|string',
            'collection_date' => 'required|date|after_or_equal:today',
        ]);

        // Check if address is within allowed areas
        $address = $request->input('address');
        $postcode = $this->extractPostcode($address);
        
        if (!$this->isInAllowedArea($postcode)) {
            return back()->withInput()->with('area_error', 
                'Sorry, we don\'t currently provide services to this area. Please contact us at enquiries@thebinday.co.uk for more information about coverage in your area.'
            );
        }

        // Save the collection
        $collections = $this->getAllCollections();
        $newCollection = [
            'id' => $this->getNextCollectionId(),
            'customer_name' => $request->input('customer_name'),
            'phone' => $request->input('phone'),
            'address' => $request->input('address'),
            'bin_type' => $request->input('bin_type'),
            'collection_date' => $request->input('collection_date'),
            'collection_time' => $request->input('collection_time', '08:00'),
            'status' => $request->input('status', 'Scheduled'),
            'notes' => $request->input('notes', ''),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $collections[] = $newCollection;
        $this->saveCollections($collections);
        
        return redirect()->route('collections.index')
            ->with('success', 'Collection booked successfully!');
    }

    /**
     * Extract postcode from address string
     */
    private function extractPostcode($address)
    {
        // Simple postcode extraction - looks for UK postcode pattern at end of address
        if (preg_match('/([A-Z]{1,2}[0-9R][0-9A-Z]?\s*[0-9][A-Z]{2})\s*$/i', $address, $matches)) {
            return strtoupper($matches[1]);
        }
        
        // Fallback: try to extract postcode area from anywhere in address
        if (preg_match('/([A-Z]{1,2}[0-9R][0-9A-Z]?)/i', $address, $matches)) {
            return strtoupper($matches[1]);
        }
        
        return '';
    }

    /**
     * Check if postcode is in allowed areas using coordinate-based geofencing
     */
    private function isInAllowedArea($postcode)
    {
        if (empty($postcode)) {
            return false;
        }

        // Use the AllowedAreaController's coordinate-based validation
        $areaController = new \App\Http\Controllers\AllowedAreaController();
        return $areaController->checkPostcode($postcode);
    }

    /**
     * Show the management page with all collections for editing
     */
    public function manage()
    {
        $collections = $this->getAllCollections();
        return view('collections.manage', compact('collections'));
    }

    /**
     * Show the form for editing a collection
     */
    public function edit($id)
    {
        // Sample collection data
        $collection = [
            'id' => $id,
            'address' => '123 Main Street',
            'bin_type' => 'Residual Waste',
            'collection_date' => '2025-01-20',
            'collection_time' => '08:00',
            'status' => 'Scheduled',
            'customer_name' => 'John Smith',
            'phone' => '07123456789',
            'notes' => 'Leave bin at front gate'
        ];

        return view('collections.edit', compact('collection'));
    }

    /**
     * Update the specified collection
     */
    public function update(Request $request, $id)
    {
        // In a real application, you would validate and update in database
        // For now, just redirect back with success message
        return redirect()->route('collections.manage')
            ->with('success', 'Collection updated successfully!');
    }

    /**
     * Remove the specified collection
     */
    public function destroy($id)
    {
        // In a real application, you would delete from database
        // For now, just redirect back with success message
        return redirect()->route('collections.manage')
            ->with('success', 'Collection deleted successfully!');
    }
}
