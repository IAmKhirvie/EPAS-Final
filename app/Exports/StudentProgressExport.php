<?php

namespace App\Exports;

use App\Models\User;
use App\Models\UserProgress;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class StudentProgressExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = User::where('role', 'student')
            ->where('stat', 1)
            ->with(['progress' => function ($q) {
                $q->where('progressable_type', 'App\\Models\\Module');
            }]);

        if (!empty($this->filters['section'])) {
            $query->where('section', $this->filters['section']);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Student ID',
            'Name',
            'Email',
            'Section',
            'Total Points',
            'Modules Completed',
            'Average Score',
            'Current Streak',
            'Last Activity',
        ];
    }

    public function map($user): array
    {
        $completedModules = $user->progress->where('status', 'completed')->count();
        $averageScore = $user->progress->whereNotNull('score')->avg('score');

        return [
            $user->student_id ?? $user->id,
            $user->full_name,
            $user->email,
            $user->section ?? 'N/A',
            $user->total_points,
            $completedModules,
            $averageScore ? round($averageScore, 1) . '%' : 'N/A',
            $user->current_streak . ' days',
            $user->last_activity_date ?? 'Never',
        ];
    }
}
