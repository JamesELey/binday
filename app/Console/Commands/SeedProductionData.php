<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\User;
use App\Area;

class SeedProductionData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'production:seed {--force : Force seeding even if data exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed essential production data (users and areas)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $force = $this->option('force');
        
        $this->info('🌱 Seeding Production Data...');
        $this->line('');

        // Check existing data
        $userCount = User::count();
        $areaCount = Area::count();

        if (!$force && ($userCount > 0 || $areaCount > 0)) {
            $this->warn("⚠️  Data already exists:");
            $this->line("   Users: {$userCount}");
            $this->line("   Areas: {$areaCount}");
            
            if (!$this->confirm('Continue with seeding?')) {
                $this->info('Seeding cancelled.');
                return 0;
            }
        }

        // Seed Users
        $this->info('👥 Seeding Users...');
        $this->seedUsers();

        // Seed Areas  
        $this->info('🏘️ Seeding Areas...');
        $this->seedAreas();

        $this->line('');
        $this->info('✅ Production seeding completed!');
        
        // Display login info
        $this->line('');
        $this->info('🔑 Login Credentials:');
        $this->line('👑 Admin: admin@binday.com / password123');
        $this->line('👷 Worker: worker@binday.com / password123');
        $this->line('👤 Customer: customer@binday.com / password123');
        $this->line('');
        $this->warn('⚠️  Please change default passwords after first login!');

        return 0;
    }

    private function seedUsers()
    {
        $users = [
            [
                'name' => 'System Administrator',
                'email' => 'admin@binday.com',
                'password' => 'password123',
                'role' => 'admin',
                'active' => true,
                'phone' => null,
                'address' => 'Production Server',
            ],
            [
                'name' => 'Production Worker',
                'email' => 'worker@binday.com', 
                'password' => 'password123',
                'role' => 'worker',
                'active' => true,
                'assigned_area_ids' => [1, 2], // Will assign to first areas created
                'phone' => null,
                'address' => 'Production Server',
            ],
            [
                'name' => 'Demo Customer',
                'email' => 'customer@binday.com',
                'password' => 'password123', 
                'role' => 'customer',
                'active' => true,
                'phone' => null,
                'address' => 'Demo Address',
            ],
        ];

        foreach ($users as $userData) {
            User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );
            $this->line("   ✓ {$userData['email']} ({$userData['role']})");
        }
    }

    private function seedAreas()
    {
        $areas = [
            [
                'name' => 'Production Area 1',
                'description' => 'Primary service area for production deployment',
                'postcodes' => null,
                'active' => true,
                'type' => 'polygon',
                'coordinates' => [
                    [-2.2560, 52.8570], // Example coordinates - replace with actual service area
                    [-2.2480, 52.8570],
                    [-2.2480, 52.8610], 
                    [-2.2560, 52.8610],
                    [-2.2560, 52.8570],
                ],
                'bin_types' => ['Food', 'Recycling', 'Garden'],
            ],
            [
                'name' => 'Production Area 2', 
                'description' => 'Secondary service area for extended coverage',
                'postcodes' => null,
                'active' => true,
                'type' => 'polygon',
                'coordinates' => [
                    [-2.2600, 52.8610],
                    [-2.2480, 52.8610],
                    [-2.2480, 52.8660],
                    [-2.2600, 52.8660], 
                    [-2.2600, 52.8610],
                ],
                'bin_types' => ['Food', 'Recycling'],
            ],
        ];

        foreach ($areas as $areaData) {
            Area::firstOrCreate(
                ['name' => $areaData['name']],
                $areaData
            );
            $this->line("   ✓ {$areaData['name']} ({$areaData['type']})");
        }
    }
}