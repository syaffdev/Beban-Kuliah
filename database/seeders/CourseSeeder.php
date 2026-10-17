<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $courses = [
            ['name' => 'Pemrograman Web', 'code' => 'WEB101', 'credits' => 3],
            ['name' => 'Basis Data', 'code' => 'DB101', 'credits' => 3],
            ['name' => 'Struktur Data', 'code' => 'SD101', 'credits' => 4],
            ['name' => 'Jaringan Komputer', 'code' => 'JK101', 'credits' => 3],
            ['name' => 'Rekayasa Perangkat Lunak', 'code' => 'RPL101', 'credits' => 2],
        ];

        User::query()->each(function (User $user) use ($courses) {
            foreach ($courses as $course) {
                $user->courses()->create($course);
            }
        });
    }
}
