<?php
// app/Http/Controllers/ModuleContentController.php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Topic;
use App\Models\SelfCheck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ModuleContentController extends Controller
{
    public function show(Module $module, $contentType)
    {
        try {
            // Check if it's a topic
            if (is_numeric($contentType)) {
                $topic = Topic::with('informationSheet')->find($contentType);
                if ($topic && $topic->informationSheet->module_id === $module->id) {
                    return $this->renderTopicContent($topic);
                }
            }

            // Check if it's a self-check
            if (str_starts_with($contentType, 'self-check-')) {
                $selfCheckId = str_replace('self-check-', '', $contentType);
                $selfCheck = SelfCheck::with('informationSheet')->find($selfCheckId);
                if ($selfCheck && $selfCheck->informationSheet->module_id === $module->id) {
                    return $this->renderSelfCheckContent($selfCheck);
                }
            }

            return $this->handleContentType($module, $contentType);
        } catch (\Exception $e) {
            Log::error('ModuleContentController::show failed', [
                'error' => $e->getMessage(),
                'user' => auth()->id(),
                'module_id' => $module->id,
                'content_type' => $contentType,
            ]);
            return '<div class="alert alert-danger">Failed to load content. Please try again.</div>';
        }
    }

    private function renderTopicContent(Topic $topic)
    {
        $content = "
        <div class='content-section'>
            <div class='section-header'>
                <h2>{$topic->title}</h2>
                <p>Topic {$topic->topic_number}</p>
            </div>
            <div class='content-display'>
                " . ($topic->content ?: '<p>No content available for this topic.</p>') . "
            </div>
            <div class='content-meta mt-4'>
                <small class='text-muted'>
                    Information Sheet: {$topic->informationSheet->sheet_number} - {$topic->informationSheet->title}
                </small>
            </div>
        </div>
        ";

        return $content;
    }

    private function renderSelfCheckContent(SelfCheck $selfCheck)
    {
        $questions = json_decode($selfCheck->content, true) ?? [];
        $questionsHtml = '';

        foreach ($questions as $index => $question) {
            $questionsHtml .= $this->renderQuestion($question, $index + 1, $selfCheck->question_type);
        }

        $content = "
        <div class='content-section'>
            <div class='section-header'>
                <h2>{$selfCheck->title}</h2>
                <p>Self Check {$selfCheck->check_number}</p>
            </div>
            <div class='content-display'>
                <form id='self-check-form' data-self-check-id='{$selfCheck->id}'>
                    <div class='questions-container'>
                        {$questionsHtml}
                    </div>
                    <div class='self-check-actions mt-4'>
                        <button type='submit' class='btn btn-primary'>Submit Answers</button>
                        <button type='button' class='btn btn-outline-secondary' id='reset-self-check'>Reset</button>
                    </div>
                </form>
            </div>
        </div>
        ";

        return $content;
    }

    private function renderQuestion($question, $number, $type)
    {
        $optionsHtml = '';

        switch ($type) {
            case 'multiple_choice':
                $options = $question['options'] ?? [];
                foreach ($options as $key => $option) {
                    $optionsHtml .= "
                    <div class='form-check'>
                        <input class='form-check-input' type='radio' name='question_{$number}' value='{$key}' id='q{$number}_{$key}'>
                        <label class='form-check-label' for='q{$number}_{$key}'>
                            {$option}
                        </label>
                    </div>
                    ";
                }
                break;

            case 'true_false':
                $optionsHtml = "
                <div class='form-check'>
                    <input class='form-check-input' type='radio' name='question_{$number}' value='true' id='q{$number}_true'>
                    <label class='form-check-label' for='q{$number}_true'>True</label>
                </div>
                <div class='form-check'>
                    <input class='form-check-input' type='radio' name='question_{$number}' value='false' id='q{$number}_false'>
                    <label class='form-check-label' for='q{$number}_false'>False</label>
                </div>
                ";
                break;

            case 'identification':
                $optionsHtml = "
                <div class='form-group'>
                    <input type='text' class='form-control' name='question_{$number}' placeholder='Enter your answer...'>
                </div>
                ";
                break;
        }

        return "
        <div class='question-card mb-4'>
            <h5>Question {$number}: {$question['question']}</h5>
            <div class='question-options mt-2'>
                {$optionsHtml}
            </div>
            <div class='question-points mt-2'>
                <small class='text-muted'>Points: {$question['points']}</small>
            </div>
        </div>
        ";
    }

    private function handleContentType(Module $module, $contentType)
    {
        // For now, return a simple message for dynamic content types
        // In a real implementation, you might have predefined content types
        $content = "
        <div class='content-section'>
            <div class='section-header'>
                <h2>Dynamic Content</h2>
                <p>Content Type: {$contentType}</p>
            </div>
            <div class='content-display'>
                <p>This content is dynamically loaded based on the information sheet and topics you create.</p>
                <p>Module: {$module->module_name}</p>
                <p>Content type: {$contentType}</p>
            </div>
        </div>
        ";

        return $content;
    }
}