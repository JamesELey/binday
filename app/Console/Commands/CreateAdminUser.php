<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\User;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create-user 
                            {--email= : Email address for the admin user} 
                            {--password= : Password for the admin user}
                            {--name= : Full name for the admin user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an admin user for production deployment';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->option('email') ?: $this->ask('Enter admin email address', 'admin@binday.com');
        $name = $this->option('name') ?: $this->ask('Enter admin full name', 'System Administrator');
        $password = $this->option('password') ?: $this->secret('Enter admin password (default: password123)') ?: 'password123';

        // Check if user already exists
        if (User::where('email', $email)->exists()) {
            $this->error("User with email '{$email}' already exists!");
            
            if ($this->confirm('Do you want to reset the password for this user?')) {
                $user = User::where('email', $email)->first();
                $user->update([
                    'password' => $password,
                    'role' => 'admin',
                    'active' => true
                ]);
                $this->info("Password updated for existing user: {$email}");
                return 0;
            }
            
            return 1;
        }

        // Create new admin user
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password, // Will be automatically hashed by the model
            'role' => 'admin',
            'active' => true,
            'phone' => null,
            'address' => 'Production Server',
        ]);

        $this->info('✅ Admin user created successfully!');
        $this->line('');
        $this->line("📧 Email: {$user->email}");
        $this->line("👤 Name: {$user->name}");
        $this->line("🔐 Password: {$password}");
        $this->line("👑 Role: {$user->role}");
        $this->line('');
        $this->warn('⚠️  Please save these credentials and change the password after first login!');

        return 0;
    }
}