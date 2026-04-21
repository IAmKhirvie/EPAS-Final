<?php

namespace App\Exports;

use App\Models\User;
use App\Models\Module;
use App\Models\Course;
use App\Services\GradingService;
use Illuminate\Support\Collection;

class GradesExport
{
    protected GradingService $gradingService;
    protected ?string $section;
    protected ?int $courseId;

    public function __construct(?string $section = null, ?int $courseId = null)
    {
        $this->gradingService = app(GradingService::class);
        $this->section = $section;
        $this->courseId = $courseId;
    }

    /**
     * Generate CSV content for grade export.
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
            'Student ID',
            'Last Name',
            'First Name',
            'Middle Name',
            'Section',
            'Email',
        ];

        // Add module headers
        $modules = $this->getModules();
        foreach ($modules as $module) {
            $headers[] = $module->module_name . ' (%)';
            $headers[] = $module->module_name . ' (Grade)';
        }

        $headers[] = 'Overall Average (%)';
        $headers[] = 'Overall Grade';
        $headers[] = 'Grade Code';
        $headers[] = 'Competency Status';

        return $headers;
    }

    /**
     * Get data for export.
     */
    protected function getData(): array
    {
        $students = $this->getStudents();
        $modules = $this->getModules();
        $data = [];

        foreach ($students as $student) {
            $row = [
                $student->student_id ?? $student->id,
                $student->last_name,
                $student->first_name,
                $student->middle_name ?? '',
                $student->section ?? '',
                $student->email,
            ];

            $totalPercentage = 0;
            $moduleCount = 0;

            foreach ($modules as $module) {
                $grade = $this->gradingService->calculateModuleGrade($student, $module);
                $row[] = $grade['percentage'];
                $row[] = $grade['grade']['code'];

                if ($grade['percentage'] > 0) {
                    $totalPercentage += $grade['percentage'];
                    $moduleCount++;
                }
            }

            $overallAverage = $moduleCount > 0 ? $totalPercentage / $moduleCount : 0;
            $overallGrade = $this->gradingService->applyGradingScale($overallAverage);

            $row[] = round($overallAverage, 2);
            $row[] = $overallGrade['descriptor'];
            $row[] = $overallGrade['code'];
            $row[] = $overallGrade['competency_status'];

            $data[] = $row;
        }

        return $data;
    }

    /**
     * Get students for export.
     */
    protected function getStudents(): Collection
    {
        $query = User::where('role', 'student')
            ->where('stat', 1);

        if ($this->section) {
            $query->where('section', $this->section);
        }

        return $query->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
    }

    /**
     * Get modules for export.
     */
    protected function getModules(): Collection
    {
        $query = Module::where('is_active', true);

        if ($this->courseId) {
            $query->where('course_id', $this->courseId);
        }

        return $query->orderBy('id')->get();
    }
}
