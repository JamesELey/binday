<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BinScheduleController extends Controller
{
    /**
     * Display the main index page
     */
    public function index()
    {
        return view('bins.index');
    }

    /**
     * Show the map page
     */
    public function map()
    {
        return view('bins.map');
    }

    /**
     * Show the map page with date filtering
     */
    public function mapByDate()
    {
        return view('bins.map-date');
    }

    /**
     * API endpoint to get all bins data
     */
    public function apiAll(): JsonResponse
    {
        // Load collections from JSON file
        $collections = $this->getCollections();
        $bins = [];
        
        foreach ($collections as $collection) {
            // Get coordinates for the address
            $coordinates = $this->getCoordinatesForAddress($collection['address']);
            
            // Determine color based on bin type
            $color = $this->getBinTypeColor($collection['bin_type']);
            
            $bins[] = [
                'id' => $collection['id'],
                'customer_name' => $collection['customer_name'],
                'phone' => $collection['phone'],
                'address' => $collection['address'],
                'bin_type' => $collection['bin_type'],
                'collection_date' => $collection['collection_date'],
                'collection_time' => $collection['collection_time'],
                'status' => $collection['status'],
                'notes' => $collection['notes'] ?? '',
                'latitude' => $coordinates['lat'],
                'longitude' => $coordinates['lng'],
                'color' => $color
            ];
        }

        return response()->json($bins);
    }

    /**
     * Get collections from JSON file
     */
    private function getCollections(): array
    {
        $storagePath = storage_path('app/collections.json');
        
        if (!file_exists($storagePath)) {
            return [];
        }
        
        $data = file_get_contents($storagePath);
        return json_decode($data, true) ?: [];
    }

    /**
     * Get coordinates for an address (with fallback to Eccleshall center)
     */
    private function getCoordinatesForAddress(string $address): array
    {
        // If it's an Eccleshall address, use coordinates within the polygon
        if (stripos($address, 'Eccleshall') !== false) {
            return $this->getEccleshallCoordinates($address);
        }
        
        // Fallback to London center for other addresses
        return [
            'lat' => 51.5074,
            'lng' => -0.1278
        ];
    }

    /**
     * Get coordinates for Eccleshall addresses based on street names
     */
    private function getEccleshallCoordinates(string $address): array
    {
        // Extract street name and map to approximate coordinates within Eccleshall
        $streetCoordinates = [
            'High Street' => ['lat' => 52.8595, 'lng' => -2.2530],
            'Castle Street' => ['lat' => 52.8590, 'lng' => -2.2535],
            'Stafford Street' => ['lat' => 52.8585, 'lng' => -2.2540],
            'Newport Road' => ['lat' => 52.8580, 'lng' => -2.2525],
            'Stone Road' => ['lat' => 52.8575, 'lng' => -2.2520],
            'Buchanan Avenue' => ['lat' => 52.8570, 'lng' => -2.2515],
            'Badgers Croft' => ['lat' => 52.8565, 'lng' => -2.2510],
            'Crooked Bridge Road' => ['lat' => 52.8560, 'lng' => -2.2505],
            'Millfield Gardens' => ['lat' => 52.8555, 'lng' => -2.2500],
            'Pinfold Lane' => ['lat' => 52.8550, 'lng' => -2.2495],
            'Sheepmarket' => ['lat' => 52.8545, 'lng' => -2.2490],
            'The Limes' => ['lat' => 52.8540, 'lng' => -2.2485],
            'Violets Way' => ['lat' => 52.8535, 'lng' => -2.2480],
            'Weavers Close' => ['lat' => 52.8530, 'lng' => -2.2475],
            'Woods Lane' => ['lat' => 52.8525, 'lng' => -2.2470],
            'Bishop\'s Court' => ['lat' => 52.8520, 'lng' => -2.2465],
            'Ferndale Close' => ['lat' => 52.8515, 'lng' => -2.2460],
            'Guildhall Lane' => ['lat' => 52.8510, 'lng' => -2.2455],
            'Offley Brook' => ['lat' => 52.8505, 'lng' => -2.2450],
            'School Lane' => ['lat' => 52.8500, 'lng' => -2.2445]
        ];
        
        // Find matching street
        foreach ($streetCoordinates as $street => $coords) {
            if (stripos($address, $street) !== false) {
                // Add small random offset to make addresses on same street appear slightly different
                $coords['lat'] += (rand(-20, 20) / 100000); // ±0.0002 degrees
                $coords['lng'] += (rand(-20, 20) / 100000);
                return $coords;
            }
        }
        
        // Default to Eccleshall center if street not found
        return ['lat' => 52.8586, 'lng' => -2.2524];
    }

    /**
     * Get color based on bin type
     */
    private function getBinTypeColor(string $binType): string
    {
        $colors = [
            'Food' => '#28a745',                // Green
            'Recycling' => '#007bff',           // Blue  
            'Garden' => '#8b4513',              // Brown
            // Legacy types (for backward compatibility)
            'Food Waste' => '#28a745',          // Green
            'Garden Waste' => '#8b4513',        // Brown
            'Residual Waste' => '#6c757d',      // Gray
            'Glass' => '#17a2b8',              // Teal
            'Paper' => '#ffc107',              // Yellow
            'Plastic' => '#e83e8c',            // Pink
        ];
        
        return $colors[$binType] ?? '#6c757d'; // Default to gray
    }

    /**
     * Get default bin types for areas
     */
    public static function getDefaultBinTypes(): array
    {
        return ['Food', 'Recycling', 'Garden'];
    }

    /**
     * Address lookup API
     */
    public function lookup(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        
        // Sample address data
        $addresses = [
            ['address' => '123 Main Street, London'],
            ['address' => '456 Oak Avenue, London'],
            ['address' => '789 Pine Road, London'],
        ];

        // Filter addresses based on query
        if ($query) {
            $addresses = array_filter($addresses, function($addr) use ($query) {
                return stripos($addr['address'], $query) !== false;
            });
        }

        return response()->json(array_values($addresses));
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('bins.create');
    }

    /**
     * Store a new bin schedule
     */
    public function store(Request $request)
    {
        // Implementation would go here
        return redirect()->route('bins.index')->with('success', 'Bin schedule created successfully!');
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        return view('bins.edit', compact('id'));
    }

    /**
     * Update a bin schedule
     */
    public function update(Request $request, $id)
    {
        // Implementation would go here
        return redirect()->route('bins.index')->with('success', 'Bin schedule updated successfully!');
    }

    /**
     * Delete a bin schedule
     */
    public function destroy($id)
    {
        // Implementation would go here
        return redirect()->route('bins.index')->with('success', 'Bin schedule deleted successfully!');
    }

    /**
     * Geocode all addresses
     */
    public function geocodeAll()
    {
        // Implementation would go here
        return response()->json(['message' => 'Geocoding completed']);
    }
}
