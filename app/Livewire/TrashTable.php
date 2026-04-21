<?php

namespace App\Livewire;

use App\Constants\Roles;
use App\Models\Module;
use App\Models\Topic;
use App\Models\InformationSheet;
use App\Models\Homework;
use App\Models\SelfCheck;
use App\Models\TaskSheet;
use App\Models\JobSheet;
use App\Models\Checklist;
use App\Models\Course;
use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class TrashTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $search = '';
    public string $typeFilter = 'all';
    public string $sortDirection = 'desc';

    public array $selectedItems = [];
    public bool $selectAll = false;
    public bool $readyToLoad = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'typeFilter' => ['except' => 'all', 'as' => 'type'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTypeFilter(): void
    {
        $this->resetPage();
        $this->selectedItems = [];
        $this->selectAll = false;
    }

    public function updatedSelectAll(bool $value): void
    {
        $this->selectedItems = $value
            ? $this->getTrashedItems()->pluck('unique_key')->toArray()
            : [];
    }

    public function toggleSort(): void
    {
        $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
    }

    private function restoreCourseChildren(Course $course): void
    {
        $course->update(['is_active' => true]);

        $modules = Module::onlyTrashed()->where('course_id', $course->id)->get();
        foreach ($modules as $module) {
            $module->restore();
            $module->update(['is_active' => true]);
            $this->restoreModuleChildren($module);
        }
    }

    private function restoreModuleChildren(Module $module): void
    {
        $sheets = InformationSheet::onlyTrashed()->where('module_id', $module->id)->get();
        foreach ($sheets as $sheet) {
            $sheet->restore();
            $this->restoreSheetChildren($sheet);
        }
    }

    private function restoreSheetChildren(InformationSheet $sheet): void
    {
        SelfCheck::onlyTrashed()->where('information_sheet_id', $sheet->id)->restore();
        TaskSheet::onlyTrashed()->where('information_sheet_id', $sheet->id)->restore();
        JobSheet::onlyTrashed()->where('information_sheet_id', $sheet->id)->restore();
        Homework::onlyTrashed()->where('information_sheet_id', $sheet->id)->restore();
        Checklist::onlyTrashed()->where('information_sheet_id', $sheet->id)->restore();
        Topic::onlyTrashed()->where('information_sheet_id', $sheet->id)->restore();
    }

    public function restoreItem(string $type, int $id): void
    {
        try {
            $model = $this->getModelInstance($type, $id);

            if (!$model || !$this->canManageItem($model, $type)) {
                session()->flash('error', 'Item not found or unauthorized.');
                return;
            }

            // Restore parents first, then the item, then children
            $this->restoreParents($type, $model);
            $model->restore();

            match ($type) {
                'course' => $this->restoreCourseChildren($model),
                'module' => $this->restoreModuleChildren($model),
                'information_sheet' => $this->restoreSheetChildren($model),
                default => null,
            };

            $uniqueKey = "{$type}_{$id}";
            $this->selectedItems = array_diff($this->selectedItems, [$uniqueKey]);

            // Clear the trash count cache
            Cache::forget("trash_count_" . Auth::id());

            session()->flash('success', ucfirst(str_replace('_', ' ', $type)) . ' restored successfully.');
        } catch (\Exception $e) {
            Log::error('Restore failed', ['type' => $type, 'id' => $id, 'error' => $e->getMessage()]);
            session()->flash('error', 'Failed to restore item.');
        }
    }

    private function restoreParents(string $type, $model): void
    {
        match ($type) {
            'information_sheet' => $this->ensureModuleAndCourseRestored($model->module_id),
            'topic', 'self_check', 'task_sheet', 'job_sheet', 'homework', 'checklist' =>
            $this->ensureSheetParentsRestored($model),
            'module' => $this->ensureCourseRestored($model->course_id),
            default => null,
        };
    }

    private function ensureModuleAndCourseRestored(int $moduleId): void
    {
        $module = Module::withTrashed()->find($moduleId);
        if (!$module) return;

        // Restore course if deleted
        $this->ensureCourseRestored($module->course_id);

        // Restore module if deleted
        if ($module->trashed()) {
            $module->restore();
        }
    }

    private function ensureCourseRestored(int $courseId): void
    {
        $course = Course::withTrashed()->find($courseId);
        if ($course && $course->trashed()) {
            $course->restore();
            $course->update(['is_active' => true]);
        }
    }

    private function ensureSheetParentsRestored($model): void
    {
        $sheet = InformationSheet::withTrashed()->find($model->information_sheet_id);
        if (!$sheet) return;

        $this->ensureModuleAndCourseRestored($sheet->module_id);

        if ($sheet->trashed()) {
            $sheet->restore();
        }
    }

    public function forceDeleteItem(string $type, int $id): void
    {
        try {
            $model = $this->getModelInstance($type, $id);

            if (!$model) {
                session()->flash('error', 'Item not found.');
                return;
            }

            if (!$this->canManageItem($model, $type)) {
                session()->flash('error', 'You do not have permission to delete this item.');
                return;
            }

            $this->forceDeleteWithChildren($model, $type);

            // Remove from selection
            $uniqueKey = "{$type}_{$id}";
            $this->selectedItems = array_diff($this->selectedItems, [$uniqueKey]);

            // Clear the trash count cache
            Cache::forget("trash_count_" . Auth::id());

            session()->flash('success', ucfirst($type) . ' permanently deleted.');
        } catch (\Exception $e) {
            Log::error('Force delete failed', ['type' => $type, 'id' => $id, 'error' => $e->getMessage()]);
            session()->flash('error', 'Failed to delete item.');
        }
    }

    public function bulkRestore(): void
    {
        try {
            $restored = 0;

            foreach ($this->selectedItems as $uniqueKey) {
                $lastUnderscore = strrpos($uniqueKey, '_');
                $type = substr($uniqueKey, 0, $lastUnderscore);
                $id = (int) substr($uniqueKey, $lastUnderscore + 1);
                $model = $this->getModelInstance($type, $id);

                if ($model && $this->canManageItem($model, $type)) {
                    $this->restoreParents($type, $model); // ← add this
                    $model->restore();
                    match ($type) {
                        'course' => $this->restoreCourseChildren($model),
                        'module' => $this->restoreModuleChildren($model),
                        'information_sheet' => $this->restoreSheetChildren($model),
                        default => null,
                    };
                    $restored++;
                }
            }

            $this->selectedItems = [];
            $this->selectAll = false;

            // Clear the trash count cache
            Cache::forget("trash_count_" . Auth::id());

            session()->flash('success', "{$restored} item(s) restored successfully.");
        } catch (\Exception $e) {
            Log::error('Bulk restore failed', ['error' => $e->getMessage()]);
            session()->flash('error', 'Failed to restore items.');
        }
    }

    public function bulkForceDelete(): void
    {
        try {
            $deleted = 0;
            $errors = 0;

            foreach ($this->selectedItems as $uniqueKey) {
                $lastUnderscore = strrpos($uniqueKey, '_');
                $type = substr($uniqueKey, 0, $lastUnderscore);
                $id = (int) substr($uniqueKey, $lastUnderscore + 1);
                $model = $this->getModelInstance($type, $id);

                if ($model && $this->canManageItem($model, $type)) {
                    try {
                        $this->forceDeleteWithChildren($model, $type);
                        $deleted++;
                    } catch (\Exception $e) {
                        Log::error("Force delete failed for {$type}#{$id}", ['error' => $e->getMessage()]);
                        $errors++;
                    }
                }
            }

            $this->selectedItems = [];
            $this->selectAll = false;

            Cache::forget("trash_count_" . Auth::id());

            if ($errors > 0) {
                session()->flash('warning', "{$deleted} item(s) deleted, {$errors} failed.");
            } else {
                session()->flash('success', "{$deleted} item(s) permanently deleted.");
            }
        } catch (\Exception $e) {
            Log::error('Bulk force delete failed', ['error' => $e->getMessage()]);
            session()->flash('error', 'Failed to delete items.');
        }
    }

    private function forceDeleteWithChildren($model, string $type): void
    {
        \DB::transaction(function () use ($model, $type) {
            match ($type) {
                'course' => $this->forceDeleteCourse($model),
                'module' => $this->forceDeleteModule($model),
                'information_sheet' => $this->forceDeleteSheet($model),
                'self_check' => $this->forceDeleteSelfCheck($model),
                'homework' => (function() use ($model) { $model->submissions()->delete(); $model->forceDelete(); })(),
                'task_sheet' => (function() use ($model) { $model->submissions()->delete(); $model->forceDelete(); })(),
                'job_sheet' => (function() use ($model) { $model->submissions()->delete(); $model->forceDelete(); })(),
                default => $model->forceDelete(),
            };
        });
    }

    private function forceDeleteSelfCheck($selfCheck): void
    {
        $selfCheck->submissions()->delete();
        $selfCheck->questions()->forceDelete();
        $selfCheck->forceDelete();
    }

    private function forceDeleteCourse(Course $course): void
    {
        foreach ($course->modules()->withTrashed()->get() as $module) {
            $this->forceDeleteModule($module);
        }
        $course->forceDelete();
    }

    private function forceDeleteModule(Module $module): void
    {
        foreach ($module->informationSheets()->withTrashed()->get() as $sheet) {
            $this->forceDeleteSheet($sheet);
        }
        $module->forceDelete();
    }

    private function forceDeleteSheet(InformationSheet $sheet): void
    {
        $sheet->topics()->withTrashed()->forceDelete();
        foreach ($sheet->selfChecks()->withTrashed()->get() as $sc) {
            $sc->submissions()->delete();
            $sc->questions()->forceDelete();
            $sc->forceDelete();
        }
        foreach ($sheet->homeworks()->withTrashed()->get() as $hw) {
            $hw->submissions()->delete();
            $hw->forceDelete();
        }
        foreach ($sheet->taskSheets()->withTrashed()->get() as $ts) {
            $ts->submissions()->delete();
            $ts->forceDelete();
        }
        foreach ($sheet->jobSheets()->withTrashed()->get() as $js) {
            $js->submissions()->delete();
            $js->forceDelete();
        }
        foreach ($sheet->checklists()->withTrashed()->get() as $cl) {
            $cl->forceDelete();
        }
        $sheet->forceDelete();
    }

    private function getModelInstance(string $type, int $id)
    {
        return match ($type) {
            'module' => Module::onlyTrashed()->find($id),
            'topic' => Topic::onlyTrashed()->find($id),
            'information_sheet' => InformationSheet::onlyTrashed()->find($id),
            'homework' => Homework::onlyTrashed()->find($id),
            'self_check' => SelfCheck::onlyTrashed()->find($id),
            'task_sheet' => TaskSheet::onlyTrashed()->find($id),
            'job_sheet' => JobSheet::onlyTrashed()->find($id),
            'checklist' => Checklist::onlyTrashed()->find($id),
            'course' => Course::onlyTrashed()->find($id),
            'announcement' => Announcement::onlyTrashed()->find($id),
            default => null,
        };
    }

    private function canManageItem($model, string $type): bool
    {
        $user = Auth::user();

        // Admins can manage everything
        if ($user->role === Roles::ADMIN) {
            return true;
        }

        // Instructors can only manage their own content
        if ($user->role === Roles::INSTRUCTOR) {
            return match ($type) {
                'module' => $model->course && $model->course->instructor_id === $user->id,
                'topic' => $model->informationSheet && $model->informationSheet->module && $model->informationSheet->module->course && $model->informationSheet->module->course->instructor_id === $user->id,
                'information_sheet' => $model->module && $model->module->course && $model->module->course->instructor_id === $user->id,
                'homework', 'self_check', 'task_sheet', 'job_sheet', 'checklist' =>
                $model->informationSheet && $model->informationSheet->module &&
                    $model->informationSheet->module->course &&
                    $model->informationSheet->module->course->instructor_id === $user->id,
                'course' => $model->instructor_id === $user->id,
                'announcement' => $model->user_id === $user->id,
                default => false,
            };
        }

        return false;
    }

    private function getTrashedItems(): Collection
    {
        $user = Auth::user();
        $isAdmin = $user->role === Roles::ADMIN;
        $items = collect();

        // Get instructor's course IDs for filtering
        $instructorCourseIds = [];
        if (!$isAdmin) {
            $instructorCourseIds = Course::where('instructor_id', $user->id)->pluck('id')->toArray();
        }

        // Helper to add items with common structure
        $addItems = function ($query, $type, $nameField, $getParentName = null) use (&$items, $isAdmin, $instructorCourseIds, $user) {
            $trashedItems = $query->onlyTrashed();

            // Apply instructor filter if not admin
            if (!$isAdmin) {
                $trashedItems = match ($type) {
                    'module' => $trashedItems->whereIn('course_id', $instructorCourseIds),
                    'topic' => $trashedItems->whereHas('informationSheet.module', fn($q) => $q->whereIn('course_id', $instructorCourseIds)),
                    'information_sheet' => $trashedItems->whereHas('module', fn($q) => $q->whereIn('course_id', $instructorCourseIds)),
                    'homework', 'self_check', 'task_sheet', 'job_sheet', 'checklist' =>
                    $trashedItems->whereHas('informationSheet.module', fn($q) => $q->whereIn('course_id', $instructorCourseIds)),
                    'course' => $trashedItems->where('instructor_id', $user->id),
                    'announcement' => $trashedItems->where('user_id', $user->id),
                    default => $trashedItems,
                };
            }

            foreach ($trashedItems->get() as $item) {
                $name = $item->$nameField ?? $item->name ?? $item->title ?? 'Unnamed';
                $parentName = $getParentName ? $getParentName($item) : null;

                $items->push([
                    'id' => $item->id,
                    'type' => $type,
                    'type_label' => $this->getTypeLabel($type),
                    'name' => $name,
                    'parent_name' => $parentName,
                    'deleted_at' => $item->deleted_at,
                    'unique_key' => "{$type}_{$item->id}",
                ]);
            }
        };

        // Only fetch items based on type filter
        $types = $this->typeFilter === 'all'
            ? ['module', 'topic', 'information_sheet', 'homework', 'self_check', 'task_sheet', 'job_sheet', 'checklist', 'course', 'announcement']
            : [$this->typeFilter];

        foreach ($types as $type) {
            match ($type) {
                'module' => $addItems(Module::query(), 'module', 'module_title', fn($m) => $m->course?->name),
                'topic' => $addItems(Topic::query(), 'topic', 'title', fn($t) => $t->informationSheet?->title),
                'information_sheet' => $addItems(InformationSheet::query(), 'information_sheet', 'title', fn($s) => $s->module?->module_title),
                'homework' => $addItems(Homework::query(), 'homework', 'title', fn($h) => $h->informationSheet?->title),
                'self_check' => $addItems(SelfCheck::query(), 'self_check', 'title', fn($s) => $s->informationSheet?->title),
                'task_sheet' => $addItems(TaskSheet::query(), 'task_sheet', 'title', fn($t) => $t->informationSheet?->title),
                'job_sheet' => $addItems(JobSheet::query(), 'job_sheet', 'title', fn($j) => $j->informationSheet?->title),
                'checklist' => $addItems(Checklist::query(), 'checklist', 'title', fn($c) => $c->informationSheet?->title),
                'course' => $isAdmin ? $addItems(Course::query(), 'course', 'name', null) : null,
                'announcement' => $addItems(Announcement::query(), 'announcement', 'title', null),
                default => null,
            };
        }

        // Apply search filter
        if ($this->search) {
            $search = strtolower($this->search);
            $items = $items->filter(function ($item) use ($search) {
                return str_contains(strtolower($item['name']), $search) ||
                    ($item['parent_name'] && str_contains(strtolower($item['parent_name']), $search));
            });
        }

        // Sort by deleted_at
        $items = $this->sortDirection === 'desc'
            ? $items->sortByDesc('deleted_at')
            : $items->sortBy('deleted_at');

        return $items->values();
    }

    private function getTypeLabel(string $type): string
    {
        return match ($type) {
            'module' => 'Module',
            'topic' => 'Topic',
            'information_sheet' => 'Information Sheet',
            'homework' => 'Homework',
            'self_check' => 'Self Check',
            'task_sheet' => 'Task Sheet',
            'job_sheet' => 'Job Sheet',
            'checklist' => 'Checklist',
            'course' => 'Course',
            'announcement' => 'Announcement',
            default => ucfirst(str_replace('_', ' ', $type)),
        };
    }

    private function getCounts(): array
    {
        $user = Auth::user();
        $isAdmin = $user->role === Roles::ADMIN;

        $instructorCourseIds = [];
        if (!$isAdmin) {
            $instructorCourseIds = Course::where('instructor_id', $user->id)->pluck('id')->toArray();
        }

        $counts = ['all' => 0];

        $countQuery = function ($query, $type) use ($isAdmin, $instructorCourseIds, $user) {
            $trashedQuery = $query->onlyTrashed();

            if (!$isAdmin) {
                $trashedQuery = match ($type) {
                    'module' => $trashedQuery->whereIn('course_id', $instructorCourseIds),
                    'topic' => $trashedQuery->whereHas('informationSheet.module', fn($q) => $q->whereIn('course_id', $instructorCourseIds)),
                    'information_sheet' => $trashedQuery->whereHas('module', fn($q) => $q->whereIn('course_id', $instructorCourseIds)),
                    'homework', 'self_check', 'task_sheet', 'job_sheet', 'checklist' =>
                    $trashedQuery->whereHas('informationSheet.module', fn($q) => $q->whereIn('course_id', $instructorCourseIds)),
                    'course' => $trashedQuery->where('instructor_id', $user->id),
                    'announcement' => $trashedQuery->where('user_id', $user->id),
                    default => $trashedQuery,
                };
            }

            return $trashedQuery->count();
        };

        $counts['module'] = $countQuery(Module::query(), 'module');
        $counts['topic'] = $countQuery(Topic::query(), 'topic');
        $counts['information_sheet'] = $countQuery(InformationSheet::query(), 'information_sheet');
        $counts['homework'] = $countQuery(Homework::query(), 'homework');
        $counts['self_check'] = $countQuery(SelfCheck::query(), 'self_check');
        $counts['task_sheet'] = $countQuery(TaskSheet::query(), 'task_sheet');
        $counts['job_sheet'] = $countQuery(JobSheet::query(), 'job_sheet');
        $counts['checklist'] = $countQuery(Checklist::query(), 'checklist');

        if ($isAdmin) {
            $counts['course'] = $countQuery(Course::query(), 'course');
        }

        $counts['announcement'] = $countQuery(Announcement::query(), 'announcement');

        $counts['all'] = array_sum(array_filter($counts, fn($k) => $k !== 'all', ARRAY_FILTER_USE_KEY));

        return $counts;
    }

    public function loadData(): void
    {
        $this->readyToLoad = true;
    }

    public function render()
    {
        if (!$this->readyToLoad) {
            return view('livewire.trash-table', [
                'items' => collect(),
                'counts' => ['all' => 0],
                'total' => 0,
                'perPage' => 20,
                'currentPage' => 1,
                'lastPage' => 1,
                'from' => 0,
                'to' => 0,
                'isAdmin' => Auth::user()->role === Roles::ADMIN,
            ]);
        }

        $items = $this->getTrashedItems();
        $perPage = 20;
        $page = $this->getPage();
        $total = $items->count();

        // Manual pagination
        $paginatedItems = $items->slice(($page - 1) * $perPage, $perPage)->values();

        return view('livewire.trash-table', [
            'items' => $paginatedItems,
            'counts' => $this->getCounts(),
            'total' => $total,
            'perPage' => $perPage,
            'currentPage' => $page,
            'lastPage' => ceil($total / $perPage) ?: 1,
            'from' => $total > 0 ? (($page - 1) * $perPage) + 1 : 0,
            'to' => min($page * $perPage, $total),
            'isAdmin' => Auth::user()->role === Roles::ADMIN,
        ]);
    }
}
