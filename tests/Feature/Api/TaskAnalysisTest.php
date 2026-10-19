<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskAnalysisTest extends TestCase
{
    use RefreshDatabase;

    public function test_deadline_risk_classifies_tasks_correctly(): void
    {
        $user = User::factory()->create();

        $course = $user->courses()->create([
            'name' => 'Pemrograman Web',
            'code' => 'WEB101',
            'credits' => 3,
        ]);

        $overdueTask = $user->tasks()->create([
            'course_id' => $course->id,
            'title' => 'Tugas terlambat',
            'deadline' => now()->subDays(1),
            'priority' => 'high',
            'status' => 'pending',
        ]);

        $highRiskTask = $user->tasks()->create([
            'course_id' => $course->id,
            'title' => 'Tugas mendekati deadline',
            'deadline' => now()->addDay(),
            'priority' => 'medium',
            'status' => 'pending',
        ]);

        $lowRiskTask = $user->tasks()->create([
            'course_id' => $course->id,
            'title' => 'Tugas masih lama',
            'deadline' => now()->addDays(5),
            'priority' => 'low',
            'status' => 'in_progress',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/analysis/deadline-risk');

        $response->assertOk()
            ->assertJsonFragment([
                'id' => $overdueTask->id,
                'risk' => 'overdue',
            ])
            ->assertJsonFragment([
                'id' => $highRiskTask->id,
                'risk' => 'high',
            ])
            ->assertJsonFragment([
                'id' => $lowRiskTask->id,
                'risk' => 'low',
            ]);
    }

    public function test_deadline_risk_excludes_completed_tasks(): void
    {
        $user = User::factory()->create();

        $course = $user->courses()->create([
            'name' => 'Basis Data',
            'code' => 'DB101',
            'credits' => 3,
        ]);

        $completedTask = $user->tasks()->create([
            'course_id' => $course->id,
            'title' => 'Tugas sudah selesai',
            'deadline' => now()->subDays(1),
            'priority' => 'medium',
            'status' => 'completed',
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/analysis/deadline-risk')
            ->assertOk()
            ->assertJsonMissing([
                'id' => $completedTask->id,
            ]);
    }

    public function test_deadline_risk_does_not_include_other_users_tasks(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $otherCourse = $otherUser->courses()->create([
            'name' => 'Basis Data',
            'code' => 'DB102',
            'credits' => 3,
        ]);

        $otherTask = $otherUser->tasks()->create([
            'course_id' => $otherCourse->id,
            'title' => 'Tugas pengguna lain',
            'deadline' => now()->addDay(),
            'priority' => 'high',
            'status' => 'pending',
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/analysis/deadline-risk')
            ->assertOk()
            ->assertJsonMissing([
                'id' => $otherTask->id,
            ]);
    }

    public function test_workload_only_counts_own_data(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherUser->courses()->create([
            'name' => 'Mata Kuliah Pengguna Lain',
            'code' => 'OTHER101',
            'credits' => 3,
        ]);

        Sanctum::actingAs($user);
        $this->getJson('/api/analysis/workload')
            ->assertOk()
            ->assertJsonPath('data.total_courses', 0)
            ->assertJsonPath('data.pending_tasks', 0)
            ->assertJsonPath('data.in_progress_tasks', 0)
            ->assertJsonPath('data.completed_tasks', 0)
            ->assertJsonPath('data.overdue_tasks', 0);
    }

    public function test_deadline_risk_uses_exact_deadline_time(): void
    {
        $user = User::factory()->create();

        $course = $user->courses()->create([
            'name' => 'Pemrograman Web',
            'code' => 'WEB101',
            'credits' => 3,
        ]);

        $overdueTask = $user->tasks()->create([
            'course_id' => $course->id,
            'title' => 'Deadline sudah lewat',
            'deadline' => now()->subMinute(),
            'priority' => 'high',
            'status' => 'pending',
        ]);

        $notOverdueTask = $user->tasks()->create([
            'course_id' => $course->id,
            'title' => 'Deadline masih berlangsung',
            'deadline' => now()->addHours(2),
            'priority' => 'medium',
            'status' => 'pending',
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/analysis/deadline-risk')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $overdueTask->id,
                'risk' => 'overdue',
            ])
            ->assertJsonFragment([
                'id' => $notOverdueTask->id,
                'risk' => 'high',
            ]);
    }
}
