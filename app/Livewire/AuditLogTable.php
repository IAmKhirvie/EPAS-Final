<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class AuditLogTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $search = '';
    public string $actionFilter = '';
    public string $userFilter = '';
    public string $dateFrom = '';
    public string $dateTo = '';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    public array $selectedLogs = [];
    public bool $selectAll = false;

    public bool $readyToLoad = false;

    public ?int $expandedLogId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'actionFilter' => ['except' => '', 'as' => 'action'],
        'userFilter' => ['except' => '', 'as' => 'user'],
        'dateFrom' => ['except' => '', 'as' => 'from'],
        'dateTo' => ['except' => '', 'as' => 'to'],
        'sortField' => ['except' => 'created_at', 'as' => 'sort'],
        'sortDirection' => ['except' => 'desc', 'as' => 'dir'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingActionFilter(): void
    {
        $this->resetPage();
    }

    public function updatingUserFilter(): void
    {
        $this->resetPage();
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    public function updatedSelectAll(bool $value): void
    {
        $this->selectedLogs = $value
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

    public function toggleExpand(int $logId): void
    {
        $this->expandedLogId = $this->expandedLogId === $logId ? null : $logId;
    }

    private function getQuery()
    {
        $query = AuditLog::with('user');

        // Action filter
        if ($this->actionFilter) {
            $query->where('action', $this->actionFilter);
        }

        // User filter
        if ($this->userFilter) {
            $query->where('user_id', $this->userFilter);
        }

        // Date range
        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        // Search
        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $sortColumn = in_array($this->sortField, ['id', 'action', 'created_at', 'ip_address'])
            ? $this->sortField : 'created_at';

        return $query->orderBy($sortColumn, $this->sortDirection);
    }

    public function loadData(): void
    {
        $this->readyToLoad = true;
    }

    public function render()
    {
        $actions = $this->readyToLoad ? AuditLog::distinct('action')->pluck('action') : collect();

        return view('livewire.audit-log-table', [
            'logs' => $this->readyToLoad ? $this->getQuery()->paginate(config('joms.pagination.audit_logs', 50)) : new \Illuminate\Pagination\LengthAwarePaginator([], 0, 50),
            'actions' => $actions,
        ]);
    }
}
