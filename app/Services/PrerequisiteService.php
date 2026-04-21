<?php

namespace App\Services;

use App\Models\Module;
use App\Models\ModulePrerequisite;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class PrerequisiteService
{
    /**
     * Add a prerequisite to a module.
     *
     * @throws \InvalidArgumentException if circular dependency is detected
     */
    public function addPrerequisite(Module $module, Module $prerequisite): bool
    {
        // Cannot set self as prerequisite
        if ($module->id === $prerequisite->id) {
            throw new \InvalidArgumentException('A module cannot be its own prerequisite.');
        }

        // Check for circular dependency
        if ($this->detectCircularDependency($module->id, $prerequisite->id)) {
            throw new \InvalidArgumentException(
                "Cannot add prerequisite: This would create a circular dependency. " .
                "Module '{$prerequisite->module_title}' already requires '{$module->module_title}' directly or indirectly."
            );
        }

        // Check if already exists
        $exists = ModulePrerequisite::where('module_id', $module->id)
            ->where('prerequisite_module_id', $prerequisite->id)
            ->exists();

        if ($exists) {
            return true; // Already a prerequisite
        }

        ModulePrerequisite::create([
            'module_id' => $module->id,
            'prerequisite_module_id' => $prerequisite->id,
        ]);

        // Clear cache
        $this->clearPrerequisiteCache($module->id);

        return true;
    }

    /**
     * Remove a prerequisite from a module.
     */
    public function removePrerequisite(Module $module, Module $prerequisite): bool
    {
        $deleted = ModulePrerequisite::where('module_id', $module->id)
            ->where('prerequisite_module_id', $prerequisite->id)
            ->delete();

        // Clear cache
        $this->clearPrerequisiteCache($module->id);

        return $deleted > 0;
    }

    /**
     * Detect if adding a prerequisite would create a circular dependency.
     * Uses depth-first search to traverse the prerequisite chain.
     */
    public function detectCircularDependency(int $moduleId, int $newPrerequisiteId, array $visited = []): bool
    {
        // If the new prerequisite is the same as the original module, it's a direct cycle
        if ($moduleId === $newPrerequisiteId) {
            return true;
        }

        // If we've already visited this node in this path, no cycle found in this branch
        if (in_array($newPrerequisiteId, $visited)) {
            return false;
        }

        $visited[] = $newPrerequisiteId;

        // Get all prerequisites of the new prerequisite module
        $prerequisites = ModulePrerequisite::where('module_id', $newPrerequisiteId)
            ->pluck('prerequisite_module_id');

        foreach ($prerequisites as $prereqId) {
            if ($this->detectCircularDependency($moduleId, $prereqId, $visited)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a user can access a module based on prerequisites.
     */
    public function canUserAccessModule(User $user, Module $module): bool
    {
        $unmetPrerequisites = $this->getUnmetPrerequisites($user, $module);
        return $unmetPrerequisites->isEmpty();
    }

    /**
     * Get all unmet prerequisites for a user and module.
     */
    public function getUnmetPrerequisites(User $user, Module $module): Collection
    {
        $cacheKey = "unmet_prerequisites_{$user->id}_{$module->id}";

        return Cache::remember($cacheKey, 300, function () use ($user, $module) {
            $unmet = collect();

            // Check manually configured prerequisites
            $prerequisites = $module->prerequisites()->with('prerequisiteModule')->get();
            foreach ($prerequisites as $prerequisite) {
                $prereqModule = $prerequisite->prerequisiteModule;
                if ($prereqModule && !$prereqModule->isCompletedBy($user)) {
                    $unmet->push($prereqModule);
                }
            }

            // Auto-sequential: if course enforces sequential modules,
            // all previous modules (by order) must be completed first
            $course = $module->course;
            if ($course && $course->enforce_sequential_modules) {
                $previousModules = Module::where('course_id', $course->id)
                    ->where('is_active', true)
                    ->where('order', '<', $module->order)
                    ->orderBy('order')
                    ->get();

                foreach ($previousModules as $prev) {
                    if (!$prev->isCompletedBy($user) && !$unmet->contains('id', $prev->id)) {
                        $unmet->push($prev);
                    }
                }
            }

            return $unmet;
        });
    }

    /**
     * Get all modules accessible to a user in a course.
     */
    public function getAccessibleModulesFor(User $user, $course): Collection
    {
        $modules = $course->modules()->where('is_active', true)->get();

        return $modules->filter(function ($module) use ($user) {
            return $this->canUserAccessModule($user, $module);
        });
    }

    /**
     * Get all modules in a course with their lock status for a user.
     */
    public function getModulesWithLockStatus(User $user, $course): Collection
    {
        $modules = $course->modules()->where('is_active', true)->orderBy('order')->get();

        return $modules->map(function ($module) use ($user) {
            $unmetPrereqs = $this->getUnmetPrerequisites($user, $module);
            $module->is_locked = $unmetPrereqs->isNotEmpty();
            $module->unmet_prerequisites = $unmetPrereqs;
            return $module;
        });
    }

    /**
     * Sync prerequisites for a module (replace all existing with new set).
     */
    public function syncPrerequisites(Module $module, array $prerequisiteIds): void
    {
        // Filter out empty values and the module's own ID
        $prerequisiteIds = array_filter($prerequisiteIds, function ($id) use ($module) {
            return !empty($id) && $id != $module->id;
        });

        // Validate no circular dependencies
        foreach ($prerequisiteIds as $prereqId) {
            if ($this->wouldCreateCircularDependency($module->id, (int) $prereqId, $prerequisiteIds)) {
                $prereqModule = Module::find($prereqId);
                throw new \InvalidArgumentException(
                    "Cannot add prerequisite: Adding '{$prereqModule->module_title}' would create a circular dependency."
                );
            }
        }

        // Delete existing prerequisites
        ModulePrerequisite::where('module_id', $module->id)->delete();

        // Add new prerequisites
        foreach ($prerequisiteIds as $prereqId) {
            ModulePrerequisite::create([
                'module_id' => $module->id,
                'prerequisite_module_id' => $prereqId,
            ]);
        }

        // Clear cache
        $this->clearPrerequisiteCache($module->id);
    }

    /**
     * Check if adding prerequisites would create a circular dependency.
     */
    private function wouldCreateCircularDependency(int $moduleId, int $prereqId, array $newPrereqIds): bool
    {
        $visited = [];
        return $this->detectCircularWithPending($moduleId, $prereqId, $visited, $newPrereqIds);
    }

    /**
     * Detect circular dependency considering pending prerequisites.
     */
    private function detectCircularWithPending(int $moduleId, int $checkId, array $visited, array $pendingPrereqs): bool
    {
        if ($moduleId === $checkId) {
            return true;
        }

        if (in_array($checkId, $visited)) {
            return false;
        }

        $visited[] = $checkId;

        // Get existing prerequisites of the check module
        $prerequisites = ModulePrerequisite::where('module_id', $checkId)
            ->pluck('prerequisite_module_id')
            ->toArray();

        foreach ($prerequisites as $prereqId) {
            if ($this->detectCircularWithPending($moduleId, $prereqId, $visited, $pendingPrereqs)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Clear prerequisite-related cache for a module.
     */
    public function clearPrerequisiteCache(int $moduleId): void
    {
        Cache::forget("module_prerequisites_{$moduleId}");
    }

    /**
     * Clear all prerequisite caches for a user.
     */
    public function clearUserPrerequisiteCache(int $userId): void
    {
        // This would need to iterate over all modules, so in production
        // consider using cache tags or a more efficient approach
    }

    /**
     * Get prerequisite chain (all prerequisites recursively).
     */
    public function getPrerequisiteChain(Module $module, array $visited = []): Collection
    {
        if (in_array($module->id, $visited)) {
            return collect();
        }

        $visited[] = $module->id;
        $chain = collect();

        $prerequisites = $module->prerequisites()->with('prerequisiteModule')->get();

        foreach ($prerequisites as $prereq) {
            $prereqModule = $prereq->prerequisiteModule;
            if ($prereqModule) {
                $chain->push($prereqModule);
                $chain = $chain->merge($this->getPrerequisiteChain($prereqModule, $visited));
            }
        }

        return $chain->unique('id');
    }
}
