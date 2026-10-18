<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilityExceptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_exceptions_require_authentication(): void
    {
        $this->getJson('/api/availability/exceptions')->assertUnauthorized();
        $this->postJson('/api/availability/exceptions', [])->assertUnauthorized();
    }

    public function test_user_can_create_full_day_unavailability(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->postJson(
            '/api/availability/exceptions',
            [
                'date' => '2026-11-02',
                'type' => 'unavailable',
                'reason' => 'Acara keluarga',
            ]
        );
        $response->assertCreated()->assertJsonPath('data.type', 'unavailable')->assertJsonPath('data.reason', 'Acara keluarga');
        $this->assertDatabaseHas('availability_exceptions', [
            'user_id' => $user->id,
            'date' => '2026-11-02',
            'type' => 'unavailable',
            'start_time' => null,
            'end_time' => null,
        ]);
    }

    public function test_user_can_create_extra_availability(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->postJson(
            '/api/availability/exceptions',
            [
                'date' => '2026-11-03',
                'type' => 'extra_available',
                'start_time' => '09:00',
                'end_time' => '12:00',
                'reason' => 'Belajar tambahan',
            ]
        );

        $response->assertCreated()
            ->assertJsonPath('data.type', 'extra_available');

        $this->assertDatabaseHas('availability_exceptions', [
            'user_id' => $user->id,
            'date' => '2026-11-03',
            'type' => 'extra_available',
        ]);
    }

    public function test_extra_availability_requires_both_times(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson(
            '/api/availability/exceptions',
            [
                'date' => '2026-11-03',
                'type' => 'extra_available',
            ]
        )->assertUnprocessable()
            ->assertJsonValidationErrors([
                'start_time',
                'end_time',
            ]);
    }

    public function test_end_time_must_be_after_start_time(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson(
            '/api/availability/exceptions',
            [
                'date' => '2026-11-03',
                'type' => 'extra_available',
                'start_time' => '12:00',
                'end_time' => '09:00',
            ]
        )->assertUnprocessable()
            ->assertJsonValidationErrors(['end_time']);
    }

    public function test_user_only_sees_own_exceptions(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $owner->availabilityExceptions()->create([
            'user_id' => $owner->id,
            'date' => '2026-11-02',
            'type' => 'unavailable',
            'reason' => 'Private',
        ]);

        $response = $this->actingAs($otherUser)
            ->getJson('/api/availability/exceptions');

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_user_cannot_update_another_users_exception(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $exception = $owner->availabilityExceptions()->create([
            'user_id' => $owner->id,
            'date' => '2026-11-02',
            'type' => 'unavailable',
        ]);

        $this->actingAs($otherUser)
            ->patchJson("/api/availability/exceptions/{$exception->id}", [
                'date' => '2026-11-04',
                'type' => 'unavailable',
            ])
            ->assertNotFound();
    }

    public function test_user_cannot_delete_another_users_exception(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $exception = $owner->availabilityExceptions()->create([
            'user_id' => $owner->id,
            'date' => '2026-11-02',
            'type' => 'unavailable',
        ]);

        $this->actingAs($otherUser)
            ->deleteJson("/api/availability/exceptions/{$exception->id}")
            ->assertNotFound();

        $this->assertDatabaseHas('availability_exceptions', [
            'id' => $exception->id,
            'user_id' => $owner->id,
        ]);
    }
}
