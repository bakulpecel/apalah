<?php

use Illuminate\Database\Seeder;

class LessonCategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 0; $i < 1000; $i++) { 
            DB::table('lesson_category')->insert([
                'lesson_id'   => rand(1, 100),
                'category_id' => rand(1, 100),
            ]);
        }
    }
}
