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
     * Seed all demo data
     */
    public function seedAll()
    {
        try {
            $this->seedEccleshallArea();
            $this->seedEccleshallCollections();
            
            return response()->json([
                'success' => true,
                'message' => 'All demo data seeded successfully!'
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
     * Seed only Eccleshall area
     */
    public function seedEccleshallAreaOnly()
    {
        try {
            $area = $this->seedEccleshallArea();
            return response()->json([
                'success' => true,
                'message' => 'Eccleshall area seeded successfully!',
                'area' => $area
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error seeding Eccleshall area: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Seed only collections
     */
    public function seedCollectionsOnly()
    {
        try {
            $collections = $this->seedEccleshallCollections();
            return response()->json([
                'success' => true,
                'message' => 'Collections seeded successfully!',
                'collections' => count($collections)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error seeding collections: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Seed Eccleshall area with accurate polygon
     */
    public function seedEccleshallArea()
    {
        // Eccleshall, Staffordshire polygon coordinates (approximated)
        $eccleshallPolygon = [
            [52.8508, -2.2519], // North point
            [52.8511, -2.2483], // Northeast
            [52.8498, -2.2441], // East
            [52.8479, -2.2424], // Southeast
            [52.8463, -2.2438], // South
            [52.8456, -2.2467], // Southwest
            [52.8464, -2.2503], // West
            [52.8485, -2.2524], // Northwest
            [52.8508, -2.2519]  // Close polygon
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
        $addresses = $this->getEccleshallAddresses();
        $binTypes = \App\Http\Controllers\BinScheduleController::getDefaultBinTypes();
        $statuses = ['Scheduled', 'Pending', 'Completed'];
        
        $collections = $this->getExistingCollections();
        $startId = $this->getNextCollectionId($collections);

        // Generate collections over 2 weeks
        $startDate = new \DateTime();
        $collections = [];

        for ($i = 0; $i < 20; $i++) {
            // Random day within 2 weeks
            $randomDays = rand(0, 13);
            $collectionDate = clone $startDate;
            $collectionDate->modify("+{$randomDays} days");

            // Random time
            $times = ['08:00', '09:30', '11:00', '12:30', '14:00', '15:30'];
            $randomTime = $times[array_rand($times)];

            // Random address and bin type
            $address = $addresses[array_rand($addresses)];
            $binType = $binTypes[array_rand($binTypes)];
            $status = $statuses[array_rand($statuses)];

            $collection = [
                'id' => $startId + $i,
                'customer_name' => $this->generateCustomerName(),
                'phone' => $this->generatePhoneNumber(),
                'address' => $address,
                'bin_type' => $binType,
                'collection_date' => $collectionDate->format('Y-m-d'),
                'collection_time' => $randomTime,
                'status' => $status,
                'notes' => $this->generateRandomNotes(),
                'created_at' => date('Y-m-d H:i:s')
            ];

            $collections[] = $collection;
        }

        // Save collections
        $this->saveCollections($collections);

        return $collections;
    }

    /**
     * Get Eccleshall addresses (real streets)
     */
    private function getEccleshallAddresses(): array
    {
        return [
            '12 High Street, Eccleshall, Staffordshire, ST21 6BZ',
            '45 Castle Street, Eccleshall, Staffordshire, ST21 6DF',
            '23 Stafford Street, Eccleshall, Staffordshire, ST21 6BH',
            '67 Newport Road, Eccleshall, Staffordshire, ST21 6JB',
            '8 Stone Road, Eccleshall, Staffordshire, ST21 6JF',
            '34 Buchanan Avenue, Eccleshall, Staffordshire, ST21 6JL',
            '56 Badgers Croft, Eccleshall, Staffordshire, ST21 6LB',
            '19 Crooked Bridge Road, Eccleshall, Staffordshire, ST21 6LE',
            '41 Millfield Gardens, Eccleshall, Staffordshire, ST21 6LG',
            '73 Pinfold Lane, Eccleshall, Staffordshire, ST21 6LH',
            '25 Sheepmarket, Eccleshall, Staffordshire, ST21 6BW',
            '52 The Limes, Eccleshall, Staffordshire, ST21 6LN',
            '14 Violets Way, Eccleshall, Staffordshire, ST21 6LP',
            '38 Weavers Close, Eccleshall, Staffordshire, ST21 6LQ',
            '61 Woods Lane, Eccleshall, Staffordshire, ST21 6LR',
            '27 Bishop\'s Court, Eccleshall, Staffordshire, ST21 6LS',
            '49 Ferndale Close, Eccleshall, Staffordshire, ST21 6LT',
            '15 Guildhall Lane, Eccleshall, Staffordshire, ST21 6LU',
            '72 Offley Brook, Eccleshall, Staffordshire, ST21 6LV',
            '33 School Lane, Eccleshall, Staffordshire, ST21 6LW'
        ];
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
     * Get existing collections
     */
    private function getExistingCollections(): array
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
    private function saveCollections(array $newCollections): void
    {
        $existingCollections = $this->getExistingCollections();
        $allCollections = array_merge($existingCollections, $newCollections);
        
        // Sanitize all string data to handle apostrophes and special characters
        $allCollections = $this->sanitizeCollectionsData($allCollections);
        
        $storagePath = storage_path('app/collections.json');
        file_put_contents($storagePath, json_encode($allCollections, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
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
     * Delete all collections
     */
    public function deleteAllCollections()
    {
        $storagePath = storage_path('app/collections.json');
        if (file_exists($storagePath)) {
            unlink($storagePath);
        }
        return true;
    }

    /**
     * Delete all areas
     */
    public function deleteAllAreas()
    {
        $storagePath = storage_path('app/allowed_areas.json');
        if (file_exists($storagePath)) {
            unlink($storagePath);
        }
        return true;
    }

    /**
     * Get data summary
     */
    public function getDataSummary()
    {
        $areas = [];
        $collections = [];
        
        $areasPath = storage_path('app/allowed_areas.json');
        if (file_exists($areasPath)) {
            $data = file_get_contents($areasPath);
            $areas = json_decode($data, true) ?: [];
        }
        
        $collectionsPath = storage_path('app/collections.json');
        if (file_exists($collectionsPath)) {
            $data = file_get_contents($collectionsPath);
            $collections = json_decode($data, true) ?: [];
        }
        
        return response()->json([
            'areas' => count($areas),
            'collections' => count($collections),
            'active_areas' => count(array_filter($areas, fn($a) => $a['active'] ?? false)),
            'recent_collections' => count(array_filter($collections, function($c) {
                $date = new \DateTime($c['collection_date'] ?? 'now');
                $now = new \DateTime();
                $diff = $now->diff($date)->days;
                return $diff <= 14;
            }))
        ]);
    }
}
