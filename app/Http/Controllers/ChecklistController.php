<?php

namespace App\Http\Controllers;

use App\Models\Checklist;
use App\Models\InformationSheet;
use App\Models\TaskSheet;
use App\Models\JobSheet;
use App\Http\Requests\StoreChecklistRequest;
use App\Services\ProgressTrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChecklistController extends Controller
{
    public function __construct(private ProgressTrackingService $progressService)
    {
    }

    public function create(InformationSheet $informationSheet)
    {
        return view('checklists.create', compact('informationSheet'));
    }

    public function store(StoreChecklistRequest $request, InformationSheet $informationSheet)
    {
        $validated = $request->validated();

        try {
            $checklist = Checklist::create([
                'information_sheet_id' => $informationSheet->id,
                'checklist_number' => $request->checklist_number,
                'title' => $request->title,
                'description' => $request->description,
                'items' => $request->items,
                'total_score' => array_sum(array_column($request->items, 'rating')),
                'max_score' => count($request->items) * 5,
                'completed_by' => auth()->id(),
                'completed_at' => now(),
            ]);

            return redirect()->route('content.management')
                ->with('success', 'Checklist created successfully!');
        } catch (\Exception $e) {
            Log::error('Checklist store failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            return back()->withInput()->with('error', 'Failed to create checklist. Please try again.');
        }
    }

    public function edit(InformationSheet $informationSheet, Checklist $checklist)
    {
        return view('checklists.edit', compact('informationSheet', 'checklist'));
    }

    public function update(Request $request, InformationSheet $informationSheet, Checklist $checklist)
    {
        $request->validate([
            'checklist_number' => 'required|string',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.rating' => 'required|integer|min:1|max:5',
            'items.*.remarks' => 'nullable|string',
        ]);

        try {
            $checklist->update([
                'checklist_number' => $request->checklist_number,
                'title' => $request->title,
                'description' => $request->description,
                'items' => $request->items,
                'total_score' => array_sum(array_column($request->items, 'rating')),
                'max_score' => count($request->items) * 5,
                'completed_by' => auth()->id(),
                'completed_at' => now(),
            ]);

            return redirect()->route('content.management')
                ->with('success', 'Checklist updated successfully!');
        } catch (\Exception $e) {
            Log::error('Checklist update failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            return back()->withInput()->with('error', 'Failed to update checklist. Please try again.');
        }
    }

    public function destroy(InformationSheet $informationSheet, Checklist $checklist)
    {
        try {
            $checklist->delete();
            return response()->json(['success' => 'Checklist deleted successfully!']);
        } catch (\Exception $e) {
            Log::error('Checklist destroy failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            return response()->json(['error' => 'Failed to delete checklist. Please try again.'], 500);
        }
    }

    public function show(Checklist $checklist)
    {
        $checklist->load(['informationSheet.module.course']);
        return view('checklists.show', compact('checklist'));
    }

    public function evaluate(Request $request, Checklist $checklist)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.rating' => 'required|integer|min:1|max:5',
            'items.*.remarks' => 'nullable|string',
        ]);

        try {
            $items = $checklist->items ?? [];

            foreach ($items as $index => &$item) {
                if (isset($request->items[$index])) {
                    $item['rating'] = $request->items[$index]['rating'];
                    $item['remarks'] = $request->items[$index]['remarks'] ?? '';
                }
            }

            $checklist->update([
                'items' => $items,
                'total_score' => array_sum(array_column($items, 'rating')),
                'evaluated_by' => auth()->id(),
                'evaluated_at' => now(),
            ]);

            return redirect()->route('checklists.show', $checklist)
                ->with('success', 'Checklist evaluation submitted successfully!');
        } catch (\Exception $e) {
            Log::error('Checklist evaluate failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            return back()->withInput()->with('error', 'Failed to submit evaluation. Please try again.');
        }
    }
}