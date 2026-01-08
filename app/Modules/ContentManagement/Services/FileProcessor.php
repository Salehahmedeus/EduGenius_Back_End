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
        $parser = new Parser();
        $pdf = $parser->parseFile($path);
        return $pdf->getText();
    }

    private function extractDocx($path)
    {
        $phpWord = IOFactory::load($path);
        $text = '';
        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if (method_exists($element, 'getText')) {
                    $text .= $element->getText() . " ";
                }
            }
        }
        return trim($text);
    }
}
