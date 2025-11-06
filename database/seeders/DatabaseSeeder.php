<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test user for web interface
        $webUser = User::factory()->create([
            'name' => 'Web User',
            'email' => 'web@test.com',
            'password' => Hash::make('password'),
        ]);

        // Create test user for API with token
        $apiUser = User::factory()->create([
            'name' => 'API User',
            'email' => 'api@test.com',
            'password' => Hash::make('password'),
        ]);

        // Create API token for testing
        $token = $apiUser->createToken('test-token')->plainTextToken;

        // Output credentials for easy access
        $this->command->info('\n====================================');
        $this->command->info('Test Users Created Successfully!');
        $this->command->info('====================================\n');
        
        $this->command->info('WEB LOGIN:');
        $this->command->info('  Email: web@test.com');
        $this->command->info('  Password: password');
        $this->command->info('  URL: http://localhost:8000\n');
        
        $this->command->info('API ACCESS:');
        $this->command->info('  Email: api@test.com');
        $this->command->info('  Token: ' . $token);
        $this->command->info('  Usage: Authorization: Bearer ' . $token . '\n');
        
        $this->command->info('====================================\n');
    }
}
