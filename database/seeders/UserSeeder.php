<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Hailey Bronson',
                'email' => 'haileypbronson@gmail.com',
                'email_verified_at' => null,
                'password' => bcrypt('password123'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]

        ];
        User::insert($users);
    }
}
