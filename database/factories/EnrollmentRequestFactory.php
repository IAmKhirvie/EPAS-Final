<?php

namespace Database\Factories;

use App\Models\EnrollmentRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EnrollmentRequestFactory extends Factory
{
    protected $model = EnrollmentRequest::class;

    public function definition(): array
    {
        $student = User::factory()->student();

        return [
            'instructor_id' => User::factory()->instructor(),
            'student_id' => $student,
            'student_name' => fake()->name(),
            'student_email' => fake()->safeEmail(),
            'section' => fake()->randomElement(['SEC-A', 'SEC-B', 'SEC-C']),
            'status' => 'pending',
            'notes' => fake()->sentence(),
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'processed_by' => User::factory()->admin(),
            'processed_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'processed_by' => User::factory()->admin(),
            'admin_notes' => fake()->sentence(),
            'processed_at' => now(),
        ]);
    }
}
