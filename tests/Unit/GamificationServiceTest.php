<?php

namespace Tests\Unit;

use App\Services\GamificationService;
use Tests\TestCase;

class GamificationServiceTest extends TestCase
{
    public function test_get_points_returns_expected_activities(): void
    {
        $points = GamificationService::getPoints();

        $this->assertIsArray($points);
        $this->assertArrayHasKey('topic_complete', $points);
        $this->assertArrayHasKey('self_check_pass', $points);
        $this->assertArrayHasKey('homework_submit', $points);
        $this->assertArrayHasKey('perfect_score', $points);
        $this->assertArrayHasKey('daily_login', $points);
        $this->assertArrayHasKey('module_complete', $points);
        $this->assertArrayHasKey('course_complete', $points);
    }

    public function test_points_are_positive_integers(): void
    {
        $points = GamificationService::getPoints();

        foreach ($points as $activity => $value) {
            $this->assertIsInt($value, "Points for {$activity} should be an integer");
            $this->assertGreaterThan(0, $value, "Points for {$activity} should be positive");
        }
    }

    public function test_course_complete_has_highest_points(): void
    {
        $points = GamificationService::getPoints();

        $maxActivity = array_search(max($points), $points);
        $this->assertEquals('course_complete', $maxActivity);
    }
}
