<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_tasks_endpoint_requires_authentication(): void
    {
        $this->getJson('/api/tasks')
            ->assertUnauthorized();
    }

    public function test_courses_endpoint_requires_authentication(): void
    {
        $this->getJson('/api/courses')
            ->assertUnauthorized();
    }

    public function test_logout_revokes_current_token(): void
    {
        $user = User::factory()->create();

        $token = $user->createToken('test-token')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/auth/logout')
            ->assertOk();

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'test-token',
        ]);

        // Reset guard agar request berikutnya mengautentikasi ulang token.
        $this->app['auth']->forgetGuards();

        $this->withToken($token)
            ->getJson('/api/tasks')
            ->assertUnauthorized();
    }
}
