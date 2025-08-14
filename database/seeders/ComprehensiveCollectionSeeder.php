<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Collection;
use App\Area;
use App\User;
use Carbon\Carbon;

class ComprehensiveCollectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌱 Generating comprehensive collection data...');
        
        // Get all areas and users
        $areas = Area::where('type', 'polygon')->get();
        $customers = User::where('role', 'customer')->get();
        
        if ($areas->isEmpty()) {
            $this->command->warn('No polygon areas found. Please run AreaSeeder first.');
            return;
        }
        
        // Clear existing collections
        Collection::truncate();
        $this->command->info('🗑️ Cleared existing collection data');
        
        $totalCollections = 0;
        
        // Generate collections for the next 7 days
        for ($dayOffset = 0; $dayOffset < 7; $dayOffset++) {
            $date = Carbon::now()->addDays($dayOffset);
            
            $this->command->info("📅 Generating collections for {$date->format('Y-m-d')} ({$date->format('l')})");
            
            foreach ($areas as $area) {
                $collectionsForArea = $this->generateCollectionsForArea($area, $date, $customers);
                $totalCollections += $collectionsForArea;
                
                $this->command->info("   📍 {$area->name}: {$collectionsForArea} collections");
            }
        }
        
        $this->command->info("✅ Generated {$totalCollections} total collections across 7 days");
        $this->command->info("📊 Average per day: " . round($totalCollections / 7, 1) . " collections");
        $this->command->info("🎯 Route planner now has realistic data for testing!");
    }
    
    /**
     * Generate collections for a specific area and date
     */
    private function generateCollectionsForArea(Area $area, Carbon $date, $customers)
    {
        // Generate 12-18 collections per area per day for good route planning
        $collectionsCount = rand(12, 18);
        $created = 0;
        
        $binTypes = ['Food', 'Recycling', 'Garden', 'Residual Waste'];
        $collectionTimes = ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00'];
        $statuses = ['pending', 'confirmed', 'collected'];
        
        // Weight the statuses based on date
        if ($date->isToday()) {
            $statusWeights = ['pending' => 40, 'confirmed' => 50, 'collected' => 10];
        } elseif ($date->isFuture()) {
            $statusWeights = ['pending' => 70, 'confirmed' => 30, 'collected' => 0];
        } else {
            $statusWeights = ['pending' => 5, 'confirmed' => 15, 'collected' => 80];
        }
        
        for ($i = 0; $i < $collectionsCount; $i++) {
            // Generate realistic coordinates within the area bounds
            $coordinates = $this->generateCoordinatesInArea($area);
            
            if (!$coordinates) {
                continue; // Skip if we can't generate valid coordinates
            }
            
            // Select a random customer or create a realistic name
            $customer = $customers->random();
            $customerName = $this->generateCustomerName();
            
            // Generate realistic address
            $address = $this->generateAddressForArea($area, $i);
            
            // Select bin type (some areas might prefer certain types)
            $binType = $this->selectBinType($area, $binTypes);
            
            // Select weighted status
            $status = $this->selectWeightedStatus($statusWeights);
            
            // Select time (prefer morning/afternoon for efficiency)
            $collectionTime = $collectionTimes[array_rand($collectionTimes)];
            
            try {
                Collection::create([
                    'customer_name' => $customerName,
                    'customer_email' => $customer ? $customer->email : $this->generateCustomerEmail($customerName),
                    'phone' => $this->generatePhoneNumber(),
                    'address' => $address,
                    'bin_type' => $binType,
                    'collection_date' => $date->format('Y-m-d'),
                    'collection_time' => $collectionTime,
                    'status' => $status,
                    'notes' => $this->generateNotes(),
                    'latitude' => $coordinates['lat'],
                    'longitude' => $coordinates['lng'],
                    'user_id' => $customer ? $customer->id : null,
                    'area_id' => $area->id,
                ]);
                
                $created++;
            } catch (\Exception $e) {
                $this->command->warn("Failed to create collection: " . $e->getMessage());
            }
        }
        
        return $created;
    }
    
    /**
     * Generate random coordinates within an area's polygon bounds
     */
    private function generateCoordinatesInArea(Area $area)
    {
        if (!$area->coordinates || empty($area->coordinates)) {
            return null;
        }
        
        $coordinates = $area->coordinates;
        
        // Find bounding box of the polygon
        $minLat = min(array_column($coordinates, 0));
        $maxLat = max(array_column($coordinates, 0));
        $minLng = min(array_column($coordinates, 1));
        $maxLng = max(array_column($coordinates, 1));
        
        // Try up to 10 times to generate a point inside the polygon
        for ($attempt = 0; $attempt < 10; $attempt++) {
            $lat = $minLat + ($maxLat - $minLat) * mt_rand() / mt_getrandmax();
            $lng = $minLng + ($maxLng - $minLng) * mt_rand() / mt_getrandmax();
            
            if ($this->isPointInPolygon($lat, $lng, $coordinates)) {
                return ['lat' => $lat, 'lng' => $lng];
            }
        }
        
        // If we can't find a point inside, use the center of the bounding box
        return [
            'lat' => ($minLat + $maxLat) / 2,
            'lng' => ($minLng + $maxLng) / 2
        ];
    }
    
    /**
     * Check if a point is inside a polygon using ray casting algorithm
     */
    private function isPointInPolygon($lat, $lng, $polygon)
    {
        $inside = false;
        $j = count($polygon) - 1;
        
        for ($i = 0; $i < count($polygon); $i++) {
            $xi = $polygon[$i][0];
            $yi = $polygon[$i][1];
            $xj = $polygon[$j][0];
            $yj = $polygon[$j][1];
            
            if ((($yi > $lng) != ($yj > $lng)) && ($lat < ($xj - $xi) * ($lng - $yi) / ($yj - $yi) + $xi)) {
                $inside = !$inside;
            }
            $j = $i;
        }
        
        return $inside;
    }
    
    /**
     * Generate realistic customer names
     */
    private function generateCustomerName()
    {
        $firstNames = [
            'James', 'Sarah', 'Michael', 'Emma', 'David', 'Jessica', 'Christopher', 'Ashley',
            'Matthew', 'Amanda', 'Joshua', 'Jennifer', 'Daniel', 'Stephanie', 'Robert', 'Nicole',
            'John', 'Elizabeth', 'Joseph', 'Helen', 'Mark', 'Samantha', 'Paul', 'Michelle',
            'Andrew', 'Lisa', 'Kenneth', 'Angela', 'Steven', 'Kimberly', 'Edward', 'Brenda',
            'Brian', 'Emma', 'Ronald', 'Amy', 'Timothy', 'Anna', 'Jason', 'Rebecca'
        ];
        
        $lastNames = [
            'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis',
            'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson', 'Thomas',
            'Taylor', 'Moore', 'Jackson', 'Martin', 'Lee', 'Perez', 'Thompson', 'White',
            'Harris', 'Sanchez', 'Clark', 'Ramirez', 'Lewis', 'Robinson', 'Walker', 'Young',
            'Allen', 'King', 'Wright', 'Scott', 'Torres', 'Nguyen', 'Hill', 'Flores'
        ];
        
        return $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
    }
    
    /**
     * Generate customer email from name
     */
    private function generateCustomerEmail($name)
    {
        $nameParts = explode(' ', strtolower($name));
        $domains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'btinternet.com'];
        
        return $nameParts[0] . '.' . $nameParts[1] . '@' . $domains[array_rand($domains)];
    }
    
    /**
     * Generate realistic UK phone numbers
     */
    private function generatePhoneNumber()
    {
        $formats = [
            '01785 ' . rand(100000, 999999), // Stafford area code
            '01889 ' . rand(100000, 999999), // Rugeley area code
            '07' . rand(100000000, 999999999), // Mobile
        ];
        
        return $formats[array_rand($formats)];
    }
    
    /**
     * Generate realistic addresses for each area
     */
    private function generateAddressForArea(Area $area, $index)
    {
        $streetNames = [
            'High Street', 'Church Lane', 'Mill Lane', 'Castle Street', 'Station Road',
            'Manor Road', 'Victoria Road', 'Queens Road', 'King Street', 'Albert Road',
            'Park Road', 'School Lane', 'Orchard Close', 'Meadow View', 'Oak Avenue',
            'Elm Grove', 'Cedar Close', 'Willow Way', 'Birch Road', 'Ash Lane'
        ];
        
        $houseNumber = rand(1, 200);
        $streetName = $streetNames[array_rand($streetNames)];
        
        // Add area-specific postcode
        $postcodes = [
            'Eccleshall Town Centre' => 'ST21 6B',
            'Eccleshall Residential North' => 'ST21 6D',
            'Eccleshall Rural South' => 'ST21 6H',
            'Stone Road Area' => 'ST21 6A',
            'Castle Development' => 'ST21 6E',
            'Stafford Extended Area' => 'ST16 2A',
            'Eccleshall West Village' => 'ST21 6G',
        ];
        
        $postcode = $postcodes[$area->name] ?? 'ST21 6X';
        $postcode .= chr(65 + ($index % 26)); // Add random letter
        
        return "{$houseNumber} {$streetName}, {$area->name}, {$postcode}";
    }
    
    /**
     * Select bin type based on area preferences
     */
    private function selectBinType($area, $binTypes)
    {
        // Different areas might have different bin type distributions
        $weights = [
            'Food' => 30,
            'Recycling' => 25,
            'Garden' => 25,
            'Residual Waste' => 20
        ];
        
        // Adjust weights based on area name
        if (strpos($area->name, 'Rural') !== false || strpos($area->name, 'Garden') !== false) {
            $weights['Garden'] = 40;
            $weights['Food'] = 35;
        } elseif (strpos($area->name, 'Town') !== false || strpos($area->name, 'Centre') !== false) {
            $weights['Recycling'] = 35;
            $weights['Residual Waste'] = 30;
        }
        
        return $this->selectWeightedOption($binTypes, $weights);
    }
    
    /**
     * Select status based on weights
     */
    private function selectWeightedStatus($weights)
    {
        $statuses = array_keys($weights);
        return $this->selectWeightedOption($statuses, $weights);
    }
    
    /**
     * Select weighted option from array
     */
    private function selectWeightedOption($options, $weights)
    {
        $totalWeight = array_sum($weights);
        $random = mt_rand(1, $totalWeight);
        $currentWeight = 0;
        
        foreach ($options as $option) {
            $currentWeight += $weights[$option] ?? 0;
            if ($random <= $currentWeight) {
                return $option;
            }
        }
        
        return $options[0]; // Fallback
    }
    
    /**
     * Generate random notes
     */
    private function generateNotes()
    {
        $notes = [
            '',
            'Leave bins at front gate',
            'Access via side gate',
            'Ring doorbell if no bins visible',
            'Large garden waste this week',
            'Please collect early if possible',
            'Bins located at rear of property',
            'Shared access with number ' . rand(1, 200),
            'Extra recycling this week',
            'Collection preferred after 9am',
        ];
        
        // 60% chance of no notes, 40% chance of random note
        return mt_rand(1, 100) <= 60 ? '' : $notes[array_rand($notes)];
    }
}
