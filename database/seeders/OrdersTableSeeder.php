<?php

namespace Database\Seeders;

use App\Models\Order;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class OrdersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        # Create seeder for orders using factory
        for($i = 1; $i <= 100; $i++) {
            Order::factory()->create();
        }
    }
}
