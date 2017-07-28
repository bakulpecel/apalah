<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(RolesTableSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(LessonsTableSeeder::class);
        $this->call(ArticlesTableSeeder::class);
        $this->call(CategoriesTableSeeder::class);
        $this->call(LessonCategoryTableSeeder::class);
        $this->call(ArticleCategoryTableSeeder::class);
        $this->call(LessonPartsTableSeeder::class);
        $this->call(PremiumPricesTableSeeder::class);
    }
}
