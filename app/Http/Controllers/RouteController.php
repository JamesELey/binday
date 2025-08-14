<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Collection;
use App\Area;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class RouteController extends Controller
{
    /**
     * Display the route planner dashboard
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $selectedDate = $request->get('date', now()->format('Y-m-d'));
        
        // Get worker's assigned areas (for now, workers can see all areas)
        // In the future, you might want to add a worker_areas table for assignments
        $workerAreas = Area::active()->get();
        
        return view('routes.index', compact('user', 'selectedDate', 'workerAreas'));
    }

    /**
     * Get collections for route planning on a specific date
     */
    public function getCollections(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        $areaIds = $request->get('areas', []);
        $user = Auth::user();
        
        // Build query for collections
        $query = Collection::with(['area', 'user'])
            ->where('collection_date', $date)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');
        
        // Filter by areas if specified
        if (!empty($areaIds)) {
            $query->whereIn('area_id', $areaIds);
        }
        
        // For workers, only show collections in their areas (if area assignments exist)
        if ($user->role === 'worker') {
            // For now, workers can see all collections
            // Later you might add: $query->whereIn('area_id', $user->assignedAreas->pluck('id'));
        }
        
        $collections = $query->orderBy('collection_time')
            ->orderBy('customer_name')
            ->get();
        
        return response()->json([
            'success' => true,
            'collections' => $collections->map(function ($collection) {
                return [
                    'id' => $collection->id,
                    'customer_name' => $collection->customer_name,
                    'address' => $collection->address,
                    'bin_type' => $collection->bin_type,
                    'collection_time' => $collection->collection_time ? $collection->collection_time->format('H:i') : null,
                    'status' => $collection->status,
                    'notes' => $collection->notes,
                    'latitude' => (float) $collection->latitude,
                    'longitude' => (float) $collection->longitude,
                    'area' => $collection->area ? $collection->area->name : 'Unassigned'
                ];
            }),
            'date' => $date,
            'total' => $collections->count()
        ]);
    }

    /**
     * Generate optimized route for collections
     */
    public function optimizeRoute(Request $request)
    {
        $collectionIds = $request->get('collection_ids', []);
        $startLat = $request->get('start_lat', 52.8586); // Default to Eccleshall
        $startLng = $request->get('start_lng', -2.2524);
        
        if (empty($collectionIds)) {
            return response()->json([
                'success' => false,
                'message' => 'No collections selected for route optimization'
            ]);
        }
        
        // Get selected collections
        $collections = Collection::whereIn('id', $collectionIds)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();
        
        if ($collections->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No valid collections found with coordinates'
            ]);
        }
        
        // Simple route optimization using nearest neighbor algorithm
        $optimizedRoute = $this->nearestNeighborOptimization($collections, $startLat, $startLng);
        
        return response()->json([
            'success' => true,
            'route' => $optimizedRoute,
            'total_collections' => count($optimizedRoute),
            'estimated_distance' => $this->calculateTotalDistance($optimizedRoute)
        ]);
    }

    /**
     * Update collection status during route execution
     */
    public function updateCollectionStatus(Request $request, $id)
    {
        $collection = Collection::findOrFail($id);
        
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,collected,cancelled',
            'notes' => 'nullable|string|max:500'
        ]);
        
        $collection->update([
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? $collection->notes
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Collection status updated successfully',
            'collection' => [
                'id' => $collection->id,
                'status' => $collection->status,
                'notes' => $collection->notes
            ]
        ]);
    }

    /**
     * Get route statistics for a specific date
     */
    public function getRouteStats(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        $areaIds = $request->get('areas', []);
        
        $query = Collection::where('collection_date', $date);
        
        if (!empty($areaIds)) {
            $query->whereIn('area_id', $areaIds);
        }
        
        $collections = $query->get();
        
        $stats = [
            'total_collections' => $collections->count(),
            'by_status' => [
                'pending' => $collections->where('status', 'pending')->count(),
                'confirmed' => $collections->where('status', 'confirmed')->count(),
                'collected' => $collections->where('status', 'collected')->count(),
                'cancelled' => $collections->where('status', 'cancelled')->count(),
            ],
            'by_bin_type' => $collections->groupBy('bin_type')->map->count(),
            'completion_rate' => $collections->count() > 0 
                ? round(($collections->where('status', 'collected')->count() / $collections->count()) * 100, 1)
                : 0
        ];
        
        return response()->json([
            'success' => true,
            'stats' => $stats,
            'date' => $date
        ]);
    }

    /**
     * Nearest neighbor algorithm for route optimization
     */
    private function nearestNeighborOptimization($collections, $startLat, $startLng)
    {
        $unvisited = $collections->toArray();
        $route = [];
        $currentLat = $startLat;
        $currentLng = $startLng;
        
        // Add starting point
        $route[] = [
            'type' => 'start',
            'name' => 'Starting Point',
            'latitude' => $currentLat,
            'longitude' => $currentLng,
            'order' => 0
        ];
        
        $order = 1;
        
        while (!empty($unvisited)) {
            $nearestIndex = 0;
            $shortestDistance = $this->calculateDistance(
                $currentLat, $currentLng,
                $unvisited[0]['latitude'], $unvisited[0]['longitude']
            );
            
            // Find nearest unvisited collection
            for ($i = 1; $i < count($unvisited); $i++) {
                $distance = $this->calculateDistance(
                    $currentLat, $currentLng,
                    $unvisited[$i]['latitude'], $unvisited[$i]['longitude']
                );
                
                if ($distance < $shortestDistance) {
                    $shortestDistance = $distance;
                    $nearestIndex = $i;
                }
            }
            
            // Add nearest collection to route
            $collection = $unvisited[$nearestIndex];
            $route[] = [
                'type' => 'collection',
                'id' => $collection['id'],
                'customer_name' => $collection['customer_name'],
                'address' => $collection['address'],
                'bin_type' => $collection['bin_type'],
                'collection_time' => $collection['collection_time'],
                'status' => $collection['status'],
                'notes' => $collection['notes'],
                'latitude' => (float) $collection['latitude'],
                'longitude' => (float) $collection['longitude'],
                'order' => $order,
                'distance_from_previous' => $shortestDistance
            ];
            
            // Update current position
            $currentLat = $collection['latitude'];
            $currentLng = $collection['longitude'];
            
            // Remove from unvisited
            array_splice($unvisited, $nearestIndex, 1);
            $order++;
        }
        
        return $route;
    }

    /**
     * Calculate distance between two points using Haversine formula
     */
    private function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371; // kilometers
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        
        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng/2) * sin($dLng/2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $earthRadius * $c;
    }

    /**
     * Calculate total distance for a route
     */
    private function calculateTotalDistance($route)
    {
        $totalDistance = 0;
        
        for ($i = 1; $i < count($route); $i++) {
            if (isset($route[$i]['distance_from_previous'])) {
                $totalDistance += $route[$i]['distance_from_previous'];
            }
        }
        
        return round($totalDistance, 2);
    }
}
