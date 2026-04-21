<?php

namespace Tests\Unit;

use App\Services\GradingService;
use ReflectionMethod;
use Tests\TestCase;

class GradingServiceTest extends TestCase
{
    private GradingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GradingService();
    }

    // ---------------------------------------------------------------
    // Helper to call protected methods via reflection
    // ---------------------------------------------------------------

    private function callProtected(string $method, array $args): mixed
    {
        $ref = new ReflectionMethod(GradingService::class, $method);
        $ref->setAccessible(true);

        return $ref->invoke($this->service, ...$args);
    }

    // ---------------------------------------------------------------
    // applyGradingScale() tests
    // ---------------------------------------------------------------

    public function test_grading_scale_outstanding_at_100(): void
    {
        $result = $this->service->applyGradingScale(100);

        $this->assertEquals('Outstanding', $result['descriptor']);
        $this->assertEquals('O', $result['code']);
        $this->assertTrue($result['is_competent']);
        $this->assertEquals('Competent', $result['competency_status']);
        $this->assertEquals(100.0, $result['percentage']);
    }

    public function test_grading_scale_outstanding_at_90(): void
    {
        $result = $this->service->applyGradingScale(90);

        $this->assertEquals('Outstanding', $result['descriptor']);
        $this->assertEquals('O', $result['code']);
        $this->assertTrue($result['is_competent']);
    }

    public function test_grading_scale_outstanding_at_95(): void
    {
        $result = $this->service->applyGradingScale(95.5);

        $this->assertEquals('Outstanding', $result['descriptor']);
        $this->assertEquals('O', $result['code']);
        $this->assertTrue($result['is_competent']);
        $this->assertEquals(95.5, $result['percentage']);
    }

    public function test_grading_scale_very_satisfactory_at_89(): void
    {
        $result = $this->service->applyGradingScale(89);

        $this->assertEquals('Very Satisfactory', $result['descriptor']);
        $this->assertEquals('VS', $result['code']);
        $this->assertTrue($result['is_competent']);
    }

    public function test_grading_scale_very_satisfactory_at_85(): void
    {
        $result = $this->service->applyGradingScale(85);

        $this->assertEquals('Very Satisfactory', $result['descriptor']);
        $this->assertEquals('VS', $result['code']);
        $this->assertTrue($result['is_competent']);
    }

    public function test_grading_scale_satisfactory_at_84(): void
    {
        $result = $this->service->applyGradingScale(84);

        $this->assertEquals('Satisfactory', $result['descriptor']);
        $this->assertEquals('S', $result['code']);
        $this->assertTrue($result['is_competent']);
    }

    public function test_grading_scale_satisfactory_at_80(): void
    {
        $result = $this->service->applyGradingScale(80);

        $this->assertEquals('Satisfactory', $result['descriptor']);
        $this->assertEquals('S', $result['code']);
        $this->assertTrue($result['is_competent']);
    }

    public function test_grading_scale_fairly_satisfactory_at_79(): void
    {
        $result = $this->service->applyGradingScale(79);

        $this->assertEquals('Fairly Satisfactory', $result['descriptor']);
        $this->assertEquals('FS', $result['code']);
        $this->assertTrue($result['is_competent']);
    }

    public function test_grading_scale_fairly_satisfactory_at_75(): void
    {
        $result = $this->service->applyGradingScale(75);

        $this->assertEquals('Fairly Satisfactory', $result['descriptor']);
        $this->assertEquals('FS', $result['code']);
        $this->assertTrue($result['is_competent']);
    }

    public function test_grading_scale_did_not_meet_expectations_at_74(): void
    {
        $result = $this->service->applyGradingScale(74);

        $this->assertEquals('Did Not Meet Expectations', $result['descriptor']);
        $this->assertEquals('DNM', $result['code']);
        $this->assertFalse($result['is_competent']);
        $this->assertEquals('Not Yet Competent', $result['competency_status']);
    }

    public function test_grading_scale_did_not_meet_expectations_at_0(): void
    {
        $result = $this->service->applyGradingScale(0);

        $this->assertEquals('Did Not Meet Expectations', $result['descriptor']);
        $this->assertEquals('DNM', $result['code']);
        $this->assertFalse($result['is_competent']);
    }

    public function test_grading_scale_did_not_meet_expectations_at_50(): void
    {
        $result = $this->service->applyGradingScale(50);

        $this->assertEquals('Did Not Meet Expectations', $result['descriptor']);
        $this->assertEquals('DNM', $result['code']);
        $this->assertFalse($result['is_competent']);
    }

    public function test_grading_scale_boundary_between_vs_and_outstanding(): void
    {
        // 89.99 should still be VS (within 85-89 range)
        $result89 = $this->service->applyGradingScale(89.99);
        $this->assertEquals('VS', $result89['code']);

        // 90.0 should be Outstanding
        $result90 = $this->service->applyGradingScale(90.0);
        $this->assertEquals('O', $result90['code']);
    }

    public function test_grading_scale_returns_no_grade_for_negative_value(): void
    {
        $result = $this->service->applyGradingScale(-1);

        $this->assertEquals('No Grade', $result['descriptor']);
        $this->assertEquals('NG', $result['code']);
        $this->assertFalse($result['is_competent']);
        $this->assertEquals('Not Yet Competent', $result['competency_status']);
        $this->assertEquals(0, $result['percentage']);
    }

    // ---------------------------------------------------------------
    // percentageToGPA() tests
    // ---------------------------------------------------------------

    public function test_gpa_returns_4_0_for_97_and_above(): void
    {
        $this->assertEquals(4.0, $this->callProtected('percentageToGPA', [97]));
        $this->assertEquals(4.0, $this->callProtected('percentageToGPA', [100]));
    }

    public function test_gpa_returns_3_7_for_93_to_96(): void
    {
        $this->assertEquals(3.7, $this->callProtected('percentageToGPA', [93]));
        $this->assertEquals(3.7, $this->callProtected('percentageToGPA', [96]));
    }

    public function test_gpa_returns_3_3_for_90_to_92(): void
    {
        $this->assertEquals(3.3, $this->callProtected('percentageToGPA', [90]));
        $this->assertEquals(3.3, $this->callProtected('percentageToGPA', [92]));
    }

    public function test_gpa_returns_3_0_for_87_to_89(): void
    {
        $this->assertEquals(3.0, $this->callProtected('percentageToGPA', [87]));
        $this->assertEquals(3.0, $this->callProtected('percentageToGPA', [89]));
    }

    public function test_gpa_returns_2_7_for_83_to_86(): void
    {
        $this->assertEquals(2.7, $this->callProtected('percentageToGPA', [83]));
        $this->assertEquals(2.7, $this->callProtected('percentageToGPA', [86]));
    }

    public function test_gpa_returns_2_3_for_80_to_82(): void
    {
        $this->assertEquals(2.3, $this->callProtected('percentageToGPA', [80]));
        $this->assertEquals(2.3, $this->callProtected('percentageToGPA', [82]));
    }

    public function test_gpa_returns_2_0_for_77_to_79(): void
    {
        $this->assertEquals(2.0, $this->callProtected('percentageToGPA', [77]));
        $this->assertEquals(2.0, $this->callProtected('percentageToGPA', [79]));
    }

    public function test_gpa_returns_1_7_for_73_to_76(): void
    {
        $this->assertEquals(1.7, $this->callProtected('percentageToGPA', [73]));
        $this->assertEquals(1.7, $this->callProtected('percentageToGPA', [76]));
    }

    public function test_gpa_returns_1_3_for_70_to_72(): void
    {
        $this->assertEquals(1.3, $this->callProtected('percentageToGPA', [70]));
        $this->assertEquals(1.3, $this->callProtected('percentageToGPA', [72]));
    }

    public function test_gpa_returns_1_0_for_67_to_69(): void
    {
        $this->assertEquals(1.0, $this->callProtected('percentageToGPA', [67]));
        $this->assertEquals(1.0, $this->callProtected('percentageToGPA', [69]));
    }

    public function test_gpa_returns_0_for_below_67(): void
    {
        $this->assertEquals(0.0, $this->callProtected('percentageToGPA', [66]));
        $this->assertEquals(0.0, $this->callProtected('percentageToGPA', [0]));
        $this->assertEquals(0.0, $this->callProtected('percentageToGPA', [50]));
    }

    // ---------------------------------------------------------------
    // calculateComponentStats() tests
    // ---------------------------------------------------------------

    public function test_self_check_stats_uses_highest_score_per_self_check(): void
    {
        // Simulates 2 self-checks with multiple attempts each
        // Self-check 1: attempts of 80%, 90%, 70% → highest = 90%
        // Self-check 2: attempts of 60%, 85% → highest = 85%
        // Expected: (90 + 85) / 2 = 87.5%
        $highestScores = collect([
            (object) ['self_check_id' => 1, 'highest_percentage' => 90, 'attempts' => 3],
            (object) ['self_check_id' => 2, 'highest_percentage' => 85, 'attempts' => 2],
        ]);

        $result = $this->callProtected('calculateSelfCheckStats', [
            $highestScores,
        ]);

        $this->assertEquals(2, $result['count']);
        $this->assertEquals(175, $result['total_score']);
        $this->assertEquals(200, $result['max_score']);
        $this->assertEquals(87.5, $result['percentage']);
        $this->assertEquals(87.5, $result['average']);
    }

    public function test_self_check_stats_with_empty_collection(): void
    {
        $result = $this->callProtected('calculateSelfCheckStats', [
            collect([]),
        ]);

        $this->assertEquals(0, $result['count']);
        $this->assertEquals(0, $result['total_score']);
        $this->assertEquals(0, $result['max_score']);
        $this->assertEquals(0, $result['percentage']);
        $this->assertEquals(0, $result['average']);
    }

    public function test_self_check_stats_single_quiz_multiple_attempts(): void
    {
        // Student took one self-check 3 times: 60%, 75%, 95%
        // Only the highest (95%) should count
        $highestScores = collect([
            (object) ['self_check_id' => 1, 'highest_percentage' => 95, 'attempts' => 3],
        ]);

        $result = $this->callProtected('calculateSelfCheckStats', [
            $highestScores,
        ]);

        $this->assertEquals(1, $result['count']);
        $this->assertEquals(95, $result['total_score']);
        $this->assertEquals(100, $result['max_score']);
        $this->assertEquals(95.0, $result['percentage']);
    }

    public function test_component_stats_with_empty_collection(): void
    {
        $result = $this->callProtected('calculateComponentStats', [
            collect([]),
            'percentage',
        ]);

        $this->assertEquals(0, $result['count']);
        $this->assertEquals(0, $result['total_score']);
        $this->assertEquals(0, $result['max_score']);
        $this->assertEquals(0, $result['percentage']);
        $this->assertEquals(0, $result['average']);
    }

    public function test_component_stats_with_single_submission(): void
    {
        $submissions = collect([
            (object) ['percentage' => 80],
        ]);

        $result = $this->callProtected('calculateComponentStats', [
            $submissions,
            'percentage',
        ]);

        $this->assertEquals(1, $result['count']);
        $this->assertEquals(80, $result['total_score']);
        $this->assertEquals(100, $result['max_score']);
        $this->assertEquals(80.0, $result['percentage']);
        $this->assertEquals(80.0, $result['average']);
    }

    public function test_component_stats_with_multiple_submissions(): void
    {
        $submissions = collect([
            (object) ['percentage' => 90],
            (object) ['percentage' => 80],
            (object) ['percentage' => 70],
        ]);

        $result = $this->callProtected('calculateComponentStats', [
            $submissions,
            'percentage',
        ]);

        $this->assertEquals(3, $result['count']);
        $this->assertEquals(240, $result['total_score']);
        $this->assertEquals(300, $result['max_score']);
        $this->assertEquals(80.0, $result['percentage']);
        $this->assertEquals(80.0, $result['average']);
    }

    public function test_component_stats_rounds_to_two_decimal_places(): void
    {
        $submissions = collect([
            (object) ['percentage' => 85],
            (object) ['percentage' => 90],
            (object) ['percentage' => 78],
        ]);

        $result = $this->callProtected('calculateComponentStats', [
            $submissions,
            'percentage',
        ]);

        // (85 + 90 + 78) / 3 = 84.333...
        $this->assertEquals(84.33, $result['percentage']);
        $this->assertEquals(84.33, $result['average']);
    }

    // ---------------------------------------------------------------
    // calculateHomeworkStats() tests
    // ---------------------------------------------------------------

    public function test_homework_stats_with_empty_collection(): void
    {
        $result = $this->callProtected('calculateHomeworkStats', [
            collect([]),
        ]);

        $this->assertEquals(0, $result['count']);
        $this->assertEquals(0, $result['total_score']);
        $this->assertEquals(0, $result['max_score']);
        $this->assertEquals(0, $result['percentage']);
        $this->assertEquals(0, $result['average']);
    }

    public function test_homework_stats_with_single_submission(): void
    {
        $submissions = collect([
            (object) ['score' => 8, 'max_points' => 10],
        ]);

        $result = $this->callProtected('calculateHomeworkStats', [
            $submissions,
        ]);

        $this->assertEquals(1, $result['count']);
        $this->assertEquals(8, $result['total_score']);
        $this->assertEquals(10, $result['max_score']);
        $this->assertEquals(80.0, $result['percentage']);
        $this->assertEquals(8.0, $result['average']);
    }

    public function test_homework_stats_with_multiple_submissions(): void
    {
        $submissions = collect([
            (object) ['score' => 18, 'max_points' => 20],
            (object) ['score' => 7, 'max_points' => 10],
            (object) ['score' => 45, 'max_points' => 50],
        ]);

        $result = $this->callProtected('calculateHomeworkStats', [
            $submissions,
        ]);

        $this->assertEquals(3, $result['count']);
        $this->assertEquals(70, $result['total_score']);
        $this->assertEquals(80, $result['max_score']);
        // (70 / 80) * 100 = 87.5
        $this->assertEquals(87.5, $result['percentage']);
        // 70 / 3 = 23.333...
        $this->assertEquals(23.33, $result['average']);
    }

    public function test_homework_stats_with_perfect_scores(): void
    {
        $submissions = collect([
            (object) ['score' => 10, 'max_points' => 10],
            (object) ['score' => 20, 'max_points' => 20],
        ]);

        $result = $this->callProtected('calculateHomeworkStats', [
            $submissions,
        ]);

        $this->assertEquals(100.0, $result['percentage']);
    }

    public function test_homework_stats_with_zero_scores(): void
    {
        $submissions = collect([
            (object) ['score' => 0, 'max_points' => 10],
            (object) ['score' => 0, 'max_points' => 20],
        ]);

        $result = $this->callProtected('calculateHomeworkStats', [
            $submissions,
        ]);

        $this->assertEquals(0.0, $result['percentage']);
        $this->assertEquals(0.0, $result['average']);
    }

    // ---------------------------------------------------------------
    // calculateTaskStats() tests
    // ---------------------------------------------------------------

    public function test_task_stats_with_empty_collection(): void
    {
        $result = $this->callProtected('calculateTaskStats', [
            collect([]),
        ]);

        $this->assertEquals(0, $result['count']);
        $this->assertEquals(0, $result['completed']);
        $this->assertEquals(0, $result['percentage']);
    }

    public function test_task_stats_with_all_completed(): void
    {
        $submissions = collect([
            (object) ['submitted_at' => '2025-01-15 10:00:00'],
            (object) ['submitted_at' => '2025-01-16 14:30:00'],
            (object) ['submitted_at' => '2025-01-17 09:00:00'],
        ]);

        $result = $this->callProtected('calculateTaskStats', [
            $submissions,
        ]);

        $this->assertEquals(3, $result['count']);
        $this->assertEquals(3, $result['completed']);
        $this->assertEquals(100.0, $result['percentage']);
    }

    public function test_task_stats_with_none_completed(): void
    {
        $submissions = collect([
            (object) ['submitted_at' => null],
            (object) ['submitted_at' => null],
        ]);

        $result = $this->callProtected('calculateTaskStats', [
            $submissions,
        ]);

        $this->assertEquals(2, $result['count']);
        $this->assertEquals(0, $result['completed']);
        $this->assertEquals(0.0, $result['percentage']);
    }

    public function test_task_stats_with_partial_completion(): void
    {
        $submissions = collect([
            (object) ['submitted_at' => '2025-01-15 10:00:00'],
            (object) ['submitted_at' => null],
            (object) ['submitted_at' => '2025-01-17 09:00:00'],
            (object) ['submitted_at' => null],
        ]);

        $result = $this->callProtected('calculateTaskStats', [
            $submissions,
        ]);

        $this->assertEquals(4, $result['count']);
        $this->assertEquals(2, $result['completed']);
        $this->assertEquals(50.0, $result['percentage']);
    }

    public function test_task_stats_rounds_percentage(): void
    {
        $submissions = collect([
            (object) ['submitted_at' => '2025-01-15 10:00:00'],
            (object) ['submitted_at' => null],
            (object) ['submitted_at' => null],
        ]);

        $result = $this->callProtected('calculateTaskStats', [
            $submissions,
        ]);

        // 1/3 * 100 = 33.333...
        $this->assertEquals(33.33, $result['percentage']);
    }
}
