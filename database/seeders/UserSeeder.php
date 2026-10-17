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
            ['name' => 'Budi Pratama', 'email' => 'budi.pratama@gmail.com'],
            ['name' => 'Siti Rahmawati', 'email' => 'siti.rahmawati@gmail.com'],
            ['name' => 'Andi Saputra', 'email' => 'andi.saputra@gmail.com'],
            ['name' => 'Dewi Anggraini', 'email' => 'dewi.anggraini@gmail.com'],
            ['name' => 'Rizky Maulana', 'email' => 'rizky.maulana@gmail.com'],
            ['name' => 'Putri Amelia', 'email' => 'putri.amelia@gmail.com'],
            ['name' => 'Fajar Nugraha', 'email' => 'fajar.nugraha@gmail.com'],
            ['name' => 'Nadia Safitri', 'email' => 'nadia.safitri@gmail.com'],
            ['name' => 'Dimas Kurniawan', 'email' => 'dimas.kurniawan@gmail.com'],
            ['name' => 'Aulia Permata', 'email' => 'aulia.permata@gmail.com'],
            ['name' => 'Yoga Firmansyah', 'email' => 'yoga.firmansyah@gmail.com'],
            ['name' => 'Intan Puspita', 'email' => 'intan.puspita@gmail.com'],
            ['name' => 'Arif Setiawan', 'email' => 'arif.setiawan@gmail.com'],
            ['name' => 'Maya Lestari', 'email' => 'maya.lestari@gmail.com'],
            ['name' => 'Reza Firmanda', 'email' => 'reza.firmanda@gmail.com'],
            ['name' => 'Laila Khairunnisa', 'email' => 'laila.khairunnisa@gmail.com'],
            ['name' => 'Kevin Alexander', 'email' => 'kevin.alexander@gmail.com'],
            ['name' => 'Nanda Prakoso', 'email' => 'nanda.prakoso@gmail.com'],
            ['name' => 'Tania Oktaviani', 'email' => 'tania.oktaviani@gmail.com'],
            ['name' => 'Ilham Ramadhan', 'email' => 'ilham.ramadhan@gmail.com'],
        ];

        foreach ($users as $userData) {
            User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make('password123'),
            ]);
        }
    }
}
