<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker =  Faker\Factory::create();

        DB::table('users')->insert([
            [
                'name'       => 'Admin',
                'username'   => 'admin',
                'email'      => 'admin@mail.com',
                'password'   => bcrypt('123456'),
                'phone_number' => $faker->e164PhoneNumber,
                'role_id'    => 1,
                'active'     => 1,
                'created_at' => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                'updated_at' => Carbon::now('Asia/Jakarta')->toDateTimeString(),
            ],
            [
                'name'       => 'Educator',
                'username'   => 'educator',
                'email'      => 'educator@mail.com',
                'password'   => bcrypt('123456'),
                'phone_number' => $faker->e164PhoneNumber,
                'role_id'    => 2,
                'active'     => 1,
                'created_at' => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                'updated_at' => Carbon::now('Asia/Jakarta')->toDateTimeString(),
            ],
            [
                'name'       => 'Moderator',
                'username'   => 'moderator',
                'email'      => 'moderator@mail.com',
                'password'   => bcrypt('123456'),
                'phone_number' => $faker->e164PhoneNumber,
                'role_id'    => 3,
                'active'     => 1,
                'created_at' => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                'updated_at' => Carbon::now('Asia/Jakarta')->toDateTimeString(),
            ],
            [
                'name'       => 'User',
                'username'   => 'user',
                'email'      => 'user@mail.com',
                'password'   => bcrypt('123456'),
                'phone_number' => $faker->e164PhoneNumber,
                'role_id'    => 4,
                'active'     => 1,
                'created_at' => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                'updated_at' => Carbon::now('Asia/Jakarta')->toDateTimeString(),
            ],
        ]);
    }
}
