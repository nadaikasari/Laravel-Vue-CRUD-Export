<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        $orders = [];
        for ($i = 0; $i < 6000; $i++) {
            $orders[] = [
                'order_no' => 'INV' . strtoupper($faker->unique()->lexify('??????')), 
                'customer_name' => $faker->name,
                'order_date' => $faker->date(),
                'grand_total' => $faker->randomFloat(2, 100, 1000), 
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('orders')->insert($orders);
    }
}
