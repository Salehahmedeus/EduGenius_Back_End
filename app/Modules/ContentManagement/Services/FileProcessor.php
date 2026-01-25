<?php

namespace App\Modules\ContentManagement\Services;

use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser;
use PhpOffice\PhpWord\IOFactory;

class FileProcessor
{
    public function extractText(string $fullPath, string $mimeType): string
    {
        if (!file_exists($fullPath)) {
            return "";
        }

        try {
            if ($mimeType === 'application/pdf') {
                return $this->extractPdf($fullPath);
            }

            if (str_contains($mimeType, 'wordprocessingml') || str_contains($mimeType, 'msword')) {
                return $this->extractDocx($fullPath);
            }

            return file_get_contents($fullPath);
        } catch (\Exception $e) {
            Log::error("Extraction Error: " . $e->getMessage());
            return "";
        }
    }

    private function extractPdf($path)
    {
        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($path);
            if (!$pdf) {
                return "";
            }
            return is_callable([$pdf, 'getText']) ? $pdf->getText() : "";
        } catch (\Exception $e) {
            Log::error("PDF Extraction Error: " . $e->getMessage());
            return "";
        }
    }

    private function extractDocx($path)
    {
        try {
            $phpWord = IOFactory::load($path);
            $text = '';
            foreach ($phpWord->getSections() as $section) {
                $text .= $this->processElements($section->getElements());
            }
            return trim($text);
        } catch (\Exception $e) {
            Log::error("Docx Extraction Error: " . $e->getMessage());
            return "";
        }
    }

    private function processElements($elements): string
    {
        $text = '';
        foreach ($elements as $element) {
            if (is_callable([$element, 'getText'])) {
                $text .= $element->getText() . " ";
            } elseif (method_exists($element, 'getElements')) {
                $text .= $this->processElements($element->getElements());
            } elseif (method_exists($element, 'getRows')) {
                // Handle tables
                foreach ($element->getRows() as $row) {
                    foreach ($row->getCells() as $cell) {
                        $text .= $this->processElements($cell->getElements());
                    }
                }
            }
        }
        return $text;
    }
}
