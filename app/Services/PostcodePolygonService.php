<?php

namespace App\Services;

class PostcodePolygonService
{
    /**
     * Convert postcode areas to polygon coordinates
     */
    public function convertPostcodesToPolygon(string $postcodes): ?array
    {
        $postcodeList = $this->parsePostcodes($postcodes);
        
        if (empty($postcodeList)) {
            return null;
        }

        // Try to get actual boundaries from Nominatim
        $boundaries = $this->fetchPostcodeBoundaries($postcodeList);
        
        if (!empty($boundaries)) {
            return $this->mergeBoundaries($boundaries);
        }

        // Fallback: create approximate polygon from postcode centers
        return $this->createApproximatePolygon($postcodeList);
    }

    /**
     * Parse comma-separated postcodes
     */
    private function parsePostcodes(string $postcodes): array
    {
        $postcodes = preg_replace('/\s*,\s*/', ',', trim($postcodes));
        $postcodeList = explode(',', $postcodes);
        
        return array_filter(array_map('trim', $postcodeList));
    }

    /**
     * Fetch actual postcode boundaries from Nominatim
     */
    private function fetchPostcodeBoundaries(array $postcodes): array
    {
        $boundaries = [];
        
        foreach ($postcodes as $postcode) {
            $boundary = $this->fetchSinglePostcodeBoundary($postcode);
            if ($boundary) {
                $boundaries[] = $boundary;
            }
        }
        
        return $boundaries;
    }

    /**
     * Fetch boundary for a single postcode
     */
    private function fetchSinglePostcodeBoundary(string $postcode): ?array
    {
        try {
            // Nominatim API for postcode boundaries
            $url = "https://nominatim.openstreetmap.org/search";
            $params = [
                'q' => $postcode . ', UK',
                'format' => 'json',
                'polygon_geojson' => '1',
                'addressdetails' => '1',
                'limit' => '1'
            ];
            
            $url .= '?' . http_build_query($params);
            
            $context = stream_context_create([
                'http' => [
                    'timeout' => 5,
                    'user_agent' => 'BinDay Collection Service/1.0'
                ]
            ]);
            
            $response = @file_get_contents($url, false, $context);
            
            if ($response === false) {
                return null;
            }
            
            $data = json_decode($response, true);
            
            if (empty($data) || !isset($data[0]['geojson'])) {
                return null;
            }
            
            $geojson = $data[0]['geojson'];
            
            // Convert GeoJSON to our coordinate format
            return $this->convertGeoJsonToCoordinates($geojson);
            
        } catch (\Exception $e) {
            \Log::warning('Failed to fetch postcode boundary', [
                'postcode' => $postcode,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Convert GeoJSON to coordinate array
     */
    private function convertGeoJsonToCoordinates(array $geojson): ?array
    {
        if (!isset($geojson['type']) || !isset($geojson['coordinates'])) {
            return null;
        }

        switch ($geojson['type']) {
            case 'Polygon':
                // Use the exterior ring (first array)
                $coordinates = $geojson['coordinates'][0];
                break;
                
            case 'MultiPolygon':
                // Use the largest polygon
                $largest = [];
                foreach ($geojson['coordinates'] as $polygon) {
                    if (count($polygon[0]) > count($largest)) {
                        $largest = $polygon[0];
                    }
                }
                $coordinates = $largest;
                break;
                
            case 'Point':
                // Create a small square around the point
                $coord = $geojson['coordinates'];
                $offset = 0.01; // ~1km
                return [
                    [$coord[1] + $offset, $coord[0] - $offset], // lat, lng
                    [$coord[1] + $offset, $coord[0] + $offset],
                    [$coord[1] - $offset, $coord[0] + $offset],
                    [$coord[1] - $offset, $coord[0] - $offset]
                ];
                
            default:
                return null;
        }

        // Convert [lng, lat] to [lat, lng] and filter valid coordinates
        $converted = [];
        foreach ($coordinates as $coord) {
            if (is_array($coord) && count($coord) >= 2) {
                $lat = (float) $coord[1];
                $lng = (float) $coord[0];
                
                // Basic UK coordinate validation
                if ($lat >= 49.5 && $lat <= 61.0 && $lng >= -8.5 && $lng <= 2.0) {
                    $converted[] = [$lat, $lng];
                }
            }
        }

        return empty($converted) ? null : $converted;
    }

    /**
     * Merge multiple boundaries into one polygon
     */
    private function mergeBoundaries(array $boundaries): array
    {
        if (count($boundaries) === 1) {
            return $boundaries[0];
        }

        // For multiple boundaries, create a convex hull
        $allPoints = [];
        foreach ($boundaries as $boundary) {
            $allPoints = array_merge($allPoints, $boundary);
        }

        return $this->convexHull($allPoints);
    }

    /**
     * Create approximate polygon from postcode centers
     */
    private function createApproximatePolygon(array $postcodes): array
    {
        $centers = [];
        
        foreach ($postcodes as $postcode) {
            $center = $this->getPostcodeCenter($postcode);
            if ($center) {
                $centers[] = $center;
            }
        }

        if (empty($centers)) {
            // Default to London area if no postcodes found
            return [
                [51.5074, -0.1278],   // Central London
                [51.5200, -0.1000],   // North East
                [51.4900, -0.1000],   // South East  
                [51.4900, -0.1500],   // South West
                [51.5200, -0.1500]    // North West
            ];
        }

        if (count($centers) === 1) {
            // Create a square around single point
            $center = $centers[0];
            $offset = 0.02; // ~2km
            return [
                [$center[0] + $offset, $center[1] - $offset],
                [$center[0] + $offset, $center[1] + $offset],
                [$center[0] - $offset, $center[1] + $offset],
                [$center[0] - $offset, $center[1] - $offset]
            ];
        }

        // Create convex hull around all centers
        return $this->convexHull($centers);
    }

    /**
     * Get approximate center coordinates for a postcode
     */
    private function getPostcodeCenter(string $postcode): ?array
    {
        // Simplified UK postcode center mapping
        $postcodeAreas = [
            // London
            'W1' => [51.5154, -0.1414],   'W2' => [51.5154, -0.1814],
            'WC1' => [51.5227, -0.1265],  'WC2' => [51.5133, -0.1282],
            'EC1' => [51.5200, -0.1000],  'EC2' => [51.5170, -0.0926],
            'EC3' => [51.5134, -0.0820],  'EC4' => [51.5134, -0.0996],
            'SW1' => [51.4975, -0.1357],  'SW2' => [51.4630, -0.1726],
            'SW3' => [51.4925, -0.1615],  'SE1' => [51.5045, -0.0865],
            'N1' => [51.5370, -0.1090],   'E1' => [51.5154, -0.0648],
            
            // Birmingham
            'B1' => [52.4829, -1.8936],   'B2' => [52.4796, -1.9026],
            
            // Manchester  
            'M1' => [53.4794, -2.2453],   'M2' => [53.4794, -2.2553],
            
            // Liverpool
            'L1' => [53.4106, -2.9779],   'L2' => [53.4106, -2.9679],
            
            // Glasgow
            'G1' => [55.8642, -4.2518],   'G2' => [55.8642, -4.2618],
            
            // Edinburgh
            'EH1' => [55.9533, -3.1883],  'EH2' => [55.9533, -3.1983],
            
            // Bristol
            'BS1' => [51.4545, -2.5879],  'BS2' => [51.4745, -2.5679],
            
            // Leeds
            'LS1' => [53.8008, -1.5491],  'LS2' => [53.8108, -1.5591],
            
            // Sheffield
            'S1' => [53.3811, -1.4701],   'S2' => [53.3711, -1.4601],
            
            // Newcastle
            'NE1' => [54.9783, -1.6178],  'NE2' => [54.9883, -1.6278],
            
            // Cardiff
            'CF1' => [51.4816, -3.1791],  'CF2' => [51.4916, -3.1891],
            
            // Staffordshire (for Eccleshall)
            'ST21' => [52.8586, -2.2524]
        ];
        
        // Extract postcode area (e.g., "W1" from "W1A 0AX")
        $area = strtoupper(preg_replace('/[0-9].*/', '', $postcode));
        
        return $postcodeAreas[$area] ?? null;
    }

    /**
     * Simple convex hull algorithm (Gift wrapping)
     */
    private function convexHull(array $points): array
    {
        if (count($points) < 3) {
            return $points;
        }

        // Find the leftmost point
        $leftmost = 0;
        for ($i = 1; $i < count($points); $i++) {
            if ($points[$i][1] < $points[$leftmost][1]) {
                $leftmost = $i;
            }
        }

        $hull = [];
        $p = $leftmost;
        
        do {
            $hull[] = $points[$p];
            $q = ($p + 1) % count($points);
            
            for ($i = 0; $i < count($points); $i++) {
                if ($this->orientation($points[$p], $points[$i], $points[$q]) === 2) {
                    $q = $i;
                }
            }
            
            $p = $q;
            
        } while ($p !== $leftmost && count($hull) < 100); // Safety limit
        
        return $hull;
    }

    /**
     * Calculate orientation of ordered triplet
     */
    private function orientation(array $p, array $q, array $r): int
    {
        $val = ($q[0] - $p[0]) * ($r[1] - $q[1]) - ($q[1] - $p[1]) * ($r[0] - $q[0]);
        
        if ($val === 0.0) return 0;     // Collinear
        return ($val > 0) ? 1 : 2;      // Clockwise or Counterclockwise
    }
}
