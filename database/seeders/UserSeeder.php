<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        if(app()->isProduction()){
            $this->seedAdmin();
        } else {
            $this->seedDummyUsers();
        }
    }

    public function seedAdmin(){
        User::create([
            'name' => 'Admin Booking Web',
            'nis' => '999999',
            'email' => 'bookingweb@gmail.com',
            'password' => bcrypt('@!bookingweb123'),
            'role' => 'admin',
            'department_id' => 1,
        ]);
    }

    public function seedDummyUsers(){
        User::create([
            'name' => 'Alvin Dimas',
            'nis' => '111111',
            'email' => 'alvin.dimas.praditya@gmail.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'department_id' => 1,
        ]);

        User::create([
            'name' => 'Noctis Yoru',
            'nis' => '987654',
            'email' => 'ncts.yoru@gmail.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'department_id' => 1,
        ]);

        User::create([
            'name' => 'Bimo Satriaji',
            'nis' => '123456',
            'email' => 'bimosatriaji6@gmail.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'department_id' => 1,
        ]);

        User::create([
            'name' => 'Anjing Sedboi',
            'nis' => '999999',
            'email' => 'anjingsedboi@gmail.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'department_id' => 1,
        ]);
    }
}
