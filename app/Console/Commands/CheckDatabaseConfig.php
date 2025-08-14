<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class CheckDatabaseConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:check-config {--fix : Attempt to fix common configuration issues}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check database configuration and connection';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Checking Database Configuration...');
        $this->line('');

        // Check if .env file exists
        $envPath = base_path('.env');
        if (!file_exists($envPath)) {
            $this->error('❌ .env file not found!');
            
            if ($this->option('fix')) {
                $this->createEnvFile();
            } else {
                $this->warn('💡 Run with --fix to create .env file from template');
            }
            return 1;
        }

        $this->info('✅ .env file exists');

        // Check database configuration values
        $dbConfig = [
            'DB_CONNECTION' => config('database.default'),
            'DB_HOST' => config('database.connections.mysql.host'),
            'DB_PORT' => config('database.connections.mysql.port'),
            'DB_DATABASE' => config('database.connections.mysql.database'),
            'DB_USERNAME' => config('database.connections.mysql.username'),
            'DB_PASSWORD' => config('database.connections.mysql.password') ? '***' : 'NOT SET',
        ];

        $this->info('📋 Current Database Configuration:');
        foreach ($dbConfig as $key => $value) {
            $status = ($key === 'DB_PASSWORD' && $value === 'NOT SET') || empty($value) ? '❌' : '✅';
            $this->line("   {$status} {$key}: {$value}");
        }

        // Test database connection
        $this->line('');
        $this->info('🔗 Testing Database Connection...');
        
        try {
            $pdo = DB::connection()->getPdo();
            $this->info('✅ Database connection successful!');
            
            // Get database info
            $dbName = DB::connection()->getDatabaseName();
            $this->line("   📊 Connected to database: {$dbName}");
            
            // Test basic queries
            $tables = DB::select("SHOW TABLES");
            $this->line("   📋 Tables found: " . count($tables));
            
            // Check for users table specifically
            $userTable = collect($tables)->first(function ($table) {
                $tableName = array_values((array) $table)[0];
                return $tableName === 'users';
            });
            
            if ($userTable) {
                $userCount = DB::table('users')->count();
                $this->line("   👥 Users in database: {$userCount}");
                
                if ($userCount === 0) {
                    $this->warn('⚠️  No users found - you may need to run seeding');
                    if ($this->option('fix')) {
                        $this->call('production:seed');
                    }
                }
            } else {
                $this->warn('⚠️  Users table not found - you may need to run migrations');
                if ($this->option('fix')) {
                    $this->call('migrate', ['--force' => true]);
                }
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Database connection failed!');
            $this->error('Error: ' . $e->getMessage());
            
            if ($this->option('fix')) {
                $this->attemptDatabaseFix();
            } else {
                $this->showConnectionHelp();
            }
            
            return 1;
        }

        $this->line('');
        $this->info('🎉 Database configuration check complete!');
        return 0;
    }

    private function createEnvFile()
    {
        $this->info('📝 Creating .env file from production template...');
        
        $templatePath = base_path('production.env.example');
        $envPath = base_path('.env');
        
        if (file_exists($templatePath)) {
            copy($templatePath, $envPath);
            $this->info('✅ .env file created from production.env.example');
            $this->warn('⚠️  You need to edit .env and configure your database settings!');
            $this->line('   Required: DB_DATABASE, DB_USERNAME, DB_PASSWORD');
        } else {
            $this->error('❌ production.env.example template not found');
        }
    }

    private function attemptDatabaseFix()
    {
        $this->info('🔧 Attempting to fix database configuration...');
        
        // Clear config cache in case it's cached incorrectly
        $this->call('config:clear');
        
        // Try connection again
        try {
            $pdo = DB::connection()->getPdo();
            $this->info('✅ Connection now working after clearing cache!');
        } catch (\Exception $e) {
            $this->error('❌ Still cannot connect to database');
            $this->showConnectionHelp();
        }
    }

    private function showConnectionHelp()
    {
        $this->line('');
        $this->warn('💡 Database Connection Troubleshooting:');
        $this->line('');
        $this->line('1. Check your .env file has correct database credentials:');
        $this->line('   DB_CONNECTION=mysql');
        $this->line('   DB_HOST=127.0.0.1');
        $this->line('   DB_PORT=3306');
        $this->line('   DB_DATABASE=binday');
        $this->line('   DB_USERNAME=your_username');
        $this->line('   DB_PASSWORD=your_password');
        $this->line('');
        $this->line('2. Ensure MariaDB/MySQL service is running:');
        $this->line('   systemctl status mariadb');
        $this->line('');
        $this->line('3. Test connection manually:');
        $this->line('   mysql -u your_username -p your_database');
        $this->line('');
        $this->line('4. Check if database exists:');
        $this->line('   mysql -u root -p -e "SHOW DATABASES;"');
    }
}