<?php

namespace App\Livewire;

use App\Constants\Roles;
use App\Models\HomeworkSubmission;
use App\Models\Module;
use App\Models\SelfCheckSubmission;
use App\Models\User;
use App\Models\UserProgress;
use App\Services\GradingService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class GradeTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $search = '';
    public string $sectionFilter = '';
    public string $sortField = 'last_name';
    public string $sortDirection = 'asc';
    public array $selectedStudents = [];
    public bool $selectAll = false;
    public bool $readyToLoad = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'sectionFilter' => ['except' => '', 'as' => 'section'],
        'sortField' => ['except' => 'last_name', 'as' => 'sort'],
        'sortDirection' => ['except' => 'asc', 'as' => 'dir'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSectionFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSelectAll(bool $value): void
    {
        $this->selectedStudents = $value
            ? $this->getStudentsQuery()->pluck('id')->map(fn ($id) => (string) $id)->toArray()
            : [];
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    private function getStudentsQuery()
    {
        $viewer = Auth::user();
        $query = User::where('role', Roles::STUDENT)->where('stat', 1);

        // Instructor scoping
        if ($viewer->role === Roles::INSTRUCTOR) {
            $instructorSections = $viewer->getAllAccessibleSections();
            if ($instructorSections->isNotEmpty()) {
                if ($this->sectionFilter && $instructorSections->contains($this->sectionFilter)) {
                    $query->where('section', $this->sectionFilter);
                } else {
                    $query->whereIn('section', $instructorSections);
                }
            } else {
                $query->where('id', 0);
            }
        } elseif ($this->sectionFilter) {
            $query->where('section', $this->sectionFilter);
        }

        // Search
        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('student_id', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $sortColumn = in_array($this->sortField, ['id', 'first_name', 'last_name', 'student_id', 'email', 'section'])
            ? $this->sortField : 'last_name';

        return $query->orderBy($sortColumn, $this->sortDirection);
    }

    public function loadData(): void
    {
        $this->readyToLoad = true;
    }

    public function render()
    {
        $viewer = Auth::user();
        $gradingService = app(GradingService::class);

        $students = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        $sections = collect();

        if ($this->readyToLoad) {
            $students = $this->getStudentsQuery()->paginate(config('joms.pagination.users', 20));

            // Pre-fetch all submissions for current page
            $studentIds = $students->getCollection()->pluck('id');

            $selfCheckAvgs = SelfCheckSubmission::whereIn('user_id', $studentIds)
                ->whereNotNull('percentage')
                ->selectRaw('user_id, MAX(percentage) as max_percentage')
                ->groupBy('user_id')
                ->pluck('max_percentage', 'user_id');

            $homeworkAvgs = HomeworkSubmission::whereIn('homework_submissions.user_id', $studentIds)
                ->whereNotNull('homework_submissions.score')
                ->join('homeworks', 'homeworks.id', '=', 'homework_submissions.homework_id')
                ->where('homeworks.max_points', '>', 0)
                ->selectRaw('homework_submissions.user_id, AVG(homework_submissions.score / homeworks.max_points * 100) as avg_score')
                ->groupBy('homework_submissions.user_id')
                ->pluck('avg_score', 'homework_submissions.user_id');

            $completedCounts = UserProgress::whereIn('user_id', $studentIds)
                ->where('status', 'completed')
                ->selectRaw('user_id, COUNT(*) as completed_count')
                ->groupBy('user_id')
                ->pluck('completed_count', 'user_id');

            // Add grade summary to each student
            $students->getCollection()->transform(function ($student) use ($selfCheckAvgs, $homeworkAvgs, $completedCounts, $gradingService) {
                $selfCheckAvg = $selfCheckAvgs->get($student->id, 0);
                $homeworkAvg = $homeworkAvgs->get($student->id, 0);
                $overallAvg = ($selfCheckAvg + $homeworkAvg) / 2;
                $grade = $gradingService->applyGradingScale($overallAvg);

                $student->grade_summary = [
                    'overall_average' => round($overallAvg, 1),
                    'self_check_average' => round($selfCheckAvg, 1),
                    'homework_average' => round($homeworkAvg, 1),
                    'completed_activities' => $completedCounts->get($student->id, 0),
                    'grade' => $grade,
                    'grade_descriptor' => $grade['descriptor'],
                    'grade_code' => $grade['code'],
                    'is_competent' => $grade['is_competent'],
                ];
                return $student;
            });

            // Sections for filter
            $sections = User::where('role', Roles::STUDENT)
                ->whereNotNull('section')
                ->distinct()
                ->pluck('section')
                ->sort();
        }

        return view('livewire.grade-table', [
            'students' => $students,
            'sections' => $sections,
            'viewer' => $viewer,
        ]);
    }
}
