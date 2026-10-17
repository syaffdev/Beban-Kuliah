<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CourseSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_access_another_users_course(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $course = $owner->courses()->create([
            'name' => 'Pemrograman Web',
            'code' => 'WEB101',
            'credits' => 3,
        ]);

        Sanctum::actingAs($otherUser);

        $this->getJson("/api/courses/{$course->id}")
            ->assertNotFound();

        $this->putJson("/api/courses/{$course->id}", [
            'name' => 'Course diubah',
        ])->assertNotFound();

        $this->deleteJson("/api/courses/{$course->id}")
            ->assertNotFound();

        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'user_id' => $owner->id,
            'name' => 'Pemrograman Web',
        ]);
    }

    public function test_user_cannot_create_course_for_another_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/courses', [
            'name' => 'Course baru',
            'code' => 'NEW101',
            'credits' => 3,
            'user_id' => $otherUser->id,
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('courses', [
            'name' => 'Course baru',
            'code' => 'NEW101',
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseMissing('courses', [
            'name' => 'Course baru',
            'user_id' => $otherUser->id,
        ]);
    }

    public function test_user_can_manage_own_course(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/courses', [
            'name' => 'Basis Data',
            'code' => 'DB101',
            'credits' => 3,
        ]);

        $response->assertCreated();

        $courseId = $response->json('data.id');

        $this->patchJson("/api/courses/{$courseId}", [
            'name' => 'Basis Data Lanjut',
        ])->assertOk();

        $this->assertDatabaseHas('courses', [
            'id' => $courseId,
            'user_id' => $user->id,
            'name' => 'Basis Data Lanjut',
        ]);
    }

    public function test_course_rejects_credits_below_minimum(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/courses', [
            'name' => 'Pemrograman Web',
            'code' => 'WEB101',
            'credits' => 0,
        ]);

        $response->assertUnprocessable();

        $this->assertDatabaseMissing('courses', [
            'user_id' => $user->id,
            'code' => 'WEB101',
        ]);
    }

    public function test_course_rejects_credits_above_maximum(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/courses', [
            'name' => 'Pemrograman Web',
            'code' => 'WEB101',
            'credits' => 7,
        ]);

        $response->assertUnprocessable();

        $this->assertDatabaseMissing('courses', [
            'user_id' => $user->id,
            'code' => 'WEB101',
        ]);
    }

    public function test_deleting_course_also_deletes_its_tasks(): void
    {
        $user = User::factory()->create();

        $course = $user->courses()->create([
            'name' => 'Pemrograman Web',
            'code' => 'WEB101',
            'credits' => 3,
        ]);

        $task = $user->tasks()->create([
            'course_id' => $course->id,
            'title' => 'Tugas Laravel',
            'deadline' => now()->addDays(3),
            'priority' => 'medium',
            'status' => 'pending',
        ]);

        Sanctum::actingAs($user);

        $this->deleteJson("/api/courses/{$course->id}")
            ->assertOk();

        $this->assertDatabaseMissing('courses', [
            'id' => $course->id,
        ]);

        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
        ]);
    }

    public function test_course_crud_returns_expected_response_structure(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        // CREATE
        $createResponse = $this->postJson('/api/courses', [
            'name' => 'Pemrograman Web',
            'code' => 'WEB101',
            'credits' => 3,
        ]);

        $createResponse->assertCreated()
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'user_id',
                    'name',
                    'code',
                    'credits',
                ],
            ]);

        $courseId = $createResponse->json('data.id');

        // LIST
        $this->getJson('/api/courses')
            ->assertOk()
            ->assertJsonStructure([
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'code',
                        'credits',
                    ],
                ],
            ]);

        // DETAIL
        $this->getJson("/api/courses/{$courseId}")
            ->assertOk()
            ->assertJsonPath('data.id', $courseId)
            ->assertJsonPath('data.code', 'WEB101');

        // UPDATE
        $this->patchJson("/api/courses/{$courseId}", [
            'name' => 'Pemrograman Web Lanjut',
        ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Pemrograman Web Lanjut');

        // DELETE
        $this->deleteJson("/api/courses/{$courseId}")
            ->assertOk()
            ->assertJsonStructure([
                'message',
            ]);
    }

    public function test_user_cannot_create_courses_with_duplicate_code(): void
    {
        $user = User::factory()->create();

        $user->courses()->create([
            'name' => 'Pemrograman Web',
            'code' => 'WEB101',
            'credits' => 3,
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/courses', [
            'name' => 'Pemrograman Mobile',
            'code' => 'WEB101',
            'credits' => 3,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['code']);

        $this->assertDatabaseCount('courses', 1);
    }

    public function test_different_users_can_use_the_same_course_code(): void
    {
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();

        $firstUser->courses()->create([
            'name' => 'Pemrograman Web',
            'code' => 'WEB101',
            'credits' => 3,
        ]);

        Sanctum::actingAs($secondUser);

        $this->postJson('/api/courses', [
            'name' => 'Pemrograman Web',
            'code' => 'WEB101',
            'credits' => 3,
        ])
            ->assertCreated()
            ->assertJsonPath('data.user_id', $secondUser->id);

        $this->assertDatabaseCount('courses', 2);
    }
}
