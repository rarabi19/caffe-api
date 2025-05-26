<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    // BAGIAN LOGIN🚀 
    public function run(): void
    {
        $Users = [
            [
                'nama' => 'zhr',
                'username' => 'Pemilik',
                'email' => 'hanivslbla@gmail.com',
                'password' => Hash::make('zahra123'),
                'role' => 'admin',
            ],
            [
                'nama' => 'Joana',
                'username' => 'Mr J',
                'email' => 'joan@manusia.com',
                'password' => Hash::make('password123'),
                'role' => 'kasir',
            ]
        ];

        foreach ($Users as $user) {
            User::create($user);
        }
    }    
}
