<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class LessonPartsTableSeeder extends Seeder
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
            DB::table('lesson_parts')->insert([
                'lesson_id'  => rand(1, 10),
                'title'      => $title = $faker->sentence(),
                'slug'       => str_slug($title . '-' . str_random(8)),
                'url_video'  => 'https://www.youtube.com/watch?v=Ojs1t-wv0wA',
                'created_at' => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                'updated_at' => Carbon::now('Asia/Jakarta')->toDateTimeString(),
            ]);
        }
    }
}
