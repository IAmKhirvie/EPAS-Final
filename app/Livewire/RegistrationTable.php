<?php

namespace App\Livewire;

use App\Models\Registration;
use App\Services\DashboardStatisticsService;
use App\Services\RegistrationService;
use App\Services\PHPMailerService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class RegistrationTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $search = '';
    public string $statusFilter = 'pending';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    public array $selectedRegistrations = [];
    public bool $selectAll = false;
    public bool $readyToLoad = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'pending', 'as' => 'status'],
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
        $this->selectedRegistrations = $value
            ? $this->getQuery()->pluck('id')->map(fn ($id) => (string) $id)->toArray()
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

    public function approveRegistration(int $id): void
    {
        try {
            $registration = Registration::findOrFail($id);
            $service = new RegistrationService(app(PHPMailerService::class));
            $result = $service->approveRegistration($registration, Auth::id());

            if ($result['success']) {
                app(DashboardStatisticsService::class)->clearRegistrationCache();
                session()->flash('success', $result['message']);
            } else {
                session()->flash('error', $result['message']);
            }
        } catch (\Exception $e) {
            Log::error('Registration approval failed', ['error' => $e->getMessage(), 'registration_id' => $id]);
            session()->flash('error', 'Failed to approve registration.');
        }
    }

    public function rejectRegistration(int $id, ?string $reason = null): void
    {
        try {
            $registration = Registration::findOrFail($id);
            $service = new RegistrationService(app(PHPMailerService::class));
            $result = $service->rejectRegistration($registration, Auth::id(), $reason);

            if ($result['success']) {
                app(DashboardStatisticsService::class)->clearRegistrationCache();
                session()->flash('success', $result['message']);
            } else {
                session()->flash('error', $result['message']);
            }
        } catch (\Exception $e) {
            Log::error('Registration rejection failed', ['error' => $e->getMessage(), 'registration_id' => $id]);
            session()->flash('error', 'Failed to reject registration.');
        }
    }

    public function resendVerification(int $id): void
    {
        try {
            $registration = Registration::findOrFail($id);
            if ($registration->isEmailVerified()) {
                session()->flash('error', 'Email is already verified.');
                return;
            }

            $service = new RegistrationService(app(PHPMailerService::class));
            $sent = $service->resendVerificationEmail($registration);

            session()->flash($sent ? 'success' : 'error',
                $sent ? 'Verification email sent!' : 'Failed to send verification email.');
        } catch (\Exception $e) {
            Log::error('Resend verification failed', ['error' => $e->getMessage(), 'registration_id' => $id]);
            session()->flash('error', 'Failed to send verification email.');
        }
    }

    public function deleteRegistration(int $id): void
    {
        try {
            $registration = Registration::findOrFail($id);
            if ($registration->status !== Registration::STATUS_REJECTED) {
                session()->flash('error', 'Only rejected registrations can be deleted.');
                return;
            }
            $registration->delete();
            $this->selectedRegistrations = array_diff($this->selectedRegistrations, [(string) $id]);
            session()->flash('success', 'Registration deleted.');
        } catch (\Exception $e) {
            Log::error('Registration deletion failed', ['error' => $e->getMessage(), 'registration_id' => $id]);
            session()->flash('error', 'Failed to delete registration.');
        }
    }

    public function bulkApprove(): void
    {
        try {
            $service = new RegistrationService(app(PHPMailerService::class));
            $approved = 0;
            $failed = 0;

            foreach ($this->selectedRegistrations as $id) {
                $registration = Registration::find($id);
                if ($registration && $registration->status !== Registration::STATUS_TRANSFERRED) {
                    $result = $service->approveRegistration($registration, Auth::id());
                    $result['success'] ? $approved++ : $failed++;
                }
            }

            app(DashboardStatisticsService::class)->clearRegistrationCache();
            $this->selectedRegistrations = [];
            $this->selectAll = false;
            session()->flash('success', "Approved {$approved} registrations." . ($failed > 0 ? " {$failed} failed." : ''));
        } catch (\Exception $e) {
            Log::error('Bulk approval failed', ['error' => $e->getMessage()]);
            session()->flash('error', 'Failed to approve registrations.');
        }
    }

    public function bulkReject(): void
    {
        try {
            $service = new RegistrationService(app(PHPMailerService::class));
            $rejected = 0;

            foreach ($this->selectedRegistrations as $id) {
                $registration = Registration::find($id);
                if ($registration && !in_array($registration->status, [Registration::STATUS_TRANSFERRED, Registration::STATUS_REJECTED])) {
                    $service->rejectRegistration($registration, Auth::id());
                    $rejected++;
                }
            }

            app(DashboardStatisticsService::class)->clearRegistrationCache();
            $this->selectedRegistrations = [];
            $this->selectAll = false;
            session()->flash('success', "Rejected {$rejected} registrations.");
        } catch (\Exception $e) {
            Log::error('Bulk rejection failed', ['error' => $e->getMessage()]);
            session()->flash('error', 'Failed to reject registrations.');
        }
    }

    private function getQuery()
    {
        $query = Registration::query();

        // Status filter
        match ($this->statusFilter) {
            'pending' => $query->pending(),
            'email_verified' => $query->awaitingApproval(),
            'rejected' => $query->where('status', Registration::STATUS_REJECTED),
            'all' => $query->whereNotIn('status', [Registration::STATUS_TRANSFERRED]),
            default => $query->pending(),
        };

        // Search
        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('middle_name', 'like', "%{$search}%");
            });
        }

        $sortColumn = in_array($this->sortField, ['id', 'first_name', 'last_name', 'email', 'status', 'created_at'])
            ? $this->sortField : 'created_at';

        return $query->orderBy($sortColumn, $this->sortDirection);
    }

    private function getCounts(): array
    {
        return [
            'pending' => Registration::where('status', Registration::STATUS_PENDING)->count(),
            'email_verified' => Registration::where('status', Registration::STATUS_EMAIL_VERIFIED)->count(),
            'rejected' => Registration::where('status', Registration::STATUS_REJECTED)->count(),
            'all' => Registration::whereNotIn('status', [Registration::STATUS_TRANSFERRED])->count(),
        ];
    }

    public function loadData(): void
    {
        $this->readyToLoad = true;
    }

    public function render()
    {
        return view('livewire.registration-table', [
            'registrations' => $this->readyToLoad ? $this->getQuery()->paginate(20) : new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20),
            'counts' => $this->readyToLoad ? $this->getCounts() : ['pending' => 0, 'email_verified' => 0, 'rejected' => 0, 'all' => 0],
        ]);
    }
}
