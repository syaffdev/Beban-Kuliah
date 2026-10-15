<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class AuthRateLimitTest extends TestCase
{
    public function test_login_is_rate_limited_after_five_requests(): void
    {
        $this->withServerVariables([
            'REMOTE_ADDR' => '10.10.0.1',
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/auth/login', [
                'email' => 'invalid-email',
                'password' => 'wrong-password',
            ])->assertUnprocessable();
        }

        $this->postJson('/api/auth/login', [
            'email' => 'invalid-email',
            'password' => 'wrong-password',
        ])->assertStatus(429);
    }

    public function test_register_is_rate_limited_after_three_requests(): void
    {
        $this->withServerVariables([
            'REMOTE_ADDR' => '10.10.0.2',
        ]);

        for ($i = 0; $i < 3; $i++) {
            $this->postJson('/api/auth/register', [])
                ->assertUnprocessable();
        }

        $this->postJson('/api/auth/register', [])
            ->assertStatus(429);
    }
}
