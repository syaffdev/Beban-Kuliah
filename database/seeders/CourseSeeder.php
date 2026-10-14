<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $courses = [
            [
                'name' => 'Pemrograman Web',
                'code' => 'WEB101',
                'credits' => 3,
            ],
            [
                'name' => 'Basis Data',
                'code' => 'DB101',
                'credits' => 3,
            ],
            [
                'name' => 'Struktur Data',
                'code' => 'SD101',
                'credits' => 3,
            ],
            [
                'name' => 'Rekayasa Perangkat Lunak',
                'code' => 'RPL101',
                'credits' => 2,
            ],
            [
                'name' => 'Jaringan Komputer',
                'code' => 'JK101',
                'credits' => 2,
            ],
        ];

        $users = User::all();

        foreach ($users as $user) {
            foreach ($courses as $data) {
                Course::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'code' => $data['code'],
                    ],
                    [
                        'name' => $data['name'],
                        'credits' => $data['credits'],
                    ]
                );
            }
        }

        $this->command->info(
            "Seeder selesai: {$users->count()} user diproses."
        );

        $this->command->info(
            'Setiap user mendapatkan 5 mata kuliah.'
        );
    }
}

