<?php

namespace App\Services;

use App\Models\Module;
use App\Models\InformationSheet;
use App\Models\TaskSheet;
use App\Models\JobSheet;
use Illuminate\Support\Facades\View;

class ContentExportService
{
    /**
     * Generate HTML content for PDF export.
     */
    public function generateModuleHtml(Module $module): string
    {
        $module->load([
            'informationSheets.topics',
            'informationSheets.selfChecks.questions',
            'informationSheets.taskSheets',
            'informationSheets.jobSheets',
            'informationSheets.homeworks',
        ]);

        return View::make('exports.module-pdf', [
            'module' => $module,
            'exportDate' => now()->format('F j, Y'),
        ])->render();
    }

    /**
     * Generate HTML content for Information Sheet PDF export.
     */
    public function generateSheetHtml(InformationSheet $sheet): string
    {
        $sheet->load([
            'topics',
            'selfChecks.questions',
            'taskSheets',
            'jobSheets',
            'homeworks',
            'module',
        ]);

        return View::make('exports.sheet-pdf', [
            'sheet' => $sheet,
            'exportDate' => now()->format('F j, Y'),
        ])->render();
    }

    /**
     * Generate HTML content for Task Sheet PDF export.
     */
    public function generateTaskSheetHtml(TaskSheet $taskSheet): string
    {
        $taskSheet->load(['informationSheet.module']);

        return View::make('exports.task-sheet-pdf', [
            'taskSheet' => $taskSheet,
            'exportDate' => now()->format('F j, Y'),
        ])->render();
    }

    /**
     * Generate HTML content for Job Sheet PDF export.
     */
    public function generateJobSheetHtml(JobSheet $jobSheet): string
    {
        $jobSheet->load(['informationSheet.module', 'steps']);

        return View::make('exports.job-sheet-pdf', [
            'jobSheet' => $jobSheet,
            'exportDate' => now()->format('F j, Y'),
        ])->render();
    }

    /**
     * Get module content as structured data for offline package.
     */
    public function getModuleOfflineData(Module $module): array
    {
        $module->load([
            'informationSheets.topics',
            'informationSheets.selfChecks.questions',
            'informationSheets.taskSheets',
            'informationSheets.jobSheets',
            'informationSheets.homeworks',
            'course',
        ]);

        return [
            'module' => [
                'id' => $module->id,
                'title' => $module->module_title,
                'name' => $module->module_name,
                'number' => $module->module_number,
                'qualification' => $module->qualification_title,
                'unit_of_competency' => $module->unit_of_competency,
                'introduction' => $module->introduction,
                'learning_outcomes' => $module->learning_outcomes,
            ],
            'course' => $module->course ? [
                'name' => $module->course->course_name,
                'code' => $module->course->course_code,
            ] : null,
            'information_sheets' => $module->informationSheets->map(function ($sheet) {
                return [
                    'id' => $sheet->id,
                    'title' => $sheet->title,
                    'learning_objective' => $sheet->learning_objective,
                    'introduction' => $sheet->introduction,
                    'topics' => $sheet->topics->map(function ($topic) {
                        return [
                            'id' => $topic->id,
                            'title' => $topic->title,
                            'content' => $topic->content,
                            'order' => $topic->order,
                        ];
                    }),
                    'self_checks' => $sheet->selfChecks->map(function ($sc) {
                        return [
                            'id' => $sc->id,
                            'title' => $sc->title,
                            'instructions' => $sc->instructions,
                            'questions' => $sc->questions->map(function ($q) {
                                return [
                                    'question' => $q->question,
                                    'type' => $q->question_type,
                                    'options' => $q->options,
                                ];
                            }),
                        ];
                    }),
                    'task_sheets' => $sheet->taskSheets->map(function ($ts) {
                        return [
                            'id' => $ts->id,
                            'title' => $ts->title,
                            'description' => $ts->description,
                            'instructions' => $ts->instructions,
                        ];
                    }),
                    'job_sheets' => $sheet->jobSheets->map(function ($js) {
                        return [
                            'id' => $js->id,
                            'title' => $js->title,
                            'description' => $js->description,
                            'tools_materials' => $js->tools_materials,
                        ];
                    }),
                ];
            }),
            'exported_at' => now()->toIso8601String(),
        ];
    }
}
