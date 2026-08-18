<?php

namespace App\Ai\Services;

use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory as SpreadsheetIOFactory;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use PhpOffice\PhpPresentation\IOFactory as PresentationIOFactory;
use Smalot\PdfParser\Parser as PdfParser;

class DocumentReaderService
{
    public function read(UploadedFile $file): string
    {
        $extension = strtolower(
            $file->getClientOriginalExtension()
        );

        return match ($extension) {
            'pdf' => $this->readPdf($file),
            'doc', 'docx' => $this->readWord($file),
            'xls', 'xlsx', 'csv' => $this->readExcel($file),
            'ppt', 'pptx' => $this->readPowerPoint($file),
            'txt' => $this->readText($file),
            default => throw new \RuntimeException("File .$extension belum didukung."),
        };
    }

    /*
    |--------------------------------------------------------------------------
    | PDF (Dual Method: shell_exec pdftotext & Smalot PdfParser)
    |--------------------------------------------------------------------------
    */
    private function readPdf(UploadedFile $file): string
    {
        $path = $file->getRealPath();
        $text = '';

        // Opsi 1: Coba gunakan pdftotext (sangat cepat & hemat RAM jika server Linux memiliki poppler-utils)
        if (function_exists('shell_exec')) {
            $command = 'pdftotext ' . escapeshellarg($path) . ' -';
            $output = @shell_exec($command);
            if ($output) {
                $text = trim($output);
            }
        }

        // Opsi 2: Fallback ke Smalot PDF Parser (Pure PHP) jika pdftotext gagal/tidak terinstall
        if (empty($text)) {
            try {
                $parser = new PdfParser();
                $pdf = $parser->parseFile($path);
                $text = trim($pdf->getText());
            } catch (\Throwable $e) {
                throw new \RuntimeException('Gagal membaca dokumen PDF: ' . $e->getMessage());
            }
        }

        if (empty($text)) {
            throw new \RuntimeException('File PDF kosong atau berupa gambar/scan (butuh OCR).');
        }

        return $text;
    }

    /*
    |--------------------------------------------------------------------------
    | WORD
    |--------------------------------------------------------------------------
    */
    private function readWord(UploadedFile $file): string
    {
        try {
            $phpWord = WordIOFactory::load($file->getRealPath());
        } catch (\Throwable $e) {
            // Fallback untuk file .docx dari path temporary
            $phpWord = WordIOFactory::createReader('Word2007')->load($file->getRealPath());
        }

        $text = '';
        foreach ($phpWord->getSections() as $section) {
            $text .= $this->extractWordElements($section->getElements());
        }

        return trim($text);
    }

    private function extractWordElements(array $elements): string
    {
        $text = '';

        foreach ($elements as $element) {
            if ($element instanceof \PhpOffice\PhpWord\Element\Text) {
                $value = trim($element->getText());
                if ($value !== '') {
                    $text .= $value . " ";
                }
            } elseif ($element instanceof \PhpOffice\PhpWord\Element\TextRun) {
                $runText = $this->extractWordElements($element->getElements());
                if (trim($runText) !== '') {
                    $text .= trim($runText) . "\n";
                }
            } elseif ($element instanceof \PhpOffice\PhpWord\Element\Table) {
                $text .= "\n[TABEL]\n";
                foreach ($element->getRows() as $row) {
                    $rowValues = [];
                    foreach ($row->getCells() as $cell) {
                        $cellText = trim($this->extractWordElements($cell->getElements()));
                        if ($cellText !== '') {
                            $rowValues[] = $cellText;
                        }
                    }
                    if (!empty($rowValues)) {
                        $text .= implode(' | ', $rowValues) . "\n";
                    }
                }
                $text .= "\n";
            } elseif ($element instanceof \PhpOffice\PhpWord\Element\ListItem) {
                $value = trim($element->getText());
                if ($value !== '') {
                    $text .= "• " . $value . "\n";
                }
            } elseif (method_exists($element, 'getElements')) {
                $children = $element->getElements();
                if (is_array($children)) {
                    $text .= $this->extractWordElements($children);
                }
            }
        }

        return $text;
    }

    /*
    |--------------------------------------------------------------------------
    | EXCEL
    |--------------------------------------------------------------------------
    */
    private function readExcel(UploadedFile $file): string
    {
        $spreadsheet = SpreadsheetIOFactory::load($file->getRealPath());
        $text = '';

        foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
            $text .= "\n=== SHEET: " . $sheet->getTitle() . " ===\n";

            foreach ($sheet->getRowIterator() as $row) {
                $values = [];
                foreach ($row->getCellIterator() as $cell) {
                    $value = $cell->getFormattedValue();
                    if ($value !== null && $value !== '') {
                        $values[] = $value;
                    }
                }

                if (!empty($values)) {
                    $text .= implode(' | ', $values) . "\n";
                }
            }
        }

        return trim($text);
    }

    /*
    |--------------------------------------------------------------------------
    | POWERPOINT
    |--------------------------------------------------------------------------
    */
    /*
    |--------------------------------------------------------------------------
    | POWERPOINT (Mendukung PPT Interaktif / Kuis / Group / Tabel)
    |--------------------------------------------------------------------------
    */

    private function readPowerPoint(UploadedFile $file): string
    {
        try {
            $presentation = PresentationIOFactory::load($file->getRealPath());
        } catch (\Throwable $e) {
            throw new \RuntimeException('Gagal membaca file PowerPoint: ' . $e->getMessage());
        }

        $text = '';
        $slideNumber = 1;

        foreach ($presentation->getAllSlides() as $slide) {
            $text .= "\n====================================\n";
            $text .= "SLIDE {$slideNumber}\n";
            $text .= "====================================\n";

            foreach ($slide->getShapeCollection() as $shape) {
                $text .= $this->extractPowerPointShape($shape);
            }

            $slideNumber++;
        }

        $text = trim($text);

        if ($text === '') {
            throw new \RuntimeException('Tidak ditemukan teks yang dapat dibaca dari file PowerPoint ini.');
        }

        return $text;
    }

    private function extractPowerPointShape($shape, int $level = 0): string
    {
        $text = '';
        $indent = str_repeat('  ', $level);

        // 1. Teks Biasa (PlainText / Text)
        if (method_exists($shape, 'getPlainText')) {
            try {
                $val = trim($shape->getPlainText());
                if ($val !== '') {
                    $text .= $indent . $val . "\n";
                }
            } catch (\Throwable $e) {}
        }

        // 2. Teks dalam Paragraf / RichText (Sering digunakan di PPT Kuis)
        if (empty(trim($text)) && method_exists($shape, 'getParagraphs')) {
            try {
                foreach ($shape->getParagraphs() as $paragraph) {
                    $pText = '';
                    if (method_exists($paragraph, 'getRichTextElements')) {
                        foreach ($paragraph->getRichTextElements() as $element) {
                            if (method_exists($element, 'getText')) {
                                $pText .= $element->getText();
                            }
                        }
                    }
                    if (trim($pText) !== '') {
                        $text .= $indent . trim($pText) . "\n";
                    }
                }
            } catch (\Throwable $e) {}
        }

        // 3. Jika Shape Berupa TABEL (Sering digunakan untuk Pilihan Ganda / Kuis)
        if (method_exists($shape, 'getRows')) {
            try {
                foreach ($shape->getRows() as $row) {
                    $rowValues = [];
                    if (method_exists($row, 'getCells')) {
                        foreach ($row->getCells() as $cell) {
                            $cellText = '';
                            if (method_exists($cell, 'getParagraphs')) {
                                foreach ($cell->getParagraphs() as $p) {
                                    if (method_exists($p, 'getRichTextElements')) {
                                        foreach ($p->getRichTextElements() as $el) {
                                            if (method_exists($el, 'getText')) {
                                                $cellText .= $el->getText();
                                            }
                                        }
                                    }
                                }
                            }
                            if (trim($cellText) !== '') {
                                $rowValues[] = trim($cellText);
                            }
                        }
                    }
                    if (!empty($rowValues)) {
                        $text .= $indent . implode(' | ', $rowValues) . "\n";
                    }
                }
            } catch (\Throwable $e) {}
        }

        // 4. Jika Shape berupa GROUP (Tombol/Kotak bertumpuk di Kuis)
        if (method_exists($shape, 'getShapeCollection')) {
            try {
                foreach ($shape->getShapeCollection() as $childShape) {
                    $text .= $this->extractPowerPointShape($childShape, $level + 1);
                }
            } catch (\Throwable $e) {}
        }

        return $text;
    }

    /*
    |--------------------------------------------------------------------------
    | TXT
    |--------------------------------------------------------------------------
    */
    private function readText(UploadedFile $file): string
    {
        $content = file_get_contents($file->getRealPath());
        if ($content === false) {
            throw new \RuntimeException('Tidak dapat membaca file TXT.');
        }

        return trim($content);
    }
}