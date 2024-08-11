<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\country;

class CountriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('countries')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        country::create([
            "name" => "Kuwait",
        ]);
        country::create([
            "name" => "Syria",
        ]);
        country::create([
            "name" => "Egypt",
        ]);

        DB::statement("ALTER TABLE cities AUTO_INCREMENT =  1");

        $json = file_get_contents(resource_path('cities.json'));
        $data = json_decode($json, true);
        foreach ($data as $item) {
            DB::table('cities')->insert([
                'name' => $item['city'],
                'lat' => $item['lat'],
                'lng' => $item['lng'],
                'country_id' => 1,
            ]);
        }
    }
}
