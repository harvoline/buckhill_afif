<?php

namespace Database\Seeders;

use App\Models\OrderStatus;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class OrderStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = ['open', 'pending payment', 'paid', 'shipped', 'cancelled'];

        foreach ($statuses as $status) {
            OrderStatus::create([
                'uuid' => Str::uuid(),
                'title' => $status,
            ]);
        }
    }
}
