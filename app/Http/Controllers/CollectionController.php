<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Collection;
use App\Area;

class CollectionController extends Controller
{
    /**
     * Display a listing of all collections
     */
    public function index()
    {
        $collections = Collection::with(['area', 'user'])->orderBy('collection_date', 'asc')->get();
        return view('collections.index', compact('collections'));
    }

    /**
     * Get all collections from database (legacy method - kept for backward compatibility)
     */
    private function getAllCollections(): array
    {
        // This method is kept for backward compatibility but now uses database
        return Collection::all()->toArray();
    }

    /**
     * Show the form for creating a new collection
     */
    public function create()
    {
        // Get all active areas to show available bin types
        $areas = Area::active()->get();
        
        // Get all possible bin types from all areas
        $allBinTypes = [];
        foreach ($areas as $area) {
            if (!empty($area->bin_types)) {
                $allBinTypes = array_merge($allBinTypes, $area->bin_types);
            }
        }
        $allBinTypes = array_unique($allBinTypes);
        
        // If no area-specific bin types, use defaults
        if (empty($allBinTypes)) {
            $allBinTypes = Area::getDefaultBinTypes();
        }
        
        return view('collections.create', compact('allBinTypes', 'areas'));
    }

    /**
     * Store a newly created collection
     */
    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'required|string|max:500',
            'bin_type' => 'required|string|max:50',
            'collection_date' => 'required|date|after_or_equal:today',
            'collection_time' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Check if address is within allowed areas
        $address = $validated['address'];
        $area = Area::active()->get()->first(function ($area) use ($address) {
            return $area->containsAddress($address);
        });
        
        if (!$area) {
            return back()->withInput()->with('area_error', 
                'Sorry, we don\'t currently provide services to this area. Please contact us at enquiries@thebinday.co.uk for more information about coverage in your area.'
            );
        }

        // Create the collection
        $collection = Collection::create(array_merge($validated, [
            'status' => Collection::STATUS_PENDING,
            'user_id' => auth()->id(),
            'area_id' => $area->id,
        ]));
        
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
        $user = auth()->user();
        
        // Get collections based on user role
        if ($user->isAdmin()) {
            $collections = Collection::with(['area', 'user'])->orderBy('collection_date', 'asc')->get();
        } elseif ($user->isWorker()) {
            // Workers can only see collections in their assigned areas
            $areaIds = $user->getManageableAreaIds();
            $collections = Collection::with(['area', 'user'])
                ->whereIn('area_id', $areaIds)
                ->orderBy('collection_date', 'asc')
                ->get();
        } else {
            // Customers can only see their own collections
            $collections = Collection::with(['area'])
                ->where('customer_email', $user->email)
                ->orderBy('collection_date', 'asc')
                ->get();
        }
        
        return view('collections.manage', compact('collections'));
    }

    /**
     * Show the form for editing a collection
     */
    public function edit($id)
    {
        $collection = Collection::with(['area', 'user'])->findOrFail($id);
        
        // Check if user can edit this collection
        $user = auth()->user();
        if (!$collection->canBeEditedBy($user)) {
            return redirect()->route('collections.manage')
                ->with('error', 'You do not have permission to edit this collection.');
        }
        
        // Get available bin types from the collection's area
        $binTypes = $collection->area ? $collection->area->bin_types : Area::getDefaultBinTypes();

        return view('collections.edit', compact('collection', 'binTypes'));
    }

    /**
     * Update the specified collection
     */
    public function update(Request $request, $id)
    {
        $collection = Collection::findOrFail($id);
        
        // Check if user can edit this collection
        $user = auth()->user();
        if (!$collection->canBeEditedBy($user)) {
            return redirect()->route('collections.manage')
                ->with('error', 'You do not have permission to edit this collection.');
        }
        
        // Validate the request
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'required|string|max:500',
            'bin_type' => 'required|string|max:50',
            'collection_date' => 'required|date',
            'collection_time' => 'nullable|date_format:H:i',
            'status' => 'required|in:pending,confirmed,collected,cancelled',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Update the collection
        $collection->update($validated);
        
        return redirect()->route('collections.manage')
            ->with('success', 'Collection updated successfully!');
    }

    /**
     * Remove the specified collection
     */
    public function destroy($id)
    {
        $collection = Collection::findOrFail($id);
        
        // Check if user can edit this collection
        $user = auth()->user();
        if (!$collection->canBeEditedBy($user)) {
            return redirect()->route('collections.manage')
                ->with('error', 'You do not have permission to delete this collection.');
        }

        $collection->delete();
        
        return redirect()->route('collections.manage')
            ->with('success', 'Collection deleted successfully!');
    }

    /**
     * Get all allowed areas for bin type determination (legacy method)
     */
    private function getAllowedAreas(): array
    {
        // This method is kept for backward compatibility but now uses database
        return Area::active()->get()->toArray();
    }
}
