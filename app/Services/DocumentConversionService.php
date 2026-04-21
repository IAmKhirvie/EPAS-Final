<?php

namespace App\Services;

use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use PhpOffice\PhpPresentation\IOFactory as PresentationIOFactory;
use PhpOffice\PhpPresentation\Shape\RichText;
use PhpOffice\PhpPresentation\Shape\RichText\Run;
use PhpOffice\PhpSpreadsheet\IOFactory as SpreadsheetIOFactory;
use Smalot\PdfParser\Parser as PdfParser;
use Illuminate\Support\Facades\Log;

class DocumentConversionService
{
    /**
     * Convert a document file to HTML.
     *
     * @return string|null HTML content, or null if not convertible
     */
    public function convertToHtml(string $filePath, string $extension): ?string
    {
        $extension = strtolower($extension);

        try {
            return match ($extension) {
                'docx', 'doc' => $this->convertWordToHtml($filePath),
                'pptx', 'ppt' => $this->convertPresentationToHtml($filePath),
                'xlsx', 'xls' => $this->convertSpreadsheetToHtml($filePath),
                'pdf' => $this->convertPdfToHtml($filePath),
                default => null,
            };
        } catch (\Throwable $e) {
            Log::error('Document conversion failed', [
                'file' => $filePath,
                'extension' => $extension,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    protected function convertWordToHtml(string $filePath): ?string
    {
        $phpWord = WordIOFactory::load($filePath);
        $htmlWriter = WordIOFactory::createWriter($phpWord, 'HTML');

        ob_start();
        $htmlWriter->save('php://output');
        $html = ob_get_clean();

        // Extract just the body content
        if (preg_match('/<body[^>]*>(.*)<\/body>/is', $html, $matches)) {
            $html = $matches[1];
        }

        // Strip MS Word/Office XML bloat before sanitizing
        $html = app(ContentSanitizationService::class)->stripWordBloat($html);

        return $this->sanitizeHtml($html);
    }

    protected function convertPresentationToHtml(string $filePath): ?string
    {
        $presentation = PresentationIOFactory::load($filePath);
        $html = '';

        foreach ($presentation->getAllSlides() as $slideIndex => $slide) {
            $slideNum = $slideIndex + 1;
            $html .= '<div class="doc-viewer__logical-page">';
            $html .= "<h4 style=\"color: #6c757d; border-bottom: 1px solid #e9ecef; padding-bottom: 0.5rem;\">Slide {$slideNum}</h4>";

            foreach ($slide->getShapeCollection() as $shape) {
                if ($shape instanceof RichText) {
                    foreach ($shape->getParagraphs() as $paragraph) {
                        $paragraphHtml = '';
                        foreach ($paragraph->getRichTextElements() as $element) {
                            $text = method_exists($element, 'getText') ? e($element->getText()) : '';
                            if (empty(trim($text))) {
                                continue;
                            }
                            if ($element instanceof Run) {
                                $font = $element->getFont();
                                if ($font->isBold()) {
                                    $text = '<strong>' . $text . '</strong>';
                                }
                                if ($font->isItalic()) {
                                    $text = '<em>' . $text . '</em>';
                                }
                                if ($font->getUnderline() !== \PhpOffice\PhpPresentation\Style\Font::UNDERLINE_NONE) {
                                    $text = '<u>' . $text . '</u>';
                                }
                            }
                            $paragraphHtml .= $text;
                        }
                        if (!empty(trim(strip_tags($paragraphHtml)))) {
                            $html .= '<p>' . $paragraphHtml . '</p>';
                        }
                    }
                }
            }

            $html .= '</div>';
        }

        return $this->sanitizeHtml($html);
    }

    protected function convertSpreadsheetToHtml(string $filePath): ?string
    {
        $spreadsheet = SpreadsheetIOFactory::load($filePath);
        $html = '';

        foreach ($spreadsheet->getAllSheets() as $sheetIndex => $sheet) {
            $sheetTitle = e($sheet->getTitle());
            $html .= '<div class="doc-viewer__logical-page">';
            $html .= "<h4 style=\"color: #6c757d; border-bottom: 1px solid #e9ecef; padding-bottom: 0.5rem;\">Sheet: {$sheetTitle}</h4>";

            $rows = $sheet->toArray(null, true, true, true);
            if (empty($rows)) {
                $html .= '<p style="color: #6c757d;">Empty sheet</p>';
                $html .= '</div>';
                continue;
            }

            $html .= '<table style="width: 100%; border-collapse: collapse; margin: 0.5rem 0;">';

            $isFirstRow = true;
            foreach ($rows as $row) {
                // Skip completely empty rows
                $hasData = false;
                foreach ($row as $cell) {
                    if ($cell !== null && $cell !== '') {
                        $hasData = true;
                        break;
                    }
                }
                if (!$hasData) continue;

                $tag = $isFirstRow ? 'th' : 'td';
                $html .= '<tr>';
                foreach ($row as $cell) {
                    $cellValue = e($cell ?? '');
                    $style = 'border: 1px solid #dee2e6; padding: 0.4rem 0.6rem; font-size: 0.85rem;';
                    if ($isFirstRow) {
                        $style .= ' background: #f8f9fa; font-weight: 600;';
                    }
                    $html .= "<{$tag} style=\"{$style}\">{$cellValue}</{$tag}>";
                }
                $html .= '</tr>';
                $isFirstRow = false;
            }

            $html .= '</table>';
            $html .= '</div>';
        }

        return $this->sanitizeHtml($html);
    }

    protected function convertPdfToHtml(string $filePath): ?string
    {
        $parser = new PdfParser();
        $pdf = $parser->parseFile($filePath);
        $pages = $pdf->getPages();
        $html = '';

        if (empty($pages)) {
            return null;
        }

        foreach ($pages as $pageIndex => $page) {
            $pageNum = $pageIndex + 1;
            $text = $page->getText();

            if (empty(trim($text))) {
                $html .= '<div class="doc-viewer__logical-page">';
                $html .= "<h4 style=\"color: #6c757d; border-bottom: 1px solid #e9ecef; padding-bottom: 0.5rem;\">Page {$pageNum}</h4>";
                $html .= '<p style="color: #adb5bd; font-style: italic;">This page contains non-text content (images, diagrams, etc.) that cannot be rendered inline.</p>';
                $html .= '</div>';
                continue;
            }

            $html .= '<div class="doc-viewer__logical-page">';
            $html .= "<h4 style=\"color: #6c757d; border-bottom: 1px solid #e9ecef; padding-bottom: 0.5rem;\">Page {$pageNum}</h4>";

            // Convert text to HTML paragraphs
            $lines = preg_split('/\n{2,}/', trim($text));
            foreach ($lines as $paragraph) {
                $paragraph = trim($paragraph);
                if (empty($paragraph)) continue;
                // Preserve single newlines as <br>
                $paragraph = e($paragraph);
                $paragraph = nl2br($paragraph);
                $html .= '<p>' . $paragraph . '</p>';
            }

            $html .= '</div>';
        }

        return $this->sanitizeHtml($html);
    }

    public function sanitizeHtml(string $html): string
    {
        $config = \HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed',
            'p,br,strong,b,em,i,u,h1,h2,h3,h4,h5,h6,ul,ol,li,table,thead,tbody,tr,th,td,div,span,hr,blockquote,pre,code,a[href],img[src|alt|width|height|style]'
        );
        $config->set('HTML.AllowedAttributes', 'class,style,src,alt,width,height,href');
        // Only use CSS properties that HTMLPurifier actually supports
        $config->set('CSS.AllowedProperties', 'color,background-color,font-size,font-weight,text-align,margin,padding,border,border-left,border-right,border-top,border-bottom,border-collapse,text-decoration,font-style,font-family,width,height,vertical-align,text-indent,line-height,white-space');
        $config->set('Cache.DefinitionImpl', null);
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'data' => true]);

        $purifier = new \HTMLPurifier($config);
        return $purifier->purify($html);
    }
}
