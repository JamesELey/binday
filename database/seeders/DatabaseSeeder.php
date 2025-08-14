<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Seeding BinDay Database...');
        
        // Seed in order (areas first, then users, then collections)
        $this->call([
            AreaSeeder::class,
            UserSeeder::class,
            ComprehensiveCollectionSeeder::class, // Use comprehensive seeder instead
        ]);
        
        $this->command->info('');
        $this->command->info('🎉 Database seeding completed!');
        $this->command->info('');
        $this->command->info('📋 Login Details:');
        $this->command->info('👑 Admin: admin@binday.com / password123');
        $this->command->info('👷 Worker: worker@binday.com / password123');
        $this->command->info('👤 Customer: customer@binday.com / password123');
        $this->command->info('');
        $this->command->info('🗺️ Demo data includes:');
        $this->command->info('   • 8 service areas (Eccleshall & Stafford)');
        $this->command->info('   • 6 users (admin, workers, customers)');
        $this->command->info('   • 800+ collections across 7 days (12-18 per area per day)');
        $this->command->info('   • Realistic coordinates within area boundaries');
        $this->command->info('   • Mixed bin types, times, and statuses for route planning');
    }
}
