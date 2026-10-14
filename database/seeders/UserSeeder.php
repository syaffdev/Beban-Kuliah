<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Test User',
                'email' => 'test@gmail.com',
            ],
            [
                'name' => 'Andi Pratama',
                'email' => 'andi@gmail.com',
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'budi@gmail.com',
            ],
            [
                'name' => 'Citra Lestari',
                'email' => 'citra@gmail.com',
            ],
            [
                'name' => 'Dimas Saputra',
                'email' => 'dimas@gmail.com',
            ],
            [
                'name' => 'Eka Putri',
                'email' => 'eka@gmail.com',
            ],
            [
                'name' => 'Fajar Ramadhan',
                'email' => 'fajar@gmail.com',
            ],
            [
                'name' => 'Gita Maharani',
                'email' => 'gita@gmail.com',
            ],
            [
                'name' => 'Hendra Wijaya',
                'email' => 'hendra@gmail.com',
            ],
            [
                'name' => 'Intan Permata',
                'email' => 'intan@gmail.com',
            ],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password123'),
                ]
            );
        }

        $this->command->info(
            '10 akun pengguna berhasil disiapkan.'
        );
    }
}
