<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AllowedAreaController extends Controller
{
    /**
     * Display a listing of allowed areas for admin management
     */
    public function index()
    {
        $areas = $this->getAllAreas();
        return view('areas.index', compact('areas'));
    }

    /**
     * Get all areas from storage
     */
    private function getAllAreas(): array
    {
        $storagePath = storage_path('app/allowed_areas.json');
        
        if (!file_exists($storagePath)) {
            // Create initial data file with sample areas
            $defaultBinTypes = \App\Http\Controllers\BinScheduleController::getDefaultBinTypes();
            
            $initialAreas = [
                [
                    'id' => 1,
                    'name' => 'Central London',
                    'postcodes' => 'EC1, EC2, EC3, EC4, WC1, WC2',
                    'description' => 'Central London business district',
                    'active' => true,
                    'type' => 'postcode',
                    'coordinates' => null,
                    'bin_types' => $defaultBinTypes,
                    'created_at' => '2025-01-15'
                ],
                [
                    'id' => 2,
                    'name' => 'North London',
                    'postcodes' => 'N1, N2, N3, N4, N5, N6, N7, N8',
                    'description' => 'North London residential areas',
                    'active' => true,
                    'type' => 'postcode',
                    'coordinates' => null,
                    'bin_types' => $defaultBinTypes,
                    'created_at' => '2025-01-15'
                ],
                [
                    'id' => 3,
                    'name' => 'East London',
                    'postcodes' => 'E1, E2, E3, E4, E5, E6, E7, E8, E9, E10',
                    'description' => 'East London coverage area',
                    'active' => true,
                    'type' => 'postcode',
                    'coordinates' => null,
                    'bin_types' => $defaultBinTypes,
                    'created_at' => '2025-01-15'
                ]
            ];
            
            file_put_contents($storagePath, json_encode($initialAreas, JSON_PRETTY_PRINT));
            return $initialAreas;
        }
        
        $data = file_get_contents($storagePath);
        return json_decode($data, true) ?: [];
    }

    /**
     * Save areas to storage
     */
    private function saveAreas(array $areas): void
    {
        // Sanitize area data to handle apostrophes and special characters
        $areas = $this->sanitizeAreasData($areas);
        
        $storagePath = storage_path('app/allowed_areas.json');
        file_put_contents($storagePath, json_encode($areas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * Sanitize areas data to handle special characters
     */
    private function sanitizeAreasData(array $areas): array
    {
        foreach ($areas as &$area) {
            if (isset($area['name'])) {
                $area['name'] = $this->sanitizeString($area['name']);
            }
            if (isset($area['description'])) {
                $area['description'] = $this->sanitizeString($area['description']);
            }
            if (isset($area['postcodes'])) {
                $area['postcodes'] = $this->sanitizeString($area['postcodes']);
            }
        }
        return $areas;
    }

    /**
     * Sanitize string data to handle apostrophes and special characters
     */
    private function sanitizeString($string): string
    {
        if (!is_string($string)) {
            return $string;
        }
        
        // Replace problematic characters
        $string = str_replace("'", "'", $string); // Replace straight apostrophe with curly apostrophe
        $string = str_replace('"', '"', $string); // Replace straight quotes with curly quotes
        $string = htmlspecialchars_decode($string); // Decode any HTML entities
        return trim($string);
    }

    /**
     * Get next available ID
     */
    private function getNextId(): int
    {
        $areas = $this->getAllAreas();
        $maxId = 0;
        foreach ($areas as $area) {
            if ($area['id'] > $maxId) {
                $maxId = $area['id'];
            }
        }
        return $maxId + 1;
    }

    /**
     * Show the map-based area creation page
     */
    public function createMap()
    {
        return view('areas.create-map');
    }

    /**
     * Store a newly created allowed area
     */
    public function store(Request $request)
    {
        // Handle both traditional form and JSON requests
        if ($request->isJson()) {
            // JSON request from map interface
            $data = $request->all();
            
            // Validate required fields
            if (empty($data['name'])) {
                return response()->json(['error' => 'Area name is required'], 400);
            }
            
            if (empty($data['coordinates']) || !is_array($data['coordinates'])) {
                return response()->json(['error' => 'Valid coordinates are required'], 400);
            }
            
            // Create new area
            $areas = $this->getAllAreas();
            $newArea = [
                'id' => $this->getNextId(),
                'name' => $data['name'],
                'description' => $data['description'] ?? '',
                'active' => $data['active'] ?? true,
                'type' => 'map',
                'postcodes' => null,
                'coordinates' => $data['coordinates'],
                'bin_types' => $data['bin_types'] ?? \App\Http\Controllers\BinScheduleController::getDefaultBinTypes(),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $areas[] = $newArea;
            $this->saveAreas($areas);
            
            return response()->json([
                'success' => true,
                'message' => 'Area created successfully with map coordinates',
                'area' => $newArea
            ]);
        } else {
            // Traditional form request
            $request->validate([
                'name' => 'required|string|max:255',
                'postcodes' => 'required|string|min:2',
                'active' => 'required|boolean',
                'description' => 'nullable|string|max:500'
            ], [
                'name.required' => 'Area name is required',
                'postcodes.required' => 'At least one postcode is required',
                'postcodes.min' => 'Postcodes must be at least 2 characters long',
                'active.required' => 'Please select a status for this area'
            ]);
            
            // Clean and validate postcodes
            $postcodes = $request->input('postcodes');
            $postcodes = trim($postcodes);
            $postcodes = preg_replace('/\s*,\s*/', ', ', $postcodes); // Normalize spacing
            
            // Basic validation for postcode format
            if (empty($postcodes)) {
                return redirect()->back()
                    ->withErrors(['postcodes' => 'Please enter at least one postcode'])
                    ->withInput();
            }
            
            // Create new postcode-based area
            $areas = $this->getAllAreas();
            $newArea = [
                'id' => $this->getNextId(),
                'name' => trim($request->input('name')),
                'description' => trim($request->input('description', '')),
                'postcodes' => $postcodes,
                'active' => $request->boolean('active'),
                'type' => 'postcode',
                'coordinates' => null,
                'bin_types' => $request->input('bin_types', \App\Http\Controllers\BinScheduleController::getDefaultBinTypes()),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $areas[] = $newArea;
            $this->saveAreas($areas);
            
            return redirect()->route('areas.index')
                ->with('success', 'Postcode-based area "' . $newArea['name'] . '" created successfully!');
        }
    }

    /**
     * Show the form for editing an allowed area
     */
    public function edit($id)
    {
        $areas = $this->getAllAreas();
        $area = null;
        
        foreach ($areas as $a) {
            if ($a['id'] == $id) {
                $area = $a;
                break;
            }
        }
        
        if (!$area) {
            return redirect()->route('areas.index')
                ->with('error', 'Area not found');
        }

        return view('areas.edit', compact('area'));
    }

    /**
     * Update the specified allowed area
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'active' => 'required|boolean'
        ]);
        
        $areas = $this->getAllAreas();
        $updated = false;
        
        for ($i = 0; $i < count($areas); $i++) {
            if ($areas[$i]['id'] == $id) {
                $areas[$i]['name'] = $request->input('name');
                $areas[$i]['description'] = $request->input('description', '');
                $areas[$i]['active'] = $request->boolean('active');
                
                // Only update postcodes if it's a postcode-based area
                if ($areas[$i]['type'] === 'postcode') {
                    $request->validate(['postcodes' => 'required|string']);
                    $areas[$i]['postcodes'] = $request->input('postcodes');
                }
                
                $updated = true;
                break;
            }
        }
        
        if (!$updated) {
            return redirect()->route('areas.index')
                ->with('error', 'Area not found');
        }
        
        $this->saveAreas($areas);
        
        return redirect()->route('areas.index')
            ->with('success', 'Allowed area updated successfully!');
    }

    /**
     * Remove the specified allowed area
     */
    public function destroy($id)
    {
        $areas = $this->getAllAreas();
        $newAreas = [];
        $deleted = false;
        
        foreach ($areas as $area) {
            if ($area['id'] != $id) {
                $newAreas[] = $area;
            } else {
                $deleted = true;
            }
        }
        
        if (!$deleted) {
            return redirect()->route('areas.index')
                ->with('error', 'Area not found');
        }
        
        $this->saveAreas($newAreas);
        
        return redirect()->route('areas.index')
            ->with('success', 'Allowed area deleted successfully!');
    }

    /**
     * API endpoint to get all allowed areas
     */
    public function apiList(): JsonResponse
    {
        $areas = $this->getAllAreas();
        $apiAreas = [];
        
        foreach ($areas as $area) {
            $apiArea = [
                'id' => $area['id'],
                'name' => $area['name'],
                'description' => $area['description'],
                'active' => $area['active'],
                'type' => $area['type'],
                'bin_types' => $area['bin_types'] ?? \App\Http\Controllers\BinScheduleController::getDefaultBinTypes(),
                'created_at' => $area['created_at']
            ];
            
            if ($area['type'] === 'postcode' && !empty($area['postcodes'])) {
                $apiArea['postcodes'] = explode(', ', $area['postcodes']);
            } elseif ($area['type'] === 'map' && !empty($area['coordinates'])) {
                $apiArea['coordinates'] = $area['coordinates'];
            }
            
            $apiAreas[] = $apiArea;
        }

        return response()->json([
            'success' => true,
            'areas' => $apiAreas,
            'total' => count($apiAreas)
        ]);
    }

    /**
     * Geocode a postcode to get latitude and longitude
     */
    public function geocodePostcode(Request $request): JsonResponse
    {
        $postcode = $request->input('postcode');
        
        if (empty($postcode)) {
            return response()->json(['error' => 'Postcode is required'], 400);
        }

        try {
            // Use Nominatim API for geocoding
            $url = "https://nominatim.openstreetmap.org/search?format=json&countrycodes=gb&q=" . urlencode($postcode);
            $response = file_get_contents($url);
            $data = json_decode($response, true);
            
            if (empty($data)) {
                return response()->json(['error' => 'Postcode not found'], 404);
            }
            
            $result = $data[0];
            return response()->json([
                'postcode' => $postcode,
                'latitude' => floatval($result['lat']),
                'longitude' => floatval($result['lon']),
                'display_name' => $result['display_name']
            ]);
            
        } catch (Exception $e) {
            return response()->json(['error' => 'Geocoding service unavailable'], 500);
        }
    }

    /**
     * Check if a postcode is within allowed areas using coordinates
     */
    public function checkPostcode($postcode): bool
    {
        try {
            // Get coordinates for postcode
            $coords = $this->getPostcodeCoordinates($postcode);
            if (!$coords) {
                return false;
            }
            
            // Check if coordinates are within any allowed area polygon
            return $this->isPointInAllowedAreas($coords['lat'], $coords['lng']);
            
        } catch (Exception $e) {
            // Fallback to simple postcode area check
            return $this->checkPostcodeAreaFallback($postcode);
        }
    }

    /**
     * Get coordinates for a postcode
     */
    private function getPostcodeCoordinates($postcode): ?array
    {
        try {
            $url = "https://nominatim.openstreetmap.org/search?format=json&countrycodes=gb&q=" . urlencode($postcode);
            $response = file_get_contents($url);
            $data = json_decode($response, true);
            
            if (empty($data)) {
                return null;
            }
            
            return [
                'lat' => floatval($data[0]['lat']),
                'lng' => floatval($data[0]['lon'])
            ];
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Check if a point is within any allowed area polygons
     */
    private function isPointInAllowedAreas($lat, $lng): bool
    {
        $areas = $this->getAllAreas();
        
        foreach ($areas as $area) {
            // Skip inactive areas
            if (!$area['active']) {
                continue;
            }
            
            // Check map-based areas with coordinates
            if ($area['type'] === 'map' && !empty($area['coordinates'])) {
                if ($this->isPointInPolygon($lat, $lng, $area['coordinates'])) {
                    return true;
                }
            }
            
            // Check postcode-based areas
            if ($area['type'] === 'postcode' && !empty($area['postcodes'])) {
                if ($this->isPointInPostcodeArea($lat, $lng, $area['postcodes'])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if coordinates fall within postcode areas (fallback for postcode-based areas)
     */
    private function isPointInPostcodeArea($lat, $lng, $postcodes): bool
    {
        // This is a simplified approach - in reality you'd have precise boundaries for each postcode
        // For now, we'll use rough geographic bounds for common London postcodes
        $postcodeList = array_map('trim', explode(',', $postcodes));
        
        foreach ($postcodeList as $postcode) {
            $bounds = $this->getPostcodeBounds($postcode);
            if ($bounds && $this->isPointInBounds($lat, $lng, $bounds)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get rough geographic bounds for a postcode area
     */
    private function getPostcodeBounds($postcode): ?array
    {
        $bounds = [
            // Central London
            'EC1' => ['lat_min' => 51.5150, 'lat_max' => 51.5250, 'lng_min' => -0.1150, 'lng_max' => -0.0950],
            'EC2' => ['lat_min' => 51.5150, 'lat_max' => 51.5250, 'lng_min' => -0.0950, 'lng_max' => -0.0750],
            'EC3' => ['lat_min' => 51.5100, 'lat_max' => 51.5200, 'lng_min' => -0.0850, 'lng_max' => -0.0650],
            'EC4' => ['lat_min' => 51.5100, 'lat_max' => 51.5200, 'lng_min' => -0.1150, 'lng_max' => -0.0950],
            'WC1' => ['lat_min' => 51.5200, 'lat_max' => 51.5300, 'lng_min' => -0.1350, 'lng_max' => -0.1150],
            'WC2' => ['lat_min' => 51.5100, 'lat_max' => 51.5200, 'lng_min' => -0.1350, 'lng_max' => -0.1150],
            
            // North London
            'N1' => ['lat_min' => 51.5300, 'lat_max' => 51.5500, 'lng_min' => -0.1200, 'lng_max' => -0.0800],
            'N2' => ['lat_min' => 51.5500, 'lat_max' => 51.5700, 'lng_min' => -0.1600, 'lng_max' => -0.1200],
            'N3' => ['lat_min' => 51.5700, 'lat_max' => 51.5900, 'lng_min' => -0.1800, 'lng_max' => -0.1400],
            'N4' => ['lat_min' => 51.5500, 'lat_max' => 51.5700, 'lng_min' => -0.1200, 'lng_max' => -0.0800],
            'N5' => ['lat_min' => 51.5500, 'lat_max' => 51.5700, 'lng_min' => -0.1400, 'lng_max' => -0.1000],
            'N6' => ['lat_min' => 51.5600, 'lat_max' => 51.5800, 'lng_min' => -0.1600, 'lng_max' => -0.1200],
            'N7' => ['lat_min' => 51.5500, 'lat_max' => 51.5700, 'lng_min' => -0.1400, 'lng_max' => -0.1000],
            'N8' => ['lat_min' => 51.5700, 'lat_max' => 51.5900, 'lng_min' => -0.1200, 'lng_max' => -0.0800],
            
            // East London
            'E1' => ['lat_min' => 51.5100, 'lat_max' => 51.5250, 'lng_min' => -0.0750, 'lng_max' => -0.0550],
            'E2' => ['lat_min' => 51.5250, 'lat_max' => 51.5400, 'lng_min' => -0.0650, 'lng_max' => -0.0450],
            'E3' => ['lat_min' => 51.5350, 'lat_max' => 51.5500, 'lng_min' => -0.0150, 'lng_max' => 0.0050],
        ];
        
        return $bounds[$postcode] ?? null;
    }

    /**
     * Check if point is within geographic bounds
     */
    private function isPointInBounds($lat, $lng, $bounds): bool
    {
        return $lat >= $bounds['lat_min'] && $lat <= $bounds['lat_max'] &&
               $lng >= $bounds['lng_min'] && $lng <= $bounds['lng_max'];
    }

    /**
     * Point-in-polygon algorithm
     */
    private function isPointInPolygon($lat, $lng, $polygon): bool
    {
        $inside = false;
        $count = count($polygon);
        
        for ($i = 0, $j = $count - 1; $i < $count; $j = $i++) {
            $xi = $polygon[$i][1]; // longitude
            $yi = $polygon[$i][0]; // latitude
            $xj = $polygon[$j][1]; // longitude
            $yj = $polygon[$j][0]; // latitude
            
            if ((($yi > $lat) !== ($yj > $lat)) && ($lng < ($xj - $xi) * ($lat - $yi) / ($yj - $yi) + $xi)) {
                $inside = !$inside;
            }
        }
        
        return $inside;
    }

    /**
     * Fallback postcode area check (for when geocoding fails)
     */
    private function checkPostcodeAreaFallback($postcode): bool
    {
        // Extract postcode area (first part before space or number)
        $postcodeArea = strtoupper(preg_replace('/[0-9\s].*/', '', $postcode));
        
        // Allowed postcode areas (fallback)
        $allowedAreas = [
            'EC1', 'EC2', 'EC3', 'EC4', 'WC1', 'WC2',  // Central London
            'N1', 'N2', 'N3', 'N4', 'N5', 'N6', 'N7', 'N8',  // North London
            'E1', 'E2', 'E3', 'E4', 'E5', 'E6', 'E7', 'E8', 'E9', 'E10'  // East London
        ];

        return in_array($postcodeArea, $allowedAreas);
    }

    /**
     * Show the bin types management page for an area
     */
    public function manageBinTypes($id)
    {
        $areas = $this->getAllAreas();
        $area = collect($areas)->firstWhere('id', (int)$id);
        
        if (!$area) {
            return redirect()->route('areas.index')->with('error', 'Area not found!');
        }

        // Get all possible bin types
        $allBinTypes = [
            'Food' => '#22c55e',     // green
            'Recycling' => '#3b82f6', // blue  
            'Garden' => '#a3721e',    // brown
            'General Waste' => '#22c55e', // green (legacy)
            'Residual Waste' => '#22c55e', // green (legacy)
            'Glass' => '#14b8a6',     // teal
            'Paper' => '#eab308',     // yellow
            'Plastic' => '#ec4899',   // pink
            'Textiles' => '#8b5cf6',  // purple
            'Electronics' => '#64748b', // slate
            'Hazardous' => '#ef4444',  // red
            'Bulky Items' => '#f97316' // orange
        ];

        return view('areas.manage-bin-types', compact('area', 'allBinTypes'));
    }

    /**
     * Update bin types for an area
     */
    public function updateBinTypes(Request $request, $id)
    {
        $request->validate([
            'bin_types' => 'array',
            'bin_types.*' => 'string|max:50',
            'active_bin_types' => 'array',
            'active_bin_types.*' => 'string|max:50'
        ]);

        $areas = $this->getAllAreas();
        $areaIndex = null;
        
        foreach ($areas as $index => $area) {
            if ($area['id'] == $id) {
                $areaIndex = $index;
                break;
            }
        }

        if ($areaIndex === null) {
            return redirect()->route('areas.index')->with('error', 'Area not found!');
        }

        // Get active bin types from checkboxes
        $activeBinTypes = $request->input('active_bin_types', []);
        
        // Add any new custom bin types
        $newBinTypes = $request->input('new_bin_types', []);
        $newBinTypes = array_filter($newBinTypes, function($type) {
            return !empty(trim($type));
        });

        // Combine active existing types with new types
        $allBinTypes = array_merge($activeBinTypes, $newBinTypes);
        $allBinTypes = array_unique($allBinTypes);
        $allBinTypes = array_values($allBinTypes); // Re-index array

        // Update the area
        $areas[$areaIndex]['bin_types'] = $allBinTypes;
        $areas[$areaIndex]['updated_at'] = date('Y-m-d H:i:s');

        $this->saveAreas($areas);

        return redirect()->route('areas.manageBinTypes', $id)
                        ->with('success', 'Bin types updated successfully!');
    }

    /**
     * Add a new bin type to an area
     */
    public function addBinType(Request $request, $id)
    {
        $request->validate([
            'bin_type' => 'required|string|max:50'
        ]);

        $areas = $this->getAllAreas();
        $areaIndex = null;
        
        foreach ($areas as $index => $area) {
            if ($area['id'] == $id) {
                $areaIndex = $index;
                break;
            }
        }

        if ($areaIndex === null) {
            return response()->json(['error' => 'Area not found'], 404);
        }

        $newBinType = trim($request->input('bin_type'));
        $currentBinTypes = $areas[$areaIndex]['bin_types'] ?? [];

        // Check if bin type already exists
        if (in_array($newBinType, $currentBinTypes)) {
            return response()->json(['error' => 'Bin type already exists'], 400);
        }

        // Add the new bin type
        $currentBinTypes[] = $newBinType;
        $areas[$areaIndex]['bin_types'] = $currentBinTypes;
        $areas[$areaIndex]['updated_at'] = date('Y-m-d H:i:s');

        $this->saveAreas($areas);

        return response()->json([
            'success' => true,
            'message' => 'Bin type added successfully',
            'bin_type' => $newBinType
        ]);
    }

    /**
     * Remove a bin type from an area
     */
    public function removeBinType(Request $request, $id)
    {
        $request->validate([
            'bin_type' => 'required|string'
        ]);

        $areas = $this->getAllAreas();
        $areaIndex = null;
        
        foreach ($areas as $index => $area) {
            if ($area['id'] == $id) {
                $areaIndex = $index;
                break;
            }
        }

        if ($areaIndex === null) {
            return response()->json(['error' => 'Area not found'], 404);
        }

        $binTypeToRemove = $request->input('bin_type');
        $currentBinTypes = $areas[$areaIndex]['bin_types'] ?? [];

        // Remove the bin type
        $currentBinTypes = array_filter($currentBinTypes, function($type) use ($binTypeToRemove) {
            return $type !== $binTypeToRemove;
        });

        $areas[$areaIndex]['bin_types'] = array_values($currentBinTypes);
        $areas[$areaIndex]['updated_at'] = date('Y-m-d H:i:s');

        $this->saveAreas($areas);

        return response()->json([
            'success' => true,
            'message' => 'Bin type removed successfully'
        ]);
    }
}
