<?php

namespace App\Exports;

use App\Models\User;
use App\Models\Module;
use App\Services\GradingService;
use Illuminate\Support\Collection;

class ClassGradesExport
{
    protected GradingService $gradingService;
    protected string $section;

    public function __construct(string $section)
    {
        $this->gradingService = app(GradingService::class);
        $this->section = $section;
    }

    /**
     * Generate CSV content for class grade export.
     */
    public function generateCSV(): string
    {
        $data = $this->getData();
        $output = fopen('php://temp', 'r+');

        // Header row
        fputcsv($output, $this->getHeaders());

        // Data rows
        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        // Summary row
        fputcsv($output, $this->getSummaryRow($data));

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Get headers for the export.
     */
    protected function getHeaders(): array
    {
        $headers = [
            '#',
            'Student ID',
            'Name',
            'Self-Checks (%)',
            'Homeworks (%)',
            'Task Sheets (%)',
            'Job Sheets (%)',
            'Overall Average (%)',
            'Grade Code',
            'Descriptor',
            'Competency',
        ];

        return $headers;
    }

    /**
     * Get data for export.
     */
    protected function getData(): array
    {
        $students = $this->getStudents();
        $modules = Module::where('is_active', true)->get();
        $data = [];
        $index = 1;

        foreach ($students as $student) {
            $components = [
                'self_checks' => [],
                'homeworks' => [],
                'task_sheets' => [],
                'job_sheets' => [],
            ];

            // Aggregate all module grades
            foreach ($modules as $module) {
                $moduleGrade = $this->gradingService->calculateModuleGrade($student, $module);

                foreach ($moduleGrade['components'] as $type => $component) {
                    if (isset($components[$type]) && isset($component['percentage'])) {
                        $components[$type][] = $component['percentage'];
                    }
                }
            }

            // Calculate averages per component
            $selfCheckAvg = count($components['self_checks']) > 0
                ? round(array_sum($components['self_checks']) / count($components['self_checks']), 2) : 0;
            $homeworkAvg = count($components['homeworks']) > 0
                ? round(array_sum($components['homeworks']) / count($components['homeworks']), 2) : 0;
            $taskSheetAvg = count($components['task_sheets']) > 0
                ? round(array_sum($components['task_sheets']) / count($components['task_sheets']), 2) : 0;
            $jobSheetAvg = count($components['job_sheets']) > 0
                ? round(array_sum($components['job_sheets']) / count($components['job_sheets']), 2) : 0;

            // Overall average (weighted)
            $weights = [0.20, 0.30, 0.25, 0.25];
            $scores = [$selfCheckAvg, $homeworkAvg, $taskSheetAvg, $jobSheetAvg];
            $totalWeight = 0;
            $weightedSum = 0;

            for ($i = 0; $i < 4; $i++) {
                if ($scores[$i] > 0) {
                    $weightedSum += $scores[$i] * $weights[$i];
                    $totalWeight += $weights[$i];
                }
            }

            $overallAvg = $totalWeight > 0 ? round($weightedSum / $totalWeight, 2) : 0;
            $grade = $this->gradingService->applyGradingScale($overallAvg);

            $data[] = [
                $index++,
                $student->student_id ?? $student->id,
                $student->last_name . ', ' . $student->first_name,
                $selfCheckAvg,
                $homeworkAvg,
                $taskSheetAvg,
                $jobSheetAvg,
                $overallAvg,
                $grade['code'],
                $grade['descriptor'],
                $grade['competency_status'],
            ];
        }

        return $data;
    }

    /**
     * Get summary row for export.
     */
    protected function getSummaryRow(array $data): array
    {
        if (empty($data)) {
            return ['Summary', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-'];
        }

        $selfCheckSum = array_sum(array_column($data, 3));
        $homeworkSum = array_sum(array_column($data, 4));
        $taskSheetSum = array_sum(array_column($data, 5));
        $jobSheetSum = array_sum(array_column($data, 6));
        $overallSum = array_sum(array_column($data, 7));
        $count = count($data);

        $overallClassAvg = round($overallSum / $count, 2);
        $classGrade = $this->gradingService->applyGradingScale($overallClassAvg);

        // Count competent vs not competent
        $competentCount = 0;
        foreach ($data as $row) {
            if ($row[10] === 'Competent') {
                $competentCount++;
            }
        }

        return [
            'Class Average',
            $count . ' students',
            '-',
            round($selfCheckSum / $count, 2),
            round($homeworkSum / $count, 2),
            round($taskSheetSum / $count, 2),
            round($jobSheetSum / $count, 2),
            $overallClassAvg,
            $classGrade['code'],
            $classGrade['descriptor'],
            $competentCount . '/' . $count . ' Competent',
        ];
    }

    /**
     * Get students for export.
     */
    protected function getStudents(): Collection
    {
        return User::where('role', 'student')
            ->where('stat', 1)
            ->where('section', $this->section)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
    }
}
