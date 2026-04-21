<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ApiTokenManager extends Component
{
    public string $tokenName = '';
    public ?string $plainTextToken = null;
    public bool $readyToLoad = false;

    protected $rules = [
        'tokenName' => 'required|string|min:3|max:50',
    ];

    public function createToken(): void
    {
        $this->validate();

        $user = Auth::user();

        // Limit to 5 tokens per user
        if ($user->tokens()->count() >= 5) {
            session()->flash('error', 'Maximum of 5 API tokens allowed. Please revoke an existing token first.');
            return;
        }

        $token = $user->createToken($this->tokenName);
        $this->plainTextToken = $token->plainTextToken;
        $this->tokenName = '';

        session()->flash('success', 'API token created. Copy it now — it won\'t be shown again.');
    }

    public function revokeToken(int $tokenId): void
    {
        $user = Auth::user();
        $token = $user->tokens()->find($tokenId);

        if ($token) {
            $token->delete();
            session()->flash('success', 'Token revoked successfully.');
        } else {
            session()->flash('error', 'Token not found.');
        }

        $this->plainTextToken = null;
    }

    public function dismissToken(): void
    {
        $this->plainTextToken = null;
    }

    public function loadData(): void
    {
        $this->readyToLoad = true;
    }

    public function render()
    {
        $tokens = $this->readyToLoad ? Auth::user()->tokens()->orderByDesc('created_at')->get() : collect();

        return view('livewire.api-token-manager', [
            'tokens' => $tokens,
        ]);
    }
}
