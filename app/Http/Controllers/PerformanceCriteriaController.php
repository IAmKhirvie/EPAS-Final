<?php

namespace App\Http\Controllers;

use App\Models\PerformanceCriteria;
use App\Models\TaskSheet;
use App\Models\JobSheet;
use App\Http\Requests\StorePerformanceCriteriaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PerformanceCriteriaController extends Controller
{
    public function create(Request $request)
    {
        $taskSheet = null;
        $jobSheet = null;
        $type = null;
        $relatedId = null;

        if ($request->has('taskSheet')) {
            $taskSheet = TaskSheet::findOrFail($request->taskSheet);
            $type = 'task_sheet';
            $relatedId = $taskSheet->id;
        } elseif ($request->has('jobSheet')) {
            $jobSheet = JobSheet::findOrFail($request->jobSheet);
            $type = 'job_sheet';
            $relatedId = $jobSheet->id;
        }

        // Check if performance criteria already exists
        $performanceCriteria = PerformanceCriteria::where('type', $type)
            ->where('related_id', $relatedId)
            ->first();

        if ($performanceCriteria) {
            return view('performance-criteria.edit', compact('performanceCriteria', 'taskSheet', 'jobSheet'));
        }

        return view('performance-criteria.create', compact('taskSheet', 'jobSheet', 'type', 'relatedId'));
    }

    public function store(StorePerformanceCriteriaRequest $request)
    {
        $validated = $request->validated();

        try {
            $performanceCriteria = PerformanceCriteria::create([
                'type' => $request->type,
                'related_id' => $request->related_id,
                'user_id' => auth()->id(),
                'criteria' => json_encode($request->criteria),
                'completed_at' => now(),
            ]);

            // Calculate score based on observed criteria
            $totalCriteria = count($request->criteria);
            $observedCriteria = count(array_filter($request->criteria, function($criterion) {
                return $criterion['observed'] == true;
            }));
            $score = $totalCriteria > 0 ? ($observedCriteria / $totalCriteria) * 100 : 0;

            $performanceCriteria->update(['score' => $score]);

            return redirect()->route('content.management')
                ->with('success', 'Performance criteria submitted successfully!');
        } catch (\Exception $e) {
            Log::error('Performance criteria creation failed', ['error' => $e->getMessage(), 'user_id' => auth()->id()]);

            return redirect()->back()->with('error', 'Failed to submit performance criteria. Please try again.');
        }
    }

    public function edit(PerformanceCriteria $performanceCriteria)
    {
        $taskSheet = null;
        $jobSheet = null;

        if ($performanceCriteria->type === 'task_sheet') {
            $taskSheet = TaskSheet::find($performanceCriteria->related_id);
        } elseif ($performanceCriteria->type === 'job_sheet') {
            $jobSheet = JobSheet::find($performanceCriteria->related_id);
        }

        return view('performance-criteria.edit', compact('performanceCriteria', 'taskSheet', 'jobSheet'));
    }

    public function update(Request $request, PerformanceCriteria $performanceCriteria)
    {
        $request->validate([
            'criteria' => 'required|array|min:1',
            'criteria.*.description' => 'required|string',
            'criteria.*.observed' => 'required|boolean',
            'criteria.*.remarks' => 'nullable|string',
        ]);

        try {
            $performanceCriteria->update([
                'criteria' => json_encode($request->criteria),
                'completed_at' => now(),
            ]);

            // Recalculate score
            $totalCriteria = count($request->criteria);
            $observedCriteria = count(array_filter($request->criteria, function($criterion) {
                return $criterion['observed'] == true;
            }));
            $score = $totalCriteria > 0 ? ($observedCriteria / $totalCriteria) * 100 : 0;

            $performanceCriteria->update(['score' => $score]);

            return redirect()->route('content.management')
                ->with('success', 'Performance criteria updated successfully!');
        } catch (\Exception $e) {
            Log::error('Performance criteria update failed', ['error' => $e->getMessage(), 'performance_criteria_id' => $performanceCriteria->id]);

            return redirect()->back()->with('error', 'Failed to update performance criteria. Please try again.');
        }
    }

    public function destroy(PerformanceCriteria $performanceCriteria)
    {
        try {
            $performanceCriteria->delete();
            return response()->json(['success' => 'Performance criteria deleted successfully!']);
        } catch (\Exception $e) {
            Log::error('Performance criteria deletion failed', ['error' => $e->getMessage(), 'performance_criteria_id' => $performanceCriteria->id]);

            return response()->json(['error' => 'Failed to delete performance criteria. Please try again.'], 500);
        }
    }
}