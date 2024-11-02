<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Department::insert([
            [ 'name' => 'IT Support' ],
            [ 'name' => 'Manager' ],
            [ 'name' => 'Marketing' ]
        ]);
    }
}
