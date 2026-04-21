<?php

namespace App\Http\Controllers;

use App\Constants\Roles;
use App\Models\DocumentAssessment;
use App\Models\DocumentAssessmentSubmission;
use App\Models\InformationSheet;
use App\Models\User;
use App\Http\Requests\StoreDocumentAssessmentRequest;
use App\Http\Requests\UpdateDocumentAssessmentRequest;
use App\Services\DocumentConversionService;
use App\Services\NotificationService;
use App\Services\ProgressTrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentAssessmentController extends Controller
{
    public function __construct(
        private DocumentConversionService $conversionService,
        private NotificationService $notificationService,
        private ProgressTrackingService $progressService,
    ) {}

    public function create(InformationSheet $informationSheet)
    {
        return view('document-assessments.create', compact('informationSheet'));
    }

    public function store(StoreDocumentAssessmentRequest $request, InformationSheet $informationSheet)
    {
        try {
            return DB::transaction(function () use ($request, $informationSheet) {
                $file = $request->file('document');
                $extension = strtolower($file->getClientOriginalExtension());
                $filename = Str::uuid() . '.' . $extension;
                $filePath = $file->storeAs('document-assessments', $filename, 'public');

                // Convert document to HTML if editable type
                $documentContent = null;
                if (in_array($extension, ['docx', 'doc', 'pptx', 'ppt', 'xlsx', 'xls', 'pdf'])) {
                    $fullPath = Storage::disk('public')->path($filePath);
                    $documentContent = $this->conversionService->convertToHtml($fullPath, $extension);
                }

                // If instructor edited content in the WYSIWYG, use that instead
                if ($request->filled('document_content')) {
                    $documentContent = $this->conversionService->sanitizeHtml($request->document_content);
                }

                DocumentAssessment::create([
                    'information_sheet_id' => $informationSheet->id,
                    'created_by' => auth()->id(),
                    'assessment_number' => $request->assessment_number,
                    'title' => $request->title,
                    'description' => $request->description,
                    'instructions' => $request->instructions,
                    'document_content' => $documentContent,
                    'file_path' => $filePath,
                    'original_filename' => $file->getClientOriginalName(),
                    'file_type' => $extension,
                    'max_points' => $request->max_points,
                    'time_limit' => $request->time_limit,
                    'due_date' => $request->due_date,
                ]);

                return redirect()->route('content.management')
                    ->with('success', 'Document Assessment created successfully!');
            });
        } catch (\Exception $e) {
            Log::error('Document assessment creation failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            return back()->withInput()->with('error', 'Failed to create document assessment. Please try again.');
        }
    }

    public function show(DocumentAssessment $documentAssessment)
    {
        $documentAssessment->load(['informationSheet.module.course', 'creator', 'submissions.user']);

        return view('document-assessments.show', [
            'assessment' => $documentAssessment,
        ]);
    }

    public function edit(InformationSheet $informationSheet, DocumentAssessment $documentAssessment)
    {
        return view('document-assessments.edit', [
            'informationSheet' => $informationSheet,
            'assessment' => $documentAssessment,
        ]);
    }

    public function update(UpdateDocumentAssessmentRequest $request, InformationSheet $informationSheet, DocumentAssessment $documentAssessment)
    {
        try {
            return DB::transaction(function () use ($request, $documentAssessment) {
                $updateData = [
                    'assessment_number' => $request->assessment_number,
                    'title' => $request->title,
                    'description' => $request->description,
                    'instructions' => $request->instructions,
                    'max_points' => $request->max_points,
                    'time_limit' => $request->time_limit,
                    'due_date' => $request->due_date,
                ];

                if ($request->hasFile('document')) {
                    // Delete old file
                    if ($documentAssessment->file_path) {
                        Storage::disk('public')->delete($documentAssessment->file_path);
                    }

                    $file = $request->file('document');
                    $extension = strtolower($file->getClientOriginalExtension());
                    $filename = Str::uuid() . '.' . $extension;
                    $filePath = $file->storeAs('document-assessments', $filename, 'public');

                    $updateData['file_path'] = $filePath;
                    $updateData['original_filename'] = $file->getClientOriginalName();
                    $updateData['file_type'] = $extension;

                    // Convert new document to HTML
                    $documentContent = null;
                    if (in_array($extension, ['docx', 'doc', 'pptx', 'ppt', 'xlsx', 'xls', 'pdf'])) {
                        $fullPath = Storage::disk('public')->path($filePath);
                        $documentContent = $this->conversionService->convertToHtml($fullPath, $extension);
                    }
                    $updateData['document_content'] = $documentContent;
                }

                // If instructor edited content in WYSIWYG, use that
                if ($request->filled('document_content')) {
                    $updateData['document_content'] = $this->conversionService->sanitizeHtml($request->document_content);
                }

                $documentAssessment->update($updateData);

                return redirect()->route('content.management')
                    ->with('success', 'Document Assessment updated successfully!');
            });
        } catch (\Exception $e) {
            Log::error('Document assessment update failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            return back()->withInput()->with('error', 'Failed to update document assessment. Please try again.');
        }
    }

    public function destroy(InformationSheet $informationSheet, DocumentAssessment $documentAssessment)
    {
        try {
            if ($documentAssessment->file_path) {
                Storage::disk('public')->delete($documentAssessment->file_path);
            }
            $documentAssessment->submissions()->delete();
            $documentAssessment->delete();

            return response()->json(['success' => 'Document Assessment deleted successfully!']);
        } catch (\Exception $e) {
            Log::error('Document assessment deletion failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            return response()->json(['error' => 'Failed to delete document assessment.'], 500);
        }
    }

    public function download(DocumentAssessment $documentAssessment)
    {
        if (!$documentAssessment->file_path || !Storage::disk('public')->exists($documentAssessment->file_path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('public')->download(
            $documentAssessment->file_path,
            $documentAssessment->original_filename
        );
    }

    public function submit(Request $request, DocumentAssessment $documentAssessment)
    {
        $request->validate([
            'answer_text' => 'required|string|min:10',
        ]);

        // Check if already submitted
        $existing = $documentAssessment->submissions()->where('user_id', auth()->id())->first();
        if ($existing) {
            return back()->with('error', 'You have already submitted an answer for this assessment.');
        }

        try {
            DB::transaction(function () use ($request, $documentAssessment) {
                $isLate = $documentAssessment->due_date && now()->gt($documentAssessment->due_date);

                $documentAssessment->submissions()->create([
                    'user_id' => auth()->id(),
                    'answer_text' => $request->answer_text,
                    'submitted_at' => now(),
                    'is_late' => $isLate,
                ]);

                // Notify ONLY the uploader
                $creator = User::find($documentAssessment->created_by);
                if ($creator) {
                    $this->notificationService->notifyDocumentAssessmentSubmitted(
                        $creator,
                        auth()->user(),
                        $documentAssessment
                    );
                }
            });

            // Track progress
            $this->progressService->recordDocumentAssessmentProgress($documentAssessment, auth()->id());

            return redirect()->route('document-assessments.show', $documentAssessment)
                ->with('success', 'Your answer has been submitted successfully!');
        } catch (\Exception $e) {
            Log::error('Document assessment submission failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            return back()->withInput()->with('error', 'Failed to submit answer. Please try again.');
        }
    }

    public function grade(Request $request, DocumentAssessmentSubmission $submission)
    {
        $assessment = $submission->documentAssessment;

        // Only the uploader or admin can grade
        if (auth()->id() !== $assessment->created_by && auth()->user()->role !== Roles::ADMIN) {
            abort(403, 'Only the assessment creator can grade submissions.');
        }

        $request->validate([
            'score' => 'required|integer|min:0|max:' . $assessment->max_points,
            'feedback' => 'nullable|string',
        ]);

        $submission->update([
            'score' => $request->score,
            'feedback' => $request->feedback,
            'evaluated_by' => auth()->id(),
            'evaluated_at' => now(),
        ]);

        // Notify student
        $this->notificationService->notifyGradePosted(
            $submission->user,
            'Document Assessment',
            $assessment->title,
            $request->score,
            $assessment->max_points
        );

        return back()->with('success', 'Submission graded successfully!');
    }

    /**
     * AJAX endpoint: convert uploaded document to HTML for preview in Quill editor.
     */
    public function convert(Request $request)
    {
        $request->validate([
            'document' => 'required|file|mimes:doc,docx,ppt,pptx,xls,xlsx,pdf|max:' . config('joms.uploads.max_document_size', 10240),
        ]);

        try {
            $file = $request->file('document');
            $extension = strtolower($file->getClientOriginalExtension());
            $tempPath = $file->getRealPath();

            $html = $this->conversionService->convertToHtml($tempPath, $extension);

            return response()->json([
                'success' => true,
                'html' => $html ?? '',
            ]);
        } catch (\Exception $e) {
            Log::error('Document conversion failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            return response()->json(['error' => 'Failed to convert document.'], 500);
        }
    }
}
