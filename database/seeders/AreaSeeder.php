<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Area;

class AreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $areas = [
            [
                'name' => 'Eccleshall Town Centre',
                'description' => 'Central Eccleshall area including High Street and surrounding residential streets',
                'postcodes' => 'ST21 6BZ, ST21 6DF, ST21 6DT, ST21 6BW',
                'active' => true,
                'type' => 'postcode',
                'bin_types' => ['Food', 'Recycling', 'Garden'],
            ],
            [
                'name' => 'Eccleshall Residential North',
                'description' => 'Northern residential areas of Eccleshall including newer developments',
                'postcodes' => 'ST21 6ET, ST21 6EU, ST21 6EW, ST21 6EX',
                'active' => true,
                'type' => 'postcode',
                'bin_types' => ['Food', 'Recycling', 'Garden'],
            ],
            [
                'name' => 'Eccleshall Rural South',
                'description' => 'Southern rural areas and farms around Eccleshall',
                'postcodes' => 'ST21 6AA, ST21 6AB, ST21 6AC, ST21 6AD',
                'active' => true,
                'type' => 'postcode',
                'bin_types' => ['Food', 'Garden'], // No recycling for rural areas
            ],
            [
                'name' => 'Stone Road Area',
                'description' => 'Stone Road and connecting residential streets',
                'postcodes' => 'ST21 6AE, ST21 6AF, ST21 6AG, ST21 6AH',
                'active' => true,
                'type' => 'postcode',
                'bin_types' => ['Food', 'Recycling'],
            ],
            [
                'name' => 'Castle Development',
                'description' => 'New housing development near Eccleshall Castle',
                'postcodes' => 'ST21 6ZA, ST21 6ZB, ST21 6ZC',
                'active' => true,
                'type' => 'postcode',
                'bin_types' => ['Food', 'Recycling', 'Garden'],
            ],
            [
                'name' => 'Stafford Central',
                'description' => 'Central Stafford area - extended coverage for testing',
                'postcodes' => 'ST16 2AA, ST16 2AB, ST16 2AC, ST16 2AD',
                'active' => true,
                'type' => 'postcode',
                'bin_types' => ['Food', 'Recycling', 'Garden'],
            ],
            [
                'name' => 'Test Polygon Area',
                'description' => 'Test area using polygon coordinates',
                'postcodes' => null,
                'active' => false, // Disabled for now
                'type' => 'polygon',
                'coordinates' => [
                    [52.8586, -2.2524], // Eccleshall center
                    [52.8600, -2.2500],
                    [52.8600, -2.2550],
                    [52.8570, -2.2550],
                    [52.8586, -2.2524], // Close polygon
                ],
                'bin_types' => ['Food', 'Recycling'],
            ],
        ];

        foreach ($areas as $areaData) {
            Area::firstOrCreate(
                ['name' => $areaData['name']],
                $areaData
            );
        }

        $this->command->info('Created ' . count($areas) . ' demo areas');
        $this->command->info('Areas cover various postcode zones around Eccleshall and Stafford');
    }
}