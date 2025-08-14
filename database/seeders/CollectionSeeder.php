<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Collection;
use App\Area;
use App\User;
use Carbon\Carbon;

class CollectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some areas and users for relationships
        $areas = Area::all();
        $customers = User::where('role', 'customer')->get();
        
        if ($areas->isEmpty()) {
            $this->command->warn('No areas found. Run AreaSeeder first.');
            return;
        }

        $collections = [
            // Current week collections
            [
                'customer_name' => 'Jane Customer',
                'customer_email' => 'customer@binday.com',
                'phone' => '01785 345678',
                'address' => '123 High Street, Eccleshall, ST21 6BZ',
                'bin_type' => 'Food',
                'collection_date' => Carbon::now()->startOfWeek()->addDays(1), // Tuesday
                'collection_time' => '09:00',
                'status' => 'confirmed',
                'notes' => 'Leave bins at front gate',
                'latitude' => 52.8586,
                'longitude' => -2.2524,
            ],
            [
                'customer_name' => 'Alice Smith',
                'customer_email' => 'alice@example.com',
                'phone' => '01785 456789',
                'address' => '45 Castle Street, Eccleshall, ST21 6DF',
                'bin_type' => 'Recycling',
                'collection_date' => Carbon::now()->startOfWeek()->addDays(2), // Wednesday
                'collection_time' => '10:30',
                'status' => 'pending',
                'notes' => null,
                'latitude' => 52.8590,
                'longitude' => -2.2520,
            ],
            [
                'customer_name' => 'Bob Johnson',
                'customer_email' => 'bob@example.com',
                'phone' => '01785 567890',
                'address' => '78 Stone Road, Eccleshall, ST21 6ET',
                'bin_type' => 'Garden',
                'collection_date' => Carbon::now()->startOfWeek()->addDays(3), // Thursday
                'collection_time' => '14:00',
                'status' => 'confirmed',
                'notes' => 'Heavy load - garden clearance',
                'latitude' => 52.8580,
                'longitude' => -2.2530,
            ],

            // Next week collections
            [
                'customer_name' => 'Sarah Wilson',
                'customer_email' => 'sarah@example.com',
                'phone' => '01785 678901',
                'address' => '12 Church Lane, Eccleshall, ST21 6BW',
                'bin_type' => 'Food',
                'collection_date' => Carbon::now()->addWeek()->startOfWeek()->addDays(1), // Next Tuesday
                'collection_time' => '08:30',
                'status' => 'pending',
                'notes' => 'Ring doorbell if no answer',
                'latitude' => 52.8585,
                'longitude' => -2.2525,
            ],
            [
                'customer_name' => 'Mike Brown',
                'customer_email' => 'mike@example.com',
                'phone' => '01785 789012',
                'address' => '67 New Street, Eccleshall, ST21 6AA',
                'bin_type' => 'Recycling',
                'collection_date' => Carbon::now()->addWeek()->startOfWeek()->addDays(4), // Next Friday
                'collection_time' => '11:00',
                'status' => 'pending',
                'notes' => null,
                'latitude' => 52.8575,
                'longitude' => -2.2535,
            ],

            // Some completed collections (last week)
            [
                'customer_name' => 'Emma Davis',
                'customer_email' => 'emma@example.com',
                'phone' => '01785 890123',
                'address' => '34 Market Square, Eccleshall, ST21 6DT',
                'bin_type' => 'Food',
                'collection_date' => Carbon::now()->subWeek()->startOfWeek()->addDays(2), // Last Wednesday
                'collection_time' => '09:45',
                'status' => 'collected',
                'notes' => 'Completed successfully',
                'latitude' => 52.8588,
                'longitude' => -2.2522,
            ],
            [
                'customer_name' => 'Tom Harris',
                'customer_email' => 'tom@example.com',
                'phone' => '01785 901234',
                'address' => '89 Victoria Road, Eccleshall, ST21 6EU',
                'bin_type' => 'Garden',
                'collection_date' => Carbon::now()->subWeek()->startOfWeek()->addDays(4), // Last Friday
                'collection_time' => '15:30',
                'status' => 'collected',
                'notes' => 'Large amount collected',
                'latitude' => 52.8595,
                'longitude' => -2.2515,
            ],

            // Future collections (two weeks ahead)
            [
                'customer_name' => 'Lisa Taylor',
                'customer_email' => 'lisa@example.com',
                'phone' => '01785 012345',
                'address' => '56 Mill Lane, Eccleshall, ST21 6EW',
                'bin_type' => 'Food',
                'collection_date' => Carbon::now()->addWeeks(2)->startOfWeek()->addDays(1), // Two weeks Tuesday
                'collection_time' => '10:00',
                'status' => 'pending',
                'notes' => 'Weekly food waste collection',
                'latitude' => 52.8582,
                'longitude' => -2.2528,
            ],

            // Stafford area collection
            [
                'customer_name' => 'Carol Wilson',
                'customer_email' => 'carol@example.com',
                'phone' => '01785 678901',
                'address' => '45 County Road, Stafford, ST16 2AA',
                'bin_type' => 'Recycling',
                'collection_date' => Carbon::now()->startOfWeek()->addDays(5), // Friday
                'collection_time' => '13:00',
                'status' => 'confirmed',
                'notes' => 'Stafford area collection',
                'latitude' => 52.8068,
                'longitude' => -2.1167,
            ],
        ];

        foreach ($collections as $collectionData) {
            // Find user if exists
            $user = $customers->firstWhere('email', $collectionData['customer_email']);
            if ($user) {
                $collectionData['user_id'] = $user->id;
            }

            // Find appropriate area based on address
            $area = $areas->first(function ($area) use ($collectionData) {
                return $area->containsAddress($collectionData['address']);
            });
            
            if ($area) {
                $collectionData['area_id'] = $area->id;
            }

            Collection::firstOrCreate(
                [
                    'customer_email' => $collectionData['customer_email'],
                    'collection_date' => $collectionData['collection_date'],
                    'bin_type' => $collectionData['bin_type'],
                ],
                $collectionData
            );
        }

        $this->command->info('Created ' . count($collections) . ' demo collections');
        $this->command->info('Collections span current week, next week, and some completed ones');
    }
}