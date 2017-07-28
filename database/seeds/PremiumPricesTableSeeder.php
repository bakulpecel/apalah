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
        $perMonth = 12000;

        for ($i = 0; $i < 12; $i++) { 
            DB::table('premium_prices')->insert([
                'month' => $i + 1,
                'price' => $perMonth * ($i + 1),
            ]);
        }
    }
}
