<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Room;
class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        if(app()->isProduction()) return;
        Room::create(['name' => 'Room A', 'description' => 'Room A untuk A']);
        Room::create(['name' => 'Room B', 'description' => 'Room B untuk B']);
        Room::create(['name' => 'Room C', 'description' => 'Room C untuk C']);
    }
}
