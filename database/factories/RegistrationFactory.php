<?php

namespace Database\Factories;

use App\Models\Registration;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class RegistrationFactory extends Factory
{
    protected $model = Registration::class;

    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'status' => Registration::STATUS_PENDING,
        ];
    }

    public function emailVerified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => now(),
            'status' => Registration::STATUS_EMAIL_VERIFIED,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => now(),
            'admin_approved_at' => now(),
            'status' => Registration::STATUS_APPROVED,
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Registration::STATUS_REJECTED,
            'rejection_reason' => fake()->sentence(),
        ]);
    }
}
