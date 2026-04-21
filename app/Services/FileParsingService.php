<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser as PdfParser;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use PhpOffice\PhpPresentation\IOFactory as PresentationIOFactory;

class FileParsingService
{
    /**
     * Extract text content from uploaded file.
     */
    public function extractText(string $filePath, string $mimeType): ?string
    {
        try {
            if (str_contains($mimeType, 'pdf')) {
                return $this->extractFromPdf($filePath);
            }

            if (str_contains($mimeType, 'spreadsheet') ||
                str_contains($mimeType, 'excel') ||
                str_contains($mimeType, 'ms-excel')) {
                return $this->extractFromExcel($filePath);
            }

            if (str_contains($mimeType, 'wordprocessingml') ||
                str_contains($mimeType, 'msword')) {
                return $this->extractFromWord($filePath);
            }

            if (str_contains($mimeType, 'presentationml') ||
                str_contains($mimeType, 'ms-powerpoint') ||
                str_contains($mimeType, 'powerpoint')) {
                return $this->extractFromPowerPoint($filePath);
            }

            return null;
        } catch (\Exception $e) {
            Log::error("File parsing failed: " . $e->getMessage(), [
                'file_path' => $filePath,
                'mime_type' => $mimeType,
            ]);
            return null;
        }
    }

    protected function extractFromPdf(string $filePath): ?string
    {
        $parser = new PdfParser();
        $pdf = $parser->parseFile($filePath);
        $text = trim($pdf->getText());
        return $text ?: null;
    }

    protected function extractFromExcel(string $filePath): ?string
    {
        $data = Excel::toArray([], $filePath);
        $text = '';
        foreach ($data as $sheet) {
            foreach ($sheet as $row) {
                $line = implode(' | ', array_filter($row, fn($v) => $v !== null && $v !== ''));
                if ($line) {
                    $text .= $line . "\n";
                }
            }
        }
        return trim($text) ?: null;
    }

    protected function extractFromWord(string $filePath): ?string
    {
        $phpWord = WordIOFactory::load($filePath);
        $text = '';

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                $text .= $this->extractWordElementText($element) . "\n";
            }
        }

        return trim($text) ?: null;
    }

    private function extractWordElementText($element): string
    {
        $text = '';

        if (method_exists($element, 'getText')) {
            $text .= $element->getText();
        } elseif (method_exists($element, 'getElements')) {
            foreach ($element->getElements() as $child) {
                $text .= $this->extractWordElementText($child) . ' ';
            }
        }

        return trim($text);
    }

    protected function extractFromPowerPoint(string $filePath): ?string
    {
        $presentation = PresentationIOFactory::load($filePath);
        $text = '';

        foreach ($presentation->getAllSlides() as $slide) {
            foreach ($slide->getShapeCollection() as $shape) {
                if ($shape instanceof \PhpOffice\PhpPresentation\Shape\RichText) {
                    foreach ($shape->getParagraphs() as $paragraph) {
                        $line = '';
                        foreach ($paragraph->getRichTextElements() as $element) {
                            if (method_exists($element, 'getText')) {
                                $line .= $element->getText();
                            }
                        }
                        if (trim($line)) {
                            $text .= trim($line) . "\n";
                        }
                    }
                } elseif ($shape instanceof \PhpOffice\PhpPresentation\Shape\Table) {
                    foreach ($shape->getRows() as $row) {
                        $cells = [];
                        foreach ($row->getCells() as $cell) {
                            foreach ($cell->getParagraphs() as $paragraph) {
                                $cellText = '';
                                foreach ($paragraph->getRichTextElements() as $element) {
                                    if (method_exists($element, 'getText')) {
                                        $cellText .= $element->getText();
                                    }
                                }
                                if (trim($cellText)) {
                                    $cells[] = trim($cellText);
                                }
                            }
                        }
                        if ($cells) {
                            $text .= implode(' | ', $cells) . "\n";
                        }
                    }
                }
            }
            $text .= "\n";
        }

        return trim($text) ?: null;
    }
}
