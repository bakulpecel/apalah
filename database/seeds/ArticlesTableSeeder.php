<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ArticlesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker\Factory::create();

        for ($i = 0; $i < 10; $i++) { 
            DB::table('articles')->insert([
                'title'        => $title = $faker->sentence(),
                'slug'         => str_slug($title . '-' . str_random(8)),
                'content'      => $faker->text($maxNbChars = 1000),
                'thumbnail'    => time() . 'jpg',
                'status'       => rand(0, 1),
                'user_id'      => rand(1, 3),
                'published_at' => Carbon::now('Asia/Jakarta')->toDateTimeString(),
            ]);
        }
    }
}
