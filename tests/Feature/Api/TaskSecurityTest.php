<?php

namespace Tests\Feature\Api;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_id_is_not_mass_assignable(): void
    {
        $task = new Task;

        $this->assertFalse(
            $task->isFillable('user_id')
        );
    }

    public function test_user_cannot_transfer_task_ownership(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $course = $owner->courses()->create([
            'name' => 'Pemrograman Web',
            'code' => 'WEB101',
            'credits' => 3,
        ]);

        $task = $owner->tasks()->create([
            'course_id' => $course->id,
            'title' => 'Tugas awal',
            'deadline' => now()->addDays(3),
            'priority' => 'medium',
            'status' => 'pending',
        ]);

        Sanctum::actingAs($owner);

        $response = $this->patchJson(
            "/api/tasks/{$task->id}",
            [
                'title' => 'Tugas diperbarui',
                'user_id' => $otherUser->id,
            ]
        );

        $response->assertOk();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'user_id' => $owner->id,
            'title' => 'Tugas diperbarui',
        ]);
    }

    public function test_user_cannot_access_another_users_task(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $course = $owner->courses()->create([
            'name' => 'Basis Data',
            'code' => 'DB101',
            'credits' => 3,
        ]);

        $task = $owner->tasks()->create([
            'course_id' => $course->id,
            'title' => 'Tugas privat',
            'deadline' => now()->addDays(3),
            'priority' => 'high',
            'status' => 'pending',
        ]);

        Sanctum::actingAs($otherUser);

        $this->getJson("/api/tasks/{$task->id}")
            ->assertNotFound();

        $this->patchJson("/api/tasks/{$task->id}", [
            'title' => 'Percobaan perubahan',
        ])->assertNotFound();

        $this->deleteJson("/api/tasks/{$task->id}")
            ->assertNotFound();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'user_id' => $owner->id,
            'title' => 'Tugas privat',
        ]);
    }

    public function test_user_cannot_attach_task_to_another_users_course(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $otherCourse = $otherUser->courses()->create([
            'name' => 'Course pengguna lain',
            'code' => 'OTH101',
            'credits' => 3,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/tasks', [
            'course_id' => $otherCourse->id,
            'title' => 'Tugas percobaan',
            'deadline' => now()->addDays(3)->toDateTimeString(),
            'priority' => 'medium',
            'status' => 'pending',
        ]);

        $response->assertUnprocessable();

        $this->assertDatabaseMissing('tasks', [
            'user_id' => $user->id,
            'course_id' => $otherCourse->id,
            'title' => 'Tugas percobaan',
        ]);
    }

    public function test_task_rejects_invalid_status(): void
    {
        $user = User::factory()->create();

        $course = $user->courses()->create([
            'name' => 'Pemrograman Web',
            'code' => 'WEB101',
            'credits' => 3,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/tasks', [
            'course_id' => $course->id,
            'title' => 'Tugas pengujian',
            'deadline' => now()->addDays(3)->toDateTimeString(),
            'priority' => 'medium',
            'status' => 'invalid_status',
        ]);

        $response->assertUnprocessable();

        $this->assertDatabaseMissing('tasks', [
            'user_id' => $user->id,
            'title' => 'Tugas pengujian',
        ]);
    }

    public function test_task_rejects_invalid_priority(): void
    {
        $user = User::factory()->create();

        $course = $user->courses()->create([
            'name' => 'Basis Data',
            'code' => 'DB101',
            'credits' => 3,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/tasks', [
            'course_id' => $course->id,
            'title' => 'Tugas pengujian priority',
            'deadline' => now()->addDays(3)->toDateTimeString(),
            'priority' => 'urgent',
            'status' => 'pending',
        ]);

        $response->assertUnprocessable();

        $this->assertDatabaseMissing('tasks', [
            'user_id' => $user->id,
            'title' => 'Tugas pengujian priority',
        ]);
    }

    public function test_task_crud_returns_expected_response_structure(): void
    {
        $user = User::factory()->create();

        $course = $user->courses()->create([
            'name' => 'Pemrograman Web',
            'code' => 'WEB101',
            'credits' => 3,
        ]);

        Sanctum::actingAs($user);

        // CREATE
        $createResponse = $this->postJson('/api/tasks', [
            'course_id' => $course->id,
            'title' => 'Tugas API',
            'deadline' => now()->addDays(3)->toDateTimeString(),
            'priority' => 'medium',
            'status' => 'pending',
        ]);

        $createResponse->assertCreated()
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'course_id',
                    'title',
                    'priority',
                    'status',
                    'course' => ['id', 'name', 'code'],
                ],
            ]);

        $taskId = $createResponse->json('data.id');

        // LIST
        $this->getJson('/api/tasks')
            ->assertOk()
            ->assertJsonStructure([
                'message',
                'data' => [
                    '*' => ['id', 'title', 'course'],
                ],
            ]);

        // DETAIL
        $this->getJson("/api/tasks/{$taskId}")
            ->assertOk()
            ->assertJsonPath('data.id', $taskId)
            ->assertJsonPath('data.course.id', $course->id);

        // UPDATE
        $this->patchJson("/api/tasks/{$taskId}", [
            'title' => 'Tugas API Diperbarui',
        ])
            ->assertOk()
            ->assertJsonPath('data.title', 'Tugas API Diperbarui');

        // DELETE
        $this->deleteJson("/api/tasks/{$taskId}")
            ->assertOk()
            ->assertJsonStructure(['message']);
    }

    public function test_invalid_task_status_has_consistent_error_format(): void
    {
        $user = User::factory()->create();

        $course = $user->courses()->create([
            'name' => 'Pemrograman Web',
            'code' => 'WEB101',
            'credits' => 3,
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/tasks', [
                'course_id' => $course->id,
                'title' => 'Tugas API',
                'deadline' => now()->addDays(3)->toDateString(),
                'priority' => 'high',
                'status' => 'selesai',
            ])
            ->assertUnprocessable()
            ->assertJsonStructure([
                'message',
                'errors' => ['status'],
            ]);
    }

    public function test_task_uses_default_estimated_duration(): void
    {
        $user = User::factory()->create();

        $course = $user->courses()->create([
            'name' => 'Pemrograman Web',
            'code' => 'WEB101',
            'credits' => 3,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/tasks', [
            'course_id' => $course->id,
            'title' => 'Tugas dengan estimasi default',
            'deadline' => now()->addDays(3)->toDateTimeString(),
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.estimated_duration_minutes', 60);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Tugas dengan estimasi default',
            'estimated_duration_minutes' => 60,
        ]);
    }

    public function test_user_can_set_and_update_estimated_duration(): void
    {
        $user = User::factory()->create();

        $course = $user->courses()->create([
            'name' => 'Basis Data',
            'code' => 'DB101',
            'credits' => 3,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/tasks', [
            'course_id' => $course->id,
            'title' => 'Tugas dengan estimasi khusus',
            'deadline' => now()->addDays(3)->toDateTimeString(),
            'estimated_duration_minutes' => 90,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.estimated_duration_minutes', 90);

        $taskId = $response->json('data.id');

        $this->patchJson("/api/tasks/{$taskId}", [
            'estimated_duration_minutes' => 120,
        ])
            ->assertOk()
            ->assertJsonPath('data.estimated_duration_minutes', 120);

        $this->assertDatabaseHas('tasks', [
            'id' => $taskId,
            'estimated_duration_minutes' => 120,
        ]);
    }

    public function test_task_rejects_estimated_duration_below_minimum(): void
    {
        $user = User::factory()->create();

        $course = $user->courses()->create([
            'name' => 'Pemrograman Web',
            'code' => 'WEB101',
            'credits' => 3,
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/tasks', [
            'course_id' => $course->id,
            'title' => 'Tugas tidak valid',
            'deadline' => now()->addDays(3)->toDateTimeString(),
            'estimated_duration_minutes' => 10,
        ])->assertUnprocessable();
    }

    public function test_task_rejects_estimated_duration_above_maximum(): void
    {
        $user = User::factory()->create();

        $course = $user->courses()->create([
            'name' => 'Pemrograman Web',
            'code' => 'WEB101',
            'credits' => 3,
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/tasks', [
            'course_id' => $course->id,
            'title' => 'Tugas tidak valid',
            'deadline' => now()->addDays(3)->toDateTimeString(),
            'estimated_duration_minutes' => 500,
        ])->assertUnprocessable();
    }
}
