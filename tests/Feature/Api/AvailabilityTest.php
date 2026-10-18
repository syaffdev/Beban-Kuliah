<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\WeeklyAvailability;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_id_is_not_mass_assignable(): void
    {
        $availability = new WeeklyAvailability;

        $this->assertFalse(
            $availability->isFillable('user_id')
        );
    }

    public function test_weekly_availability_requires_authentication(): void
    {
        $this->getJson('/api/availability/weekly')
            ->assertUnauthorized();

        $this->postJson('/api/availability/weekly', [
            'day_of_week' => 1,
            'start_time' => '19:00',
            'end_time' => '21:00',
        ])->assertUnauthorized();
    }

    public function test_user_can_create_and_list_own_weekly_availability(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->postJson('/api/availability/weekly', [
            'day_of_week' => 1,
            'start_time' => '19:00',
            'end_time' => '21:00',
        ])
            ->assertCreated()
            ->assertJsonPath('data.day_of_week', 1)
            ->assertJsonPath('data.start_time', '19:00')
            ->assertJsonPath('data.end_time', '21:00');

        $this->assertDatabaseHas('weekly_availabilities', [
            'user_id' => $user->id,
            'day_of_week' => 1,
            'start_time' => '19:00',
            'end_time' => '21:00',
        ]);

        $this->getJson('/api/availability/weekly')
            ->assertOk()
            ->assertJsonPath('data.0.day_of_week', 1);
    }

    public function test_user_only_sees_own_weekly_availability(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $owner->weeklyAvailabilities()->create([
            'day_of_week' => 1,
            'start_time' => '19:00',
            'end_time' => '21:00',
        ]);

        $otherUser->weeklyAvailabilities()->create([
            'day_of_week' => 3,
            'start_time' => '16:00',
            'end_time' => '18:00',
        ]);

        Sanctum::actingAs($otherUser);

        $this->getJson('/api/availability/weekly')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.day_of_week', 3);
    }

    public function test_user_can_update_own_weekly_availability(): void
    {
        $user = User::factory()->create();

        $availability = $user->weeklyAvailabilities()->create([
            'day_of_week' => 1,
            'start_time' => '19:00',
            'end_time' => '21:00',
        ]);

        Sanctum::actingAs($user);

        $this->patchJson(
            "/api/availability/weekly/{$availability->id}",
            [
                'day_of_week' => 2,
                'start_time' => '18:00',
                'end_time' => '20:00',
            ]
        )
            ->assertOk()
            ->assertJsonPath('data.day_of_week', 2)
            ->assertJsonPath('data.start_time', '18:00')
            ->assertJsonPath('data.end_time', '20:00');

        $this->assertDatabaseHas('weekly_availabilities', [
            'id' => $availability->id,
            'user_id' => $user->id,
            'day_of_week' => 2,
            'start_time' => '18:00',
            'end_time' => '20:00',
        ]);
    }

    public function test_user_cannot_update_another_users_weekly_availability(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $availability = $owner->weeklyAvailabilities()->create([
            'day_of_week' => 1,
            'start_time' => '19:00',
            'end_time' => '21:00',
        ]);

        Sanctum::actingAs($otherUser);

        $this->patchJson(
            "/api/availability/weekly/{$availability->id}",
            [
                'day_of_week' => 2,
                'start_time' => '18:00',
                'end_time' => '20:00',
            ]
        )->assertNotFound();

        $this->assertDatabaseHas('weekly_availabilities', [
            'id' => $availability->id,
            'user_id' => $owner->id,
            'day_of_week' => 1,
        ]);
    }

    public function test_user_can_delete_own_weekly_availability(): void
    {
        $user = User::factory()->create();

        $availability = $user->weeklyAvailabilities()->create([
            'day_of_week' => 1,
            'start_time' => '19:00',
            'end_time' => '21:00',
        ]);

        Sanctum::actingAs($user);

        $this->deleteJson(
            "/api/availability/weekly/{$availability->id}"
        )
            ->assertOk()
            ->assertJsonPath(
                'message',
                'Weekly availability deleted successfully.'
            );

        $this->assertDatabaseMissing('weekly_availabilities', [
            'id' => $availability->id,
        ]);
    }
}
