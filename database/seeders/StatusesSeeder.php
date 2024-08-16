<?php

namespace Database\Seeders;

use App\Models\order_status;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class StatusesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('order_statuses')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        order_status::create([
            "name" => "new",
        ]);
        order_status::create([
            "name" => "working",
        ]);
        order_status::create([
            "name" => "ended",
        ]);
        order_status::create([
            "name" => "under delivery",
        ]);
        order_status::create([
            "name" => "cancelled",
        ]);
        order_status::create([
            "name" => "done",
        ]);
    }
}
