<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        User::create([
            "name" => "Ahmad",
            "role_id" => 1,
            "email" => "mm@gmail.com",
            "birth_date" => "2001-1-1",
            "password" => bcrypt("111"),
            "phone_no" => "0999",
        ]);
    }
}
