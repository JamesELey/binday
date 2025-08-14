<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default admin user
        User::firstOrCreate(
            ['email' => 'admin@binday.com'],
            [
                'name' => 'System Administrator',
                'password' => 'password123',
                'role' => 'admin',
                'active' => true,
                'phone' => '01785 123456',
                'address' => 'Eccleshall, Staffordshire',
            ]
        );

        // Create demo worker user
        User::firstOrCreate(
            ['email' => 'worker@binday.com'],
            [
                'name' => 'John Worker',
                'password' => 'password123',
                'role' => 'worker',
                'active' => true,
                'assigned_area_ids' => [1], // Will be assigned to first area
                'phone' => '01785 234567',
                'address' => 'Eccleshall, Staffordshire',
            ]
        );

        // Create demo customer user
        User::firstOrCreate(
            ['email' => 'customer@binday.com'],
            [
                'name' => 'Jane Customer',
                'password' => 'password123',
                'role' => 'customer',
                'active' => true,
                'phone' => '01785 345678',
                'address' => '123 High Street, Eccleshall, ST21 6BZ',
            ]
        );

        // Create additional demo users
        $demoUsers = [
            [
                'name' => 'Alice Smith',
                'email' => 'alice@example.com',
                'role' => 'customer',
                'address' => '45 Castle Street, Eccleshall, ST21 6DF',
                'phone' => '01785 456789',
            ],
            [
                'name' => 'Bob Johnson',
                'email' => 'bob@example.com',
                'role' => 'customer',
                'address' => '78 Stone Road, Eccleshall, ST21 6ET',
                'phone' => '01785 567890',
            ],
            [
                'name' => 'Carol Wilson',
                'email' => 'carol@example.com',
                'role' => 'worker',
                'assigned_area_ids' => [2],
                'address' => 'Stafford, ST16 2AA',
                'phone' => '01785 678901',
            ],
        ];

        foreach ($demoUsers as $userData) {
            User::firstOrCreate(
                ['email' => $userData['email']],
                array_merge($userData, [
                    'password' => 'password123',
                    'active' => true,
                ])
            );
        }

        $this->command->info('Created demo users:');
        $this->command->info('👑 Admin: admin@binday.com / password123');
        $this->command->info('👷 Worker: worker@binday.com / password123');
        $this->command->info('👤 Customer: customer@binday.com / password123');
    }
}