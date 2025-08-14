<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\AllowedAreaController;
use App\Http\Controllers\CollectionController;

class DataSeederController extends Controller
{
    private $areaController;
    private $collectionController;

    public function __construct()
    {
        $this->areaController = new AllowedAreaController();
        $this->collectionController = new CollectionController();
    }

    /**
     * Show the seeding management interface
     */
    public function index()
    {
        return view('admin.seed-data');
    }

    /**
     * Seed all demo data using database seeders
     */
    public function seedAll()
    {
        try {
            // Use Laravel's database seeders instead of JSON files
            \Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\AreaSeeder',
                '--force' => true
            ]);
            
            \Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\ComprehensiveCollectionSeeder', 
                '--force' => true
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'All demo data seeded successfully using database!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error seeding data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete all data
     */
    public function deleteAll()
    {
        try {
            $this->deleteAllCollections();
            $this->deleteAllAreas();
            
            return response()->json([
                'success' => true,
                'message' => 'All data deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error deleting data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Seed only areas using database seeder
     */
    public function seedEccleshallAreaOnly()
    {
        try {
            \Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\AreaSeeder',
                '--force' => true
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Areas seeded successfully using database!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error seeding Eccleshall area: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Seed only collections using database seeder
     */
    public function seedCollectionsOnly()
    {
        try {
            \Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\ComprehensiveCollectionSeeder',
                '--force' => true
            ]);
            
            $collectionsCount = \App\Collection::count();
            
            return response()->json([
                'success' => true,
                'message' => 'Collections seeded successfully using database!',
                'collections' => $collectionsCount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error seeding collections: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Seed Eccleshall area with realistic detailed polygon
     */
    public function seedEccleshallArea()
    {
        // Eccleshall, Staffordshire - detailed polygon covering the town boundary
        $eccleshallPolygon = [
            [52.8620, -2.2580], // North boundary (near A519)
            [52.8615, -2.2520], // Northeast (Stafford Road area)
            [52.8605, -2.2480], // East (towards Stone Road)
            [52.8590, -2.2440], // Southeast (residential area)
            [52.8575, -2.2420], // East boundary
            [52.8560, -2.2435], // Southeast curve
            [52.8545, -2.2455], // South (industrial/farm area)
            [52.8535, -2.2480], // South boundary
            [52.8530, -2.2510], // Southwest
            [52.8525, -2.2540], // Southwest boundary
            [52.8535, -2.2565], // West (towards Newport Road)
            [52.8545, -2.2585], // Northwest
            [52.8560, -2.2595], // North boundary (rural edge)
            [52.8575, -2.2590], // North curve
            [52.8590, -2.2585], // Northeast curve
            [52.8605, -2.2580], // Back towards north
            [52.8620, -2.2580]  // Close polygon
        ];

        // Save Eccleshall area
        $storagePath = storage_path('app/allowed_areas.json');
        $areas = [];
        
        if (file_exists($storagePath)) {
            $data = file_get_contents($storagePath);
            $areas = json_decode($data, true) ?: [];
        }

        $eccleshallArea = [
            'id' => $this->getNextAreaId($areas),
            'name' => 'Eccleshall, Staffordshire',
            'description' => 'Historic market town in Staffordshire with comprehensive bin collection coverage',
            'active' => true,
            'type' => 'map',
            'postcodes' => null,
            'bin_types' => \App\Http\Controllers\BinScheduleController::getDefaultBinTypes(),
            'coordinates' => $eccleshallPolygon,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $areas[] = $eccleshallArea;
        file_put_contents($storagePath, json_encode($areas, JSON_PRETTY_PRINT));

        return $eccleshallArea;
    }

    /**
     * Seed 20 random collections in Eccleshall over 2 weeks
     */
    public function seedEccleshallCollections()
    {
        $collections = $this->getExistingCollections();
        $startId = $this->getNextCollectionId($collections);
        $newCollections = [];

        // Get collection routes (different areas of Eccleshall)
        $routes = $this->getEccleshallRoutes();
        
        // Start from next Monday
        $startDate = new \DateTime();
        $startDate->modify('next monday');
        
        $collectionId = $startId;

        // Generate 2 weeks of collections
        for ($week = 0; $week < 2; $week++) {
            // Week 1: Food waste collection
            // Week 2: Recycling and Garden waste collection
            $weekTypes = $week === 0 ? ['Food'] : ['Recycling', 'Garden'];
            
            // Collection days: Monday, Wednesday, Friday
            $collectionDays = [0, 2, 4]; // Monday = 0, Wednesday = 2, Friday = 4
            
            foreach ($collectionDays as $dayOffset) {
                $collectionDate = clone $startDate;
                $collectionDate->modify("+{$week} weeks +{$dayOffset} days");
                
                // Each day covers one route (area of town)
                $route = $routes[$dayOffset]; // Monday=Route1, Wed=Route2, Fri=Route3
                
                foreach ($route['addresses'] as $address) {
                    foreach ($weekTypes as $binType) {
                        $collection = [
                            'id' => $collectionId++,
                            'customer_name' => $this->generateCustomerName(),
                            'phone' => $this->generatePhoneNumber(),
                            'address' => $address,
                            'bin_type' => $binType,
                            'collection_date' => $collectionDate->format('Y-m-d'),
                            'collection_time' => $route['time_slot'],
                            'status' => $this->getRealisticStatus($collectionDate),
                            'notes' => $this->generateRouteNotes($route['name'], $binType),
                            'created_at' => date('Y-m-d H:i:s')
                        ];
                        
                        $newCollections[] = $collection;
                    }
                }
            }
        }

        // Sanitize and save collections
        $sanitizedCollections = $this->sanitizeCollectionsData($newCollections);
        $this->saveCollections($sanitizedCollections);

        return $sanitizedCollections;
    }

    /**
     * Get Eccleshall collection routes (realistic bin collection areas)
     */
    private function getEccleshallRoutes(): array
    {
        return [
            // Monday Route - Town Centre & High Street Area
            0 => [
                'name' => 'Town Centre Route',
                'time_slot' => '08:00',
                'addresses' => [
                    '12 High Street, Eccleshall, Staffordshire, ST21 6BZ',
                    '45 Castle Street, Eccleshall, Staffordshire, ST21 6DF',
                    '23 Stafford Street, Eccleshall, Staffordshire, ST21 6BH',
                    '25 Sheepmarket, Eccleshall, Staffordshire, ST21 6BW',
                    '15 Guildhall Lane, Eccleshall, Staffordshire, ST21 6LU',
                    '33 School Lane, Eccleshall, Staffordshire, ST21 6LW',
                    '27 Bishop\'s Court, Eccleshall, Staffordshire, ST21 6LS'
                ]
            ],
            // Wednesday Route - Residential Estates North
            2 => [
                'name' => 'North Residential Route',
                'time_slot' => '09:30',
                'addresses' => [
                    '67 Newport Road, Eccleshall, Staffordshire, ST21 6JB',
                    '8 Stone Road, Eccleshall, Staffordshire, ST21 6JF',
                    '34 Buchanan Avenue, Eccleshall, Staffordshire, ST21 6JL',
                    '56 Badgers Croft, Eccleshall, Staffordshire, ST21 6LB',
                    '41 Millfield Gardens, Eccleshall, Staffordshire, ST21 6LG',
                    '73 Pinfold Lane, Eccleshall, Staffordshire, ST21 6LH',
                    '52 The Limes, Eccleshall, Staffordshire, ST21 6LN'
                ]
            ],
            // Friday Route - Modern Housing Developments South
            4 => [
                'name' => 'South Housing Route',
                'time_slot' => '11:00',
                'addresses' => [
                    '19 Crooked Bridge Road, Eccleshall, Staffordshire, ST21 6LE',
                    '14 Violets Way, Eccleshall, Staffordshire, ST21 6LP',
                    '38 Weavers Close, Eccleshall, Staffordshire, ST21 6LQ',
                    '61 Woods Lane, Eccleshall, Staffordshire, ST21 6LR',
                    '49 Ferndale Close, Eccleshall, Staffordshire, ST21 6LT',
                    '72 Offley Brook, Eccleshall, Staffordshire, ST21 6LV'
                ]
            ]
        ];
    }

    /**
     * Get Eccleshall addresses (real streets) - Legacy method for compatibility
     */
    private function getEccleshallAddresses(): array
    {
        $routes = $this->getEccleshallRoutes();
        $allAddresses = [];
        
        foreach ($routes as $route) {
            $allAddresses = array_merge($allAddresses, $route['addresses']);
        }
        
        return $allAddresses;
    }

    /**
     * Get realistic status based on collection date
     */
    private function getRealisticStatus(\DateTime $collectionDate): string
    {
        $now = new \DateTime();
        $daysDiff = $collectionDate->diff($now)->days;
        
        if ($collectionDate < $now) {
            // Past collections are mostly completed
            return rand(1, 10) <= 8 ? 'Completed' : 'Pending';
        } elseif ($daysDiff <= 2) {
            // Near future collections are scheduled
            return 'Scheduled';
        } else {
            // Far future collections are pending
            return 'Pending';
        }
    }

    /**
     * Generate route-specific notes
     */
    private function generateRouteNotes(string $routeName, string $binType): string
    {
        $routeNotes = [
            'Town Centre Route' => [
                'Food' => 'Town centre collection - early morning to avoid traffic',
                'Recycling' => 'High Street area - check for cardboard from shops',
                'Garden' => 'Limited garden waste in town centre area'
            ],
            'North Residential Route' => [
                'Food' => 'Residential area - family households, typical food waste',
                'Recycling' => 'Good recycling participation in this area',
                'Garden' => 'Large gardens - expect higher garden waste volume'
            ],
            'South Housing Route' => [
                'Food' => 'Modern housing estate - younger families',
                'Recycling' => 'New development - residents very environmentally conscious',
                'Garden' => 'New gardens - moderate garden waste expected'
            ]
        ];

        $standardNotes = [
            'Standard collection',
            'Regular customer',
            'No special requirements',
            'Access via front gate',
            'Leave bins after collection'
        ];

        if (isset($routeNotes[$routeName][$binType])) {
            return $routeNotes[$routeName][$binType];
        }

        return $standardNotes[array_rand($standardNotes)];
    }

    /**
     * Generate random customer names
     */
    private function generateCustomerName(): string
    {
        $firstNames = ['James', 'Sarah', 'Michael', 'Emma', 'David', 'Lisa', 'Robert', 'Helen', 'John', 'Amanda', 'Paul', 'Karen', 'Mark', 'Rachel', 'Andrew', 'Claire', 'Stephen', 'Laura', 'Matthew', 'Susan'];
        $lastNames = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin'];
        
        return $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
    }

    /**
     * Generate random UK phone numbers
     */
    private function generatePhoneNumber(): string
    {
        $prefixes = ['07123', '07234', '07345', '07456', '07567', '07678', '07789', '07890'];
        $prefix = $prefixes[array_rand($prefixes)];
        $suffix = str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
        return $prefix . $suffix;
    }

    /**
     * Generate random collection notes
     */
    private function generateRandomNotes(): string
    {
        $notes = [
            'Leave bin at front gate',
            'Ring doorbell for access',
            'Bin in side alley',
            'Back garden access required',
            'Please close gate after collection',
            'Bin located behind garage',
            'Access through side passage',
            'Key safe code: 1234',
            'Contact customer on arrival',
            'Bin on driveway',
            '',
            '',
            '' // Empty notes for variety
        ];
        
        return $notes[array_rand($notes)];
    }

    /**
     * Get existing collections from database
     */
    private function getExistingCollections(): array
    {
        return \App\Collection::all()->toArray();
    }

    /**
     * Save collections to database (using Laravel seeders)
     */
    private function saveCollections(array $newCollections): void
    {
        // Use the database seeder instead of JSON files
        \Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\CollectionSeeder',
            '--force' => true
        ]);
    }

    /**
     * Sanitize collections data to handle special characters
     */
    private function sanitizeCollectionsData(array $collections): array
    {
        foreach ($collections as &$collection) {
            if (isset($collection['customer_name'])) {
                $collection['customer_name'] = $this->sanitizeString($collection['customer_name']);
            }
            if (isset($collection['address'])) {
                $collection['address'] = $this->sanitizeString($collection['address']);
            }
            if (isset($collection['notes'])) {
                $collection['notes'] = $this->sanitizeString($collection['notes']);
            }
        }
        return $collections;
    }

    /**
     * Sanitize string data to handle apostrophes and special characters
     */
    private function sanitizeString($string): string
    {
        // Replace problematic characters
        $string = str_replace("'", "'", $string); // Replace straight apostrophe with curly apostrophe
        $string = str_replace('"', '"', $string); // Replace straight quotes with curly quotes
        $string = htmlspecialchars_decode($string); // Decode any HTML entities
        return trim($string);
    }

    /**
     * Get next collection ID
     */
    private function getNextCollectionId(array $collections): int
    {
        $maxId = 0;
        foreach ($collections as $collection) {
            if ($collection['id'] > $maxId) {
                $maxId = $collection['id'];
            }
        }
        return $maxId + 1;
    }

    /**
     * Get next area ID
     */
    private function getNextAreaId(array $areas): int
    {
        $maxId = 0;
        foreach ($areas as $area) {
            if ($area['id'] > $maxId) {
                $maxId = $area['id'];
            }
        }
        return $maxId + 1;
    }

    /**
     * Delete all collections from database
     */
    public function deleteAllCollections()
    {
        \App\Collection::truncate();
        return true;
    }

    /**
     * Delete all areas from database
     */
    public function deleteAllAreas()
    {
        \App\Area::truncate();
        return true;
    }

    /**
     * Get data summary from database
     */
    public function getDataSummary()
    {
        $areas = \App\Area::all();
        $collections = \App\Collection::all();
        
        return response()->json([
            'areas' => $areas->count(),
            'collections' => $collections->count(),
            'active_areas' => $areas->where('active', true)->count(),
            'recent_collections' => $collections->where('collection_date', '>=', now()->subDays(14))->count()
        ]);
    }
}
