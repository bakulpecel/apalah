<?php

use Illuminate\Database\Seeder;

class PremiumPricesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('premium_prices')->insert([
            ['month' => 1, 'price' => 50000],
            ['month' => 3, 'price' => 120000],
            ['month' => 6, 'price' => 180000],
        ]);
    }
}
