<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if admin user already exists
        $adminUser = User::where('email', 'admin@example.com')->first();

        if ($adminUser) {
            $this->command->info('Admin user already exists. Updating...');
            
            // Update existing user
            $adminUser->update([
                'name' => 'Admin User',
                'email_verified_at' => now(),
            ]);
            
            // Disable 2FA if enabled
            $adminUser->two_factor_secret = null;
            $adminUser->two_factor_recovery_codes = null;
            $adminUser->two_factor_confirmed_at = null;
            $adminUser->save();
        } else {
            $this->command->info('Creating admin user...');
            
            // Create new admin user without 2FA
            $adminUser = User::factory()->withoutTwoFactor()->create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'email_verified_at' => now(),
            ]);
        }

        // Assign super-admin role
        $adminUser->syncRoles(['super-admin']);

        $this->command->info('✅ Admin user ready!');
        $this->command->info("   Email: {$adminUser->email}");
        $this->command->info("   Password: password");
        $this->command->info("   Roles: " . $adminUser->roles->pluck('name')->implode(', '));
        $this->command->info("   Permissions: " . $adminUser->getAllPermissions()->count());
    }
}
