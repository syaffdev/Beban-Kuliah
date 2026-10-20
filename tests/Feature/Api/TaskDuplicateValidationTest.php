<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskDuplicateValidationTest extends TestCase
{
    use RefreshDatabase;

    private function createCourse($user, string $code): object
    {
        return $user->courses()->create([
            'name' => 'Mata kuliah '.$code,
            'code' => $code,
            'credits' => 3,
        ]);
    }

    private function taskPayload(int $courseId, string $title): array
    {
        return [
            'course_id' => $courseId,
            'title' => $title,
            'deadline' => now()->addDays(3)->toDateTimeString(),
            'priority' => 'medium',
            'status' => 'pending',
        ];
    }

    public function test_store_rejects_duplicate_title_in_same_course(): void
    {
        $user = User::factory()->create();
        $course = $this->createCourse($user, 'DB101');

        Sanctum::actingAs($user);

        $this->postJson('/api/tasks', $this->taskPayload($course->id, 'Tugas Database'))
            ->assertCreated();

        $this->postJson('/api/tasks', $this->taskPayload($course->id, 'Tugas Database'))
            ->assertUnprocessable()
            ->assertJsonStructure([
                'message',
                'errors' => ['title'],
            ]);
    }

    public function test_store_rejects_title_with_different_capitalization(): void
    {
        $user = User::factory()->create();
        $course = $this->createCourse($user, 'DB101');

        Sanctum::actingAs($user);

        $this->postJson('/api/tasks', $this->taskPayload($course->id, 'Tugas Database'))
            ->assertCreated();

        $this->postJson('/api/tasks', $this->taskPayload($course->id, 'tugas database'))
            ->assertUnprocessable()
            ->assertJsonStructure(['errors' => ['title']]);
    }

    public function test_store_rejects_title_with_leading_and_trailing_spaces(): void
    {
        $user = User::factory()->create();
        $course = $this->createCourse($user, 'DB101');

        Sanctum::actingAs($user);

        $this->postJson('/api/tasks', $this->taskPayload($course->id, 'Tugas Database'))
            ->assertCreated();

        $this->postJson('/api/tasks', $this->taskPayload($course->id, '  Tugas Database  '))
            ->assertUnprocessable()
            ->assertJsonStructure(['errors' => ['title']]);
    }

    public function test_store_allows_same_title_in_different_course(): void
    {
        $user = User::factory()->create();
        $courseA = $this->createCourse($user, 'DB101');
        $courseB = $this->createCourse($user, 'WEB101');

        Sanctum::actingAs($user);

        $this->postJson('/api/tasks', $this->taskPayload($courseA->id, 'Laporan Mingguan'))
            ->assertCreated();

        $this->postJson('/api/tasks', $this->taskPayload($courseB->id, 'Laporan Mingguan'))
            ->assertCreated();
    }

    public function test_store_allows_same_title_for_different_user(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $courseA = $this->createCourse($userA, 'DB101');
        $courseB = $this->createCourse($userB, 'DB101');

        Sanctum::actingAs($userA);

        $this->postJson('/api/tasks', $this->taskPayload($courseA->id, 'Tugas Database'))
            ->assertCreated();

        Sanctum::actingAs($userB);

        $this->postJson('/api/tasks', $this->taskPayload($courseB->id, 'Tugas Database'))
            ->assertCreated();
    }

    public function test_update_allows_title_equal_to_task_itself(): void
    {
        $user = User::factory()->create();
        $course = $this->createCourse($user, 'DB101');

        Sanctum::actingAs($user);

        $taskId = $this->postJson('/api/tasks', $this->taskPayload($course->id, 'Tugas Database'))
            ->json('data.id');

        $this->patchJson("/api/tasks/{$taskId}", ['title' => 'Tugas Database'])
            ->assertOk()
            ->assertJsonPath('data.title', 'Tugas Database');
    }

    public function test_update_rejects_title_colliding_with_other_task(): void
    {
        $user = User::factory()->create();
        $course = $this->createCourse($user, 'DB101');

        Sanctum::actingAs($user);

        $firstTaskId = $this->postJson('/api/tasks', $this->taskPayload($course->id, 'Tugas Database'))
            ->json('data.id');

        $secondTaskId = $this->postJson('/api/tasks', $this->taskPayload($course->id, 'Tugas Jaringan'))
            ->json('data.id');

        $this->patchJson("/api/tasks/{$secondTaskId}", ['title' => 'Tugas Database'])
            ->assertUnprocessable()
            ->assertJsonStructure(['errors' => ['title']]);

        $this->assertDatabaseHas('tasks', [
            'id' => $secondTaskId,
            'title' => 'Tugas Jaringan',
        ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $firstTaskId,
            'title' => 'Tugas Database',
        ]);
    }

    public function test_update_rejects_colliding_title_with_spaces_and_capitalization(): void
    {
        $user = User::factory()->create();
        $course = $this->createCourse($user, 'DB101');

        Sanctum::actingAs($user);

        $this->postJson('/api/tasks', $this->taskPayload($course->id, 'Tugas Database'))
            ->assertCreated();

        $secondTaskId = $this->postJson('/api/tasks', $this->taskPayload($course->id, 'Tugas Jaringan'))
            ->json('data.id');

        $this->patchJson("/api/tasks/{$secondTaskId}", ['title' => '  TUGAS database  '])
            ->assertUnprocessable()
            ->assertJsonStructure(['errors' => ['title']]);
    }
}
