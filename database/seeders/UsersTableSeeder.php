<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        # Global admin
        User::create([
            'first_name' => "Admin",
            'last_name' => "Global",
            'uuid' => Str::uuid(),
            'is_admin' => 1,
            'email' => "admin@buckhill.co.uk",
            'password' => bcrypt("admin")
        ]);

        # Normal user
        for($i = 1; $i <= 10; $i++) {
            User::create([
                'first_name' => "User",
                'last_name' => "$i",
                'uuid' => Str::uuid(),
                'is_admin' => 0,
                'email' => "user$i@buckhill.co.uk",
                'password' => bcrypt("userpassword")
            ]);
        }

    }
}
