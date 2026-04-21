<?php

namespace Tests\Feature\Livewire;

use App\Livewire\ApiTokenManager;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ApiTokenManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_renders(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ApiTokenManager::class)
            ->assertStatus(200)
            ->assertSee('Create API Token');
    }

    public function test_create_token(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ApiTokenManager::class)
            ->set('tokenName', 'Test Token')
            ->call('createToken')
            ->assertNotSet('plainTextToken', null)
            ->assertSee('Your New API Token');

        $this->assertEquals(1, $user->tokens()->count());
    }

    public function test_create_token_requires_name(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ApiTokenManager::class)
            ->set('tokenName', '')
            ->call('createToken')
            ->assertHasErrors('tokenName');
    }

    public function test_max_5_tokens(): void
    {
        $user = User::factory()->create();

        // Create 5 tokens
        for ($i = 1; $i <= 5; $i++) {
            $user->createToken("Token {$i}");
        }

        Livewire::actingAs($user)
            ->test(ApiTokenManager::class)
            ->set('tokenName', 'Token 6')
            ->call('createToken')
            ->assertSee('Maximum of 5 API tokens allowed');

        $this->assertEquals(5, $user->tokens()->count());
    }

    public function test_revoke_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('Test Token');

        Livewire::actingAs($user)
            ->test(ApiTokenManager::class)
            ->call('revokeToken', $token->accessToken->id)
            ->assertSee('Token revoked successfully');

        $this->assertEquals(0, $user->tokens()->count());
    }

    public function test_dismiss_token_clears_plaintext(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ApiTokenManager::class)
            ->set('tokenName', 'Test Token')
            ->call('createToken')
            ->call('dismissToken')
            ->assertSet('plainTextToken', null);
    }
}
