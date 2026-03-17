<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = (string) env('APP_ADMIN_EMAIL', 'admin@example.com');
        $password = (string) env('APP_ADMIN_PASSWORD', 'password');

        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => (string) env('APP_ADMIN_NAME', 'Admin'),
                'password' => Hash::make($password),
                'is_admin' => true,
            ],
        );
    }
}
