<?php

namespace Database\Seeders;

use App\Models\products_type;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class ProductsTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('products_types')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        products_type::create([
            "name" => "chicken",
        ]);
        products_type::create([
            "name" => "meat",
        ]);
        products_type::create([
            "name" => "pizza",
        ]);
        products_type::create([
            "name" => "drinks",
        ]);
    }
}
