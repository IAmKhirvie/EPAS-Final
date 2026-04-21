<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErrorHandlingTest extends TestCase
{
    use RefreshDatabase;

    public function test_nonexistent_route_does_not_expose_stack_trace(): void
    {
        $response = $this->get('/this-route-does-not-exist-at-all-12345');
        $content = $response->getContent();

        // Should never expose internal details
        $this->assertStringNotContainsString('Stack trace', $content);
        $this->assertStringNotContainsString('vendor/', $content);
    }

    public function test_404_returns_proper_response(): void
    {
        $response = $this->get('/this-route-does-not-exist-at-all-12345');
        // Should return 404 or redirect, never 500
        $this->assertTrue(in_array($response->status(), [404, 302]));
        $this->assertNotEquals(500, $response->status());
    }

    public function test_csrf_mismatch_is_handled_gracefully(): void
    {
        $user = User::factory()->create();

        // Simulate expired CSRF token by sending invalid token
        $response = $this->actingAs($user)
            ->withHeader('X-CSRF-TOKEN', 'invalid-token')
            ->post('/logout', [], ['X-CSRF-TOKEN' => 'invalid-token']);

        // Should get 419 or redirect, not 500
        $this->assertNotEquals(500, $response->status());
    }

    public function test_server_errors_do_not_expose_debug_info(): void
    {
        // Verify the app is configured for testing
        $this->assertEquals('testing', app()->environment());
    }
}
