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
                'postcodes' => null,
                'active' => true,
                'type' => 'polygon',
                'coordinates' => [
                    [-2.2560, 52.8570], // Southwest corner
                    [-2.2480, 52.8570], // Southeast corner  
                    [-2.2480, 52.8610], // Northeast corner
                    [-2.2560, 52.8610], // Northwest corner
                    [-2.2560, 52.8570], // Close polygon
                ],
                'bin_types' => ['Food', 'Recycling', 'Garden'],
            ],
            [
                'name' => 'Eccleshall Residential North',
                'description' => 'Northern residential areas of Eccleshall including newer developments',
                'postcodes' => null,
                'active' => true,
                'type' => 'polygon',
                'coordinates' => [
                    [-2.2600, 52.8610], // Southwest corner
                    [-2.2480, 52.8610], // Southeast corner
                    [-2.2480, 52.8660], // Northeast corner
                    [-2.2600, 52.8660], // Northwest corner
                    [-2.2600, 52.8610], // Close polygon
                ],
                'bin_types' => ['Food', 'Recycling', 'Garden'],
            ],
            [
                'name' => 'Eccleshall Rural South',
                'description' => 'Southern rural areas and farms around Eccleshall',
                'postcodes' => null,
                'active' => true,
                'type' => 'polygon',
                'coordinates' => [
                    [-2.2580, 52.8520], // Southwest corner
                    [-2.2450, 52.8520], // Southeast corner
                    [-2.2450, 52.8570], // Northeast corner
                    [-2.2580, 52.8570], // Northwest corner
                    [-2.2580, 52.8520], // Close polygon
                ],
                'bin_types' => ['Food', 'Garden'], // No recycling for rural areas
            ],
            [
                'name' => 'Stone Road Area',
                'description' => 'Stone Road and connecting residential streets',
                'postcodes' => null,
                'active' => true,
                'type' => 'polygon',
                'coordinates' => [
                    [-2.2450, 52.8570], // Southwest corner
                    [-2.2350, 52.8570], // Southeast corner
                    [-2.2350, 52.8620], // Northeast corner
                    [-2.2450, 52.8620], // Northwest corner
                    [-2.2450, 52.8570], // Close polygon
                ],
                'bin_types' => ['Food', 'Recycling'],
            ],
            [
                'name' => 'Castle Development',
                'description' => 'New housing development near Eccleshall Castle',
                'postcodes' => null,
                'active' => true,
                'type' => 'polygon',
                'coordinates' => [
                    [-2.2630, 52.8580], // Southwest corner
                    [-2.2560, 52.8580], // Southeast corner
                    [-2.2560, 52.8620], // Northeast corner
                    [-2.2630, 52.8620], // Northwest corner
                    [-2.2630, 52.8580], // Close polygon
                ],
                'bin_types' => ['Food', 'Recycling', 'Garden'],
            ],
            [
                'name' => 'Stafford Extended Area',
                'description' => 'Extended coverage area towards Stafford for demonstration',
                'postcodes' => null,
                'active' => true,
                'type' => 'polygon',
                'coordinates' => [
                    [-2.1800, 52.8100], // Southwest corner (towards Stafford)
                    [-2.1600, 52.8100], // Southeast corner
                    [-2.1600, 52.8200], // Northeast corner
                    [-2.1800, 52.8200], // Northwest corner
                    [-2.1800, 52.8100], // Close polygon
                ],
                'bin_types' => ['Food', 'Recycling', 'Garden'],
            ],
            [
                'name' => 'Eccleshall West Village',
                'description' => 'Western village area with mixed residential and small commercial properties',
                'postcodes' => null,
                'active' => true,
                'type' => 'polygon',
                'coordinates' => [
                    [-2.2680, 52.8540], // Southwest corner
                    [-2.2580, 52.8540], // Southeast corner
                    [-2.2580, 52.8590], // Northeast corner
                    [-2.2680, 52.8590], // Northwest corner
                    [-2.2680, 52.8540], // Close polygon
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
        $this->command->info('Areas use polygon coordinates around Eccleshall and Stafford for proper map display');
    }
}