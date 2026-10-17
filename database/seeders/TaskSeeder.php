<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        User::query()
            ->with('courses')
            ->each(function (User $user) {
                foreach ($user->courses as $course) {
                    $tasks = [
                        [
                            'title' => "Tugas {$course->name} 1",
                            'description' => 'Mengerjakan tugas pertama.',
                            'deadline' => now()->addDays(2),
                            'priority' => 'high',
                            'status' => 'pending',
                        ],
                        [
                            'title' => "Tugas {$course->name} 2",
                            'description' => 'Menyelesaikan tugas lanjutan.',
                            'deadline' => now()->addDays(5),
                            'priority' => 'medium',
                            'status' => 'in_progress',
                        ],
                        [
                            'title' => "Tugas {$course->name} 3",
                            'description' => 'Mengerjakan latihan tambahan.',
                            'deadline' => now()->addDays(10),
                            'priority' => 'low',
                            'status' => 'completed',
                        ],
                    ];

                    $user->tasks()->createMany(
                        array_map(
                            fn (array $task) => [
                                ...$task,
                                'course_id' => $course->id,
                            ],
                            $tasks
                        )
                    );
                }
            });
    }
}
