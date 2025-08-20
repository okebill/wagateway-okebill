<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Create or update admin user
        User::updateOrCreate(
            ['email' => 'adminwa@localhost.com'],
            [
                'name' => 'Administrator',
                'email' => 'adminwa@localhost.com',
                'password' => Hash::make('adminwa'),
                'role' => 'admin',
                'is_approved' => true,
                'approved_at' => Carbon::now(),
                'status' => 'active',
                'limit_device' => 999, // Unlimited devices for admin
                'account_expires_at' => null, // Never expires
                'email_verified_at' => Carbon::now(),
            ]
        );

        $this->command->info('✅ Admin user created successfully!');
        $this->command->line('');
        $this->command->line('📋 Admin Credentials:');
        $this->command->line('   Email: adminwa@localhost.com');
        $this->command->line('   Username: adminwa (for display)');
        $this->command->line('   Password: adminwa');
        $this->command->line('   Role: admin');
        $this->command->line('');
        $this->command->warn('⚠️  SECURITY: Change admin password after first login!');
    }
}
