<?php

namespace Tests\Unit;

use App\Services\SelfCheckGradingService;
use Tests\TestCase;

class SelfCheckGradingServiceTest extends TestCase
{
    private SelfCheckGradingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SelfCheckGradingService();
    }

    public function test_calculate_percentage_returns_correct_value(): void
    {
        $this->assertEquals(75.0, $this->service->calculatePercentage(75, 100));
        $this->assertEquals(50.0, $this->service->calculatePercentage(5, 10));
        $this->assertEquals(100.0, $this->service->calculatePercentage(10, 10));
    }

    public function test_calculate_percentage_handles_zero_total(): void
    {
        $this->assertEquals(0.0, $this->service->calculatePercentage(0, 0));
    }

    public function test_calculate_score_sums_points_earned(): void
    {
        $results = [
            ['points_earned' => 10],
            ['points_earned' => 20],
            ['points_earned' => 5],
        ];

        $this->assertEquals(35.0, $this->service->calculateScore($results));
    }

    public function test_calculate_score_handles_empty_results(): void
    {
        $this->assertEquals(0.0, $this->service->calculateScore([]));
    }

    public function test_is_passing_uses_default_threshold(): void
    {
        // Default passing score is 70 from config
        $this->assertTrue($this->service->isPassing(70));
        $this->assertTrue($this->service->isPassing(100));
        $this->assertFalse($this->service->isPassing(69));
    }

    public function test_is_passing_uses_custom_threshold(): void
    {
        $this->assertTrue($this->service->isPassing(80, 80));
        $this->assertFalse($this->service->isPassing(79, 80));
        $this->assertTrue($this->service->isPassing(50, 50));
    }
}
