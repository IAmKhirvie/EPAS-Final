<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use App\Models\InformationSheet;
use App\Http\Requests\StoreTopicRequest;
use App\Http\Requests\UpdateTopicRequest;
use App\Services\ContentSanitizationService;
use App\Services\DocumentConversionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TopicController extends Controller
{
    public function __construct(
        private ContentSanitizationService $sanitizer
    ) {}

    public function create($informationSheetId)
    {
        $informationSheet = InformationSheet::with(['module.course'])->findOrFail($informationSheetId);
        $nextOrder = $informationSheet->topics()->max('order') + 1;
        
        return view('modules.information-sheets.topics.create', compact('informationSheet', 'nextOrder'));
    }

    public function store(StoreTopicRequest $request, $informationSheetId)
    {
        $informationSheet = InformationSheet::findOrFail($informationSheetId);

        $validated = $request->validated();

        try {
            // Check if using block-based content
            if (!empty($validated['blocks'])) {
                $blocks = json_decode($validated['blocks'], true);

                if (is_array($blocks) && count($blocks) > 0) {
                    // Sanitize block HTML content
                    $blocks = $this->sanitizer->sanitizeBlocks($blocks);
                    // Process block images
                    $blocks = $this->sanitizer->processBlockImages($request, $blocks);
                    // Process block documents
                    $blocks = $this->sanitizer->processBlockDocuments($request, $blocks);

                    $validated['blocks'] = $blocks;
                    $validated['content'] = null;
                    $validated['parts'] = null;
                    $validated['file_path'] = null;
                    $validated['original_filename'] = null;
                    $validated['document_content'] = null;
                } else {
                    $validated['blocks'] = null;
                }
            } else {
                $validated['blocks'] = null;

                // Legacy flow: process content + parts + document
                if (!empty($validated['content'])) {
                    $validated['content'] = $this->sanitizer->sanitizeWithHtmlPurifier($validated['content']);
                }

                $parts = $this->sanitizer->processPartsWithImages($request, $validated['parts'] ?? []);
                $validated['parts'] = $parts;

                if ($request->hasFile('file')) {
                    $file = $request->file('file');
                    $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                    $validated['file_path'] = $file->storeAs('topics', $filename, 'public');
                    $validated['original_filename'] = $file->getClientOriginalName();

                    $ext = strtolower($file->getClientOriginalExtension());
                    if (in_array($ext, ['docx', 'doc', 'pptx', 'ppt', 'xlsx', 'xls', 'pdf'])) {
                        $validated['document_content'] = app(DocumentConversionService::class)
                            ->convertToHtml(Storage::disk('public')->path($validated['file_path']), $ext);
                    }
                }
            }

            // Remove the raw blocks JSON string before creating (it's now an array)
            unset($validated['block_images'], $validated['block_documents']);

            $topic = $informationSheet->topics()->create($validated);

            // Load relationships for the announcement
            $informationSheet->load('module.course');
            $module = $informationSheet->module;
            $course = $module->course;

            $content = "New topic '{$topic->title}' (Topic {$topic->topic_number}) has been added to Information Sheet {$informationSheet->sheet_number} in Module {$module->module_number} of {$course->course_name}.";

            \App\Http\Controllers\AnnouncementController::createAutomaticAnnouncement(
                'topic',
                $content,
                auth()->user(),
                'all'
            );

            return redirect()->route('content.management')
                ->with('success', "Topic '{$topic->title}' created successfully!");

        } catch (\Exception $e) {
            Log::error('Topic creation failed: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return back()->withInput()
                ->with('error', 'Failed to create topic. Please try again.');
        }
    }

    public function edit($informationSheetId, $topicId)
    {
        $informationSheet = InformationSheet::with(['module.course'])->findOrFail($informationSheetId);
        $topic = Topic::findOrFail($topicId);
        
        return view('modules.information-sheets.topics.edit', compact('informationSheet', 'topic'));
    }

    public function update(UpdateTopicRequest $request, $informationSheetId, $topicId)
    {
        $informationSheet = InformationSheet::findOrFail($informationSheetId);
        $topic = Topic::findOrFail($topicId);

        $validated = $request->validated();

        try {
            // Check if using block-based content
            if (!empty($validated['blocks'])) {
                $blocks = json_decode($validated['blocks'], true);

                if (is_array($blocks) && count($blocks) > 0) {
                    // Sanitize block HTML content
                    $blocks = $this->sanitizer->sanitizeBlocks($blocks);
                    // Process block images (pass existing blocks for image retention)
                    $blocks = $this->sanitizer->processBlockImages($request, $blocks, $topic->blocks);
                    // Process block documents
                    $blocks = $this->sanitizer->processBlockDocuments($request, $blocks, $topic->blocks);

                    $validated['blocks'] = $blocks;
                    // Clear legacy fields when switching to blocks
                    $validated['content'] = null;
                    $validated['parts'] = null;

                    // Clean up legacy document if switching to blocks
                    if ($topic->file_path && !$topic->usesBlocks()) {
                        Storage::disk('public')->delete($topic->file_path);
                        $validated['file_path'] = null;
                        $validated['original_filename'] = null;
                        $validated['document_content'] = null;
                    }
                } else {
                    $validated['blocks'] = null;
                }
            } else {
                $validated['blocks'] = null;

                // Legacy flow: process content + parts + document
                if (!empty($validated['content'])) {
                    $validated['content'] = $this->sanitizer->sanitizeWithHtmlPurifier($validated['content']);
                }

                $parts = $this->sanitizer->processPartsWithImages($request, $validated['parts'] ?? [], $topic->parts ?? []);
                $validated['parts'] = $parts;

                if ($request->hasFile('file')) {
                    if ($topic->file_path) {
                        Storage::disk('public')->delete($topic->file_path);
                    }
                    $file = $request->file('file');
                    $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                    $validated['file_path'] = $file->storeAs('topics', $filename, 'public');
                    $validated['original_filename'] = $file->getClientOriginalName();

                    $ext = strtolower($file->getClientOriginalExtension());
                    if (in_array($ext, ['docx', 'doc', 'pptx', 'ppt', 'xlsx', 'xls', 'pdf'])) {
                        $validated['document_content'] = app(DocumentConversionService::class)
                            ->convertToHtml(Storage::disk('public')->path($validated['file_path']), $ext);
                    } else {
                        $validated['document_content'] = null;
                    }
                }
            }

            // Remove upload-related keys before updating
            unset($validated['block_images'], $validated['block_documents']);

            $topic->update($validated);

            return redirect()->route('content.management')
                ->with('success', "Topic '{$topic->title}' updated successfully!");

        } catch (\Exception $e) {
            Log::error('Topic update failed: ' . $e->getMessage());

            return back()->withInput()
                ->with('error', 'Failed to update topic. Please try again.');
        }
    }

    public function destroy($topicId)
    {
        try {
            $topic = Topic::findOrFail($topicId);
            $topicTitle = $topic->title;

            // Clean up files
            if ($topic->file_path) {
                Storage::disk('public')->delete($topic->file_path);
            }
            if ($topic->parts) {
                foreach ($topic->parts as $part) {
                    if (!empty($part['image'])) {
                        $filename = basename($part['image']);
                        Storage::disk('public')->delete('topic-images/' . $filename);
                    }
                }
            }
            // Clean up block images and documents
            if ($topic->blocks) {
                foreach ($topic->blocks as $block) {
                    $data = $block['data'] ?? [];
                    if (!empty($data['image'])) {
                        $filename = basename($data['image']);
                        Storage::disk('public')->delete('topic-images/' . $filename);
                    }
                    if (!empty($data['file_path'])) {
                        Storage::disk('public')->delete($data['file_path']);
                    }
                }
            }

            $topic->delete();

            if (request()->expectsJson()) {
                return response()->json([
                    'message' => "Topic '{$topicTitle}' deleted successfully!"
                ]);
            }

            return redirect()->route('content.management')
                ->with('success', "Topic '{$topicTitle}' deleted successfully!");

        } catch (\Exception $e) {
            Log::error('Topic deletion failed: ' . $e->getMessage());
            
            if (request()->expectsJson()) {
                return response()->json([
                    'message' => 'Failed to delete topic. Please try again.'
                ], 500);
            }
            
            return back()->with('error', 'Failed to delete topic. Please try again.');
        }
    }

    public function showContent($topicId)
    {
        $topic = Topic::with('informationSheet.module')->findOrFail($topicId);
        return view('modules.information-sheets.topics.content', compact('topic'));
    }

    public function getContent(Topic $topic)
    {
        try {
            // Return the topic content as HTML for AJAX requests
            $html = view('modules.information-sheets.topics.content-partial', compact('topic'))->render();

            return response()->json([
                'html' => $html
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading topic content: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to load topic content'
            ], 500);
        }
    }

    /**
     * Get topic content for AJAX loading in module show page
     */
    public function getTopicContent(InformationSheet $informationSheet, Topic $topic)
    {
        try {
            // Verify the topic belongs to this information sheet
            if ($topic->information_sheet_id !== $informationSheet->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Topic not found in this information sheet'
                ], 404);
            }

            $html = view('modules.information-sheets.topics.content-partial', compact('topic'))->render();

            return response()->json([
                'success' => true,
                'html' => $html,
                'topic' => [
                    'id' => $topic->id,
                    'title' => $topic->title,
                    'topic_number' => $topic->topic_number,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading topic content: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load topic content',
                'html' => '<div class="alert alert-danger">Failed to load content. Please refresh the page.</div>'
            ], 500);
        }
    }

    public function download(Topic $topic)
    {
        if (!$topic->file_path) {
            return redirect()->back()->with('error', 'No file attached to this topic.');
        }

        $filePath = Storage::disk('public')->path($topic->file_path);
        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'File not found.');
        }

        return response()->download($filePath, $topic->original_filename);
    }

    /**
     * Delete a specific part image
     */
    public function deletePartImage(Request $request, $topicId, $partIndex)
    {
        try {
            $topic = Topic::findOrFail($topicId);
            $parts = $topic->parts ?? [];

            if (isset($parts[$partIndex]) && !empty($parts[$partIndex]['image'])) {
                // Delete file from storage
                $filename = basename($parts[$partIndex]['image']);
                Storage::disk('public')->delete('topic-images/' . $filename);

                // Remove image from part
                $parts[$partIndex]['image'] = null;
                $topic->update(['parts' => $parts]);
            }

            return response()->json(['success' => true, 'message' => 'Image deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Part image deletion failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete image'], 500);
        }
    }
}