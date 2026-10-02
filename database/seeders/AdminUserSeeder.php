<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Creates the initial administrator from config/admin.php (ADMIN_* in .env) unless it already exists.
     */
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => config('admin.email')],
            [
                'name' => config('admin.name'),
                'password' => config('admin.password'),
                'email_verified_at' => now(),
            ],
        );
    }
}
