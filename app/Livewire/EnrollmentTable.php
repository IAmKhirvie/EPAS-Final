<?php

namespace App\Livewire;

use App\Constants\Roles;
use App\Models\EnrollmentRequest;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class EnrollmentTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $search = '';
    public string $statusFilter = 'all';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    public array $selectedRequests = [];
    public bool $selectAll = false;
    public bool $readyToLoad = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'all', 'as' => 'status'],
        'sortField' => ['except' => 'created_at', 'as' => 'sort'],
        'sortDirection' => ['except' => 'desc', 'as' => 'dir'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSelectAll(bool $value): void
    {
        $this->selectedRequests = $value
            ? $this->getQuery()->pluck('id')->map(fn ($id) => (string) $id)->toArray()
            : [];
    }

    public function bulkApprove(): void
    {
        if (Auth::user()->role !== Roles::ADMIN) {
            session()->flash('error', 'Only administrators can approve requests.');
            return;
        }

        $count = 0;
        $notificationService = app(NotificationService::class);
        $requests = EnrollmentRequest::whereIn('id', $this->selectedRequests)->where('status', 'pending')->get();
        foreach ($requests as $request) {
            $request->approve(Auth::user());
            if ($request->student) {
                $notificationService->notifyEnrollmentApproved($request->student, $request->section);
            }
            $count++;
        }

        $this->selectedRequests = [];
        $this->selectAll = false;
        session()->flash('success', "{$count} enrollment request(s) approved.");
    }

    public function bulkReject(): void
    {
        if (Auth::user()->role !== Roles::ADMIN) {
            session()->flash('error', 'Only administrators can reject requests.');
            return;
        }

        $count = 0;
        $notificationService = app(NotificationService::class);
        $requests = EnrollmentRequest::whereIn('id', $this->selectedRequests)->where('status', 'pending')->get();
        foreach ($requests as $request) {
            $request->reject(Auth::user(), 'Bulk rejected by admin.');
            if ($request->student) {
                $notificationService->notifyEnrollmentRejected($request->student, $request->section, 'Bulk rejected by admin.');
            }
            $count++;
        }

        $this->selectedRequests = [];
        $this->selectAll = false;
        session()->flash('success', "{$count} enrollment request(s) rejected.");
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

    public function approveRequest(int $id): void
    {
        if (Auth::user()->role !== Roles::ADMIN) {
            session()->flash('error', 'Only administrators can approve requests.');
            return;
        }

        try {
            $request = EnrollmentRequest::findOrFail($id);
            if (!$request->isPending()) {
                session()->flash('error', 'This request has already been processed.');
                return;
            }

            $request->approve(Auth::user());

            // Notify student of enrollment approval
            if ($request->student) {
                app(NotificationService::class)->notifyEnrollmentApproved($request->student, $request->section);
            }

            session()->flash('success', "Student {$request->student_display_name} has been enrolled in {$request->section}.");
        } catch (\Exception $e) {
            Log::error('Enrollment approval failed', ['error' => $e->getMessage(), 'enrollment_request_id' => $id]);
            session()->flash('error', 'Failed to approve enrollment request.');
        }
    }

    public function rejectRequest(int $id, ?string $notes = null): void
    {
        if (Auth::user()->role !== Roles::ADMIN) {
            session()->flash('error', 'Only administrators can reject requests.');
            return;
        }

        try {
            $request = EnrollmentRequest::findOrFail($id);
            if (!$request->isPending()) {
                session()->flash('error', 'This request has already been processed.');
                return;
            }

            $request->reject(Auth::user(), $notes ?? 'Rejected by admin.');

            // Notify student of enrollment rejection
            if ($request->student) {
                app(NotificationService::class)->notifyEnrollmentRejected($request->student, $request->section, $notes);
            }

            session()->flash('success', 'Enrollment request rejected.');
        } catch (\Exception $e) {
            Log::error('Enrollment rejection failed', ['error' => $e->getMessage(), 'enrollment_request_id' => $id]);
            session()->flash('error', 'Failed to reject enrollment request.');
        }
    }

    public function cancelRequest(int $id): void
    {
        try {
            $request = EnrollmentRequest::findOrFail($id);
            if ($request->instructor_id !== Auth::id()) {
                session()->flash('error', 'You can only cancel your own requests.');
                return;
            }
            if (!$request->isPending()) {
                session()->flash('error', 'Only pending requests can be cancelled.');
                return;
            }

            $request->delete();
            session()->flash('success', 'Enrollment request cancelled.');
        } catch (\Exception $e) {
            Log::error('Enrollment cancellation failed', ['error' => $e->getMessage(), 'enrollment_request_id' => $id]);
            session()->flash('error', 'Failed to cancel enrollment request.');
        }
    }

    private function getQuery()
    {
        $user = Auth::user();

        $query = EnrollmentRequest::with(['instructor', 'student', 'processedBy']);

        // Instructors only see their own requests
        if ($user->role === Roles::INSTRUCTOR) {
            $query->byInstructor($user->id);
        }

        // Status filter
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        // Search
        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                    ->orWhere('student_email', 'like', "%{$search}%")
                    ->orWhere('section', 'like', "%{$search}%")
                    ->orWhereHas('student', function ($sq) use ($search) {
                        $sq->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('student_id', 'like', "%{$search}%");
                    });
            });
        }

        $sortColumn = in_array($this->sortField, ['id', 'student_name', 'section', 'status', 'created_at'])
            ? $this->sortField : 'created_at';

        return $query->orderBy($sortColumn, $this->sortDirection);
    }

    private function getCounts(): array
    {
        $user = Auth::user();
        $base = EnrollmentRequest::query();

        if ($user->role === Roles::INSTRUCTOR) {
            $base->byInstructor($user->id);
        }

        $counts = $base->selectRaw("
            COUNT(*) as all_count,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
        ")->first();

        return [
            'all' => (int) $counts->all_count,
            'pending' => (int) $counts->pending,
            'approved' => (int) $counts->approved,
            'rejected' => (int) $counts->rejected,
        ];
    }

    public function loadData(): void
    {
        $this->readyToLoad = true;
    }

    public function render()
    {
        $user = Auth::user();

        return view('livewire.enrollment-table', [
            'requests' => $this->readyToLoad ? $this->getQuery()->paginate(15) : new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15),
            'counts' => $this->readyToLoad ? $this->getCounts() : ['all' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0],
            'isAdmin' => $user->role === Roles::ADMIN,
            'isInstructor' => $user->role === Roles::INSTRUCTOR,
        ]);
    }
}
