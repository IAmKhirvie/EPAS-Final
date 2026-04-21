<?php

namespace App\Http\Controllers;

use App\Models\InformationSheet;
use App\Models\Homework;
use App\Services\NotificationService;
use App\Services\ProgressTrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Http\Traits\SanitizesContent;

class HomeworkController extends Controller
{
    use SanitizesContent;

    public function __construct(private ProgressTrackingService $progressService)
    {
    }

    public function create(InformationSheet $informationSheet)
    {
        $this->authorize('create', Homework::class);
        return view('homeworks.create', compact('informationSheet'));
    }

    public function store(Request $request, InformationSheet $informationSheet)
    {
        $this->authorize('create', Homework::class);
        $request->validate([
            'homework_number' => 'required|string',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'required|string',
            'requirements' => 'required|array|min:1',
            'submission_guidelines' => 'required|array|min:1',
            'due_date' => 'required|date',
            'max_points' => 'required|integer|min:1',
            'reference_images' => 'nullable|array',
            'reference_images.*' => 'image|mimes:jpeg,png,jpg,gif|mimetypes:image/jpeg,image/png,image/gif|max:' . config('joms.uploads.max_image_size', 5120),
        ]);

        try {
            $referenceImagePaths = [];
            if ($request->hasFile('reference_images')) {
                foreach ($request->file('reference_images') as $image) {
                    $referenceImagePaths[] = $image->store('homework-references', 'public');
                }
            }

            $homework = Homework::create([
                'information_sheet_id' => $informationSheet->id,
                'homework_number' => $this->stripHtml($request->homework_number),
                'title' => $this->stripHtml($request->title),
                'description' => $this->sanitizeContent($request->description),
                'instructions' => $this->sanitizeContent($request->instructions),
                'requirements' => $request->requirements,
                'submission_guidelines' => $request->submission_guidelines,
                'reference_images' => $referenceImagePaths,
                'due_date' => $request->due_date,
                'max_points' => $request->max_points,
            ]);

            return redirect()->route('content.management')
                ->with('success', 'Homework created successfully!');
        } catch (\Exception $e) {
            Log::error('Homework creation failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            return back()->withInput()->with('error', 'Failed to create homework. Please try again.');
        }
    }

    public function edit(InformationSheet $informationSheet, Homework $homework)
    {
        $this->authorize('update', $homework);
        return view('homeworks.edit', compact('informationSheet', 'homework'));
    }

    public function update(Request $request, InformationSheet $informationSheet, Homework $homework)
    {
        $this->authorize('update', $homework);
        $request->validate([
            'homework_number' => 'required|string',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'required|string',
            'requirements' => 'required|array|min:1',
            'submission_guidelines' => 'required|array|min:1',
            'due_date' => 'required|date',
            'max_points' => 'required|integer|min:1',
            'reference_images' => 'nullable|array',
            'reference_images.*' => 'image|mimes:jpeg,png,jpg,gif|mimetypes:image/jpeg,image/png,image/gif|max:' . config('joms.uploads.max_image_size', 5120),
        ]);

        try {
            $referenceImagePaths = $homework->reference_images ?? [];

            if ($request->hasFile('reference_images')) {
                // Delete old reference images
                foreach ($referenceImagePaths as $oldImage) {
                    Storage::disk('public')->delete($oldImage);
                }

                $referenceImagePaths = [];
                foreach ($request->file('reference_images') as $image) {
                    $referenceImagePaths[] = $image->store('homework-references', 'public');
                }
            }

            $homework->update([
                'homework_number' => $this->stripHtml($request->homework_number),
                'title' => $this->stripHtml($request->title),
                'description' => $this->sanitizeContent($request->description),
                'instructions' => $this->sanitizeContent($request->instructions),
                'requirements' => $request->requirements,
                'submission_guidelines' => $request->submission_guidelines,
                'reference_images' => $referenceImagePaths,
                'due_date' => $request->due_date,
                'max_points' => $request->max_points,
            ]);

            return redirect()->route('content.management')
                ->with('success', 'Homework updated successfully!');
        } catch (\Exception $e) {
            Log::error('Homework update failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            return back()->withInput()->with('error', 'Failed to update homework. Please try again.');
        }
    }

    public function destroy(InformationSheet $informationSheet, Homework $homework)
    {
        $this->authorize('delete', $homework);

        try {
            // Delete reference images
            $referenceImages = $homework->reference_images ?? [];
            foreach ($referenceImages as $image) {
                Storage::disk('public')->delete($image);
            }

            $homework->delete();

            return response()->json(['success' => 'Homework deleted successfully!']);
        } catch (\Exception $e) {
            Log::error('Homework deletion failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            return response()->json(['error' => 'Failed to delete homework. Please try again.'], 500);
        }
    }

    public function show(Homework $homework)
    {
        $homework->load(['informationSheet.module.course']);
        return view('homeworks.show', compact('homework'));
    }

    public function submit(Request $request, Homework $homework)
    {
        $request->validate([
            'submission_file' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx,zip|mimetypes:image/jpeg,image/png,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/zip|max:' . config('joms.uploads.max_document_size', 10240),
            'description' => 'nullable|string',
            'work_hours' => 'nullable|numeric|min:0',
        ]);

        try {
            $filePath = $request->file('submission_file')->store('homework-submissions', 'public');

            $submission = $homework->submissions()->create([
                'user_id' => auth()->id(),
                'file_path' => $filePath,
                'description' => $request->description,
                'work_hours' => $request->work_hours,
                'submitted_at' => now(),
            ]);

            // Track progress
            $this->progressService->recordHomeworkProgress($homework, auth()->id());

            // Award gamification points
            app(\App\Services\GamificationService::class)->awardForActivity(auth()->user(), 'homework_submit', $submission);

            // Notify instructor of submission
            $homework->loadMissing('informationSheet.module.course.instructor');
            app(NotificationService::class)->notifySubmissionReceived(auth()->user(), 'homework', $homework);

            return redirect()->route('homeworks.show', $homework)
                ->with('success', 'Homework submitted successfully!');
        } catch (\Exception $e) {
            Log::error('Homework submission failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            return back()->withInput()->with('error', 'Failed to submit homework. Please try again.');
        }
    }
}