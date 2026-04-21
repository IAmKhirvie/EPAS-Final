<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\InformationSheet;
use App\Services\FileParsingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Http\Traits\SanitizesContent;
use App\Models\Course;


class InformationSheetController extends Controller
{
    use SanitizesContent;

    public function __construct(protected FileParsingService $fileParser) {}

    public function create(Module $module)
    {
        $nextOrder = $module->informationSheets()->max('order') + 1;
        return view('modules.information-sheets.create', compact('module', 'nextOrder'));
    }

    public function store(Request $request, Module $module)
    {
        $validated = $request->validate([
            'sheet_number' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'order' => 'required|integer|min:0',
            'file' => 'nullable|file|mimes:pdf,xlsx,xls,doc,docx,ppt,pptx|max:' . config('joms.uploads.max_document_size', 10240),
        ]);

        try {
            $validated = $this->sanitizeFields($validated, ['content']);

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $filename = \Illuminate\Support\Str::uuid() . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('information-sheets', $filename, 'public');

                $validated['file_path'] = $filePath;
                $validated['original_filename'] = $file->getClientOriginalName();

                $extractedText = $this->fileParser->extractText(
                    $file->getRealPath(),
                    $file->getMimeType()
                );

                if ($extractedText) {
                    if (!empty($validated['content'])) {
                        $validated['content'] .= "\n\n--- Extracted from uploaded file ---\n\n" . $extractedText;
                    } else {
                        $validated['content'] = $extractedText;
                    }
                }
            }

            $informationSheet = $module->informationSheets()->create($validated);

            return redirect()->route('content.management')
                ->with('success', "Information Sheet {$informationSheet->sheet_number} created successfully!");
        } catch (\Exception $e) {
            Log::error('Information sheet creation failed: ' . $e->getMessage());

            return back()->withInput()
                ->with('error', 'Failed to create information sheet. Please try again.');
        }
    }

    public function edit(Module $module, InformationSheet $informationSheet)
    {
        return view('modules.information-sheets.edit', compact('module', 'informationSheet'));
    }

    public function update(Request $request, Module $module, InformationSheet $informationSheet)
    {
        $validated = $request->validate([
            'sheet_number' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'order' => 'required|integer|min:0',
            'file' => 'nullable|file|mimes:pdf,xlsx,xls,doc,docx,ppt,pptx|max:' . config('joms.uploads.max_document_size', 10240),
        ]);

        try {
            $validated = $this->sanitizeFields($validated, ['content']);

            if ($request->hasFile('file')) {
                // Delete old file if exists
                if ($informationSheet->file_path) {
                    Storage::disk('public')->delete($informationSheet->file_path);
                }

                $file = $request->file('file');
                $filename = \Illuminate\Support\Str::uuid() . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('information-sheets', $filename, 'public');

                $validated['file_path'] = $filePath;
                $validated['original_filename'] = $file->getClientOriginalName();

                $extractedText = $this->fileParser->extractText(
                    $file->getRealPath(),
                    $file->getMimeType()
                );

                if ($extractedText) {
                    if (!empty($validated['content'])) {
                        $validated['content'] .= "\n\n--- Extracted from uploaded file ---\n\n" . $extractedText;
                    } else {
                        $validated['content'] = $extractedText;
                    }
                }
            }

            $informationSheet->update($validated);

            return redirect()->route('content.management')
                ->with('success', "Information Sheet {$informationSheet->sheet_number} updated successfully!");
        } catch (\Exception $e) {
            Log::error('Information sheet update failed: ' . $e->getMessage());

            return back()->withInput()
                ->with('error', 'Failed to update information sheet. Please try again.');
        }
    }

    public function download(Module $module, InformationSheet $informationSheet)
    {
        if (!$informationSheet->file_path) {
            return redirect()->back()->with('error', 'No file attached to this information sheet.');
        }

        $filePath = Storage::disk('public')->path($informationSheet->file_path);

        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'File not found.');
        }

        return response()->download($filePath, $informationSheet->original_filename);
    }

    public function destroy(Course $course, Module $module, InformationSheet $informationSheet)
    {
        try {
            $informationSheet->delete();

            if (request()->expectsJson()) {
                return response()->json(['message' => 'Information sheet deleted successfully!']);
            }

            return redirect()->route('content.management')->with('success', 'Information sheet deleted!');
        } catch (\Exception $e) {
            Log::error('Info sheet deletion failed: ' . $e->getMessage());

            if (request()->expectsJson()) {
                return response()->json(['message' => 'Failed to delete. Please try again.'], 500);
            }

            return back()->with('error', 'Failed to delete.');
        }
    }
}
