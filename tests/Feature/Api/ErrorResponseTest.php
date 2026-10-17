<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ErrorResponseTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_requires_authentication(): void
    {
        $this->getJson('/api/courses')
            ->assertUnauthorized()
            ->assertJsonStructure([
                'message',
            ]);
    }

    public function test_course_validation_error_has_consistent_format(): void
    {
        $user = User::factory()->create();

        $token = $user->createToken('test-token')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/courses', [
                'code' => 'WEB101',
                'credits' => 3,
            ])
            ->assertUnprocessable()
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'name',
                ],
            ]);
    }

    public function test_missing_course_returns_not_found(): void
    {
        $user = User::factory()->create();

        $token = $user->createToken('test-token')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/courses/999999')
            ->assertNotFound()
            ->assertJsonStructure([
                'message',
            ]);
    }

    public function test_server_error_returns_safe_json_response(): void
    {
        Route::get('/api/testing/server-error', function () {
            throw new \RuntimeException(
                'Sensitive database details'
            );
        });

        $this->getJson('/api/testing/server-error')
            ->assertInternalServerError()
            ->assertExactJson([
                'message' => 'Terjadi kesalahan pada server.',
            ])
            ->assertDontSee('Sensitive database details');
    }
}
