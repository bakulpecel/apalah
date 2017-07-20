<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class LessonsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker\Factory::create();

        for ($i = 0; $i < 100; $i++) { 
            DB::table('lessons')->insert([
                'title'           => $title = $faker->sentence(),
                'slug'            => str_replace(' ', '-', strtolower($title)),
                'summary'         => $faker->paragraph(),
                'thumbnail'       => time() . 'jpg',
                'url_source_code' => 'http://github.com',
                'type'            => rand(0, 1),
                'status'          => rand(0, 1),
                'user_id'         => rand(1, 2),
                'published_at'    => Carbon::now('Asia/Jakarta')->toDateTimeString(),
            ]);
        }
    }
}
