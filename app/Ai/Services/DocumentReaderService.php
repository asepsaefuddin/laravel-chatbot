<?php

namespace App\Ai\Services;

use Illuminate\Http\UploadedFile;
use Smalot\PdfParser\Parser;
use PhpOffice\PhpSpreadsheet\IOFactory as SpreadsheetIOFactory;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use PhpOffice\PhpPresentation\IOFactory as PresentationIOFactory;

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

            default => throw new \RuntimeException(
                "File .$extension belum didukung."
            ),
        };
    }

    /**
     * Membaca PDF.
     */
    private function readPdf(UploadedFile $file): string
{
    $path = $file->getRealPath();

    if (!$path || !file_exists($path)) {
        throw new \RuntimeException(
            'File PDF tidak ditemukan.'
        );
    }

    try {

        $parser = new Parser();

        $pdf = $parser->parseFile($path);

        $text = $pdf->getText();

        $text = trim($text);

        if ($text === '') {
            throw new \RuntimeException(
                'PDF berhasil dibuka tetapi tidak memiliki teks yang dapat dibaca.'
            );
        }

        return $text;

    } catch (\Throwable $e) {

        throw new \RuntimeException(
            'Gagal membaca PDF: ' . $e->getMessage(),
            0,
            $e
        );
    }
}

    /**
     * Membaca DOC / DOCX.
     */
    private function readWord(UploadedFile $file): string
    {
        $phpWord = WordIOFactory::load(
            $file->getRealPath()
        );

        $text = '';

        foreach ($phpWord->getSections() as $section) {

            foreach ($section->getElements() as $element) {

                if (method_exists($element, 'getText')) {

                    $value = $element->getText();

                    if (is_string($value)) {
                        $text .= $value . "\n";
                    }
                }

                if (method_exists($element, 'getElements')) {

                    foreach ($element->getElements() as $child) {

                        if (method_exists($child, 'getText')) {

                            $value = $child->getText();

                            if (is_string($value)) {
                                $text .= $value . "\n";
                            }
                        }
                    }
                }
            }
        }

        return trim($text);
    }

    /**
     * Membaca XLS / XLSX / CSV.
     */
    private function readExcel(UploadedFile $file): string
    {
        $spreadsheet = SpreadsheetIOFactory::load(
            $file->getRealPath()
        );

        $text = '';

        foreach ($spreadsheet->getWorksheetIterator() as $sheet) {

            $text .= "\n";
            $text .= "=== SHEET: "
                . $sheet->getTitle()
                . " ===\n";

            foreach (
                $sheet->getRowIterator()
                as $row
            ) {

                $values = [];

                foreach (
                    $row->getCellIterator()
                    as $cell
                ) {

                    $values[] = $cell->getFormattedValue();
                }

                $text .= implode(
                    ' | ',
                    $values
                ) . "\n";
            }
        }

        return trim($text);
    }

    /**
     * Membaca PPT / PPTX.
     */
    private function readPowerPoint(UploadedFile $file): string
{
    $path = $file->getRealPath();

    if (!$path || !file_exists($path)) {
        throw new \RuntimeException(
            'File PowerPoint tidak ditemukan.'
        );
    }

    try {

        $presentation = PresentationIOFactory::load($path);

        $text = '';

        foreach ($presentation->getAllSlides() as $slide) {

            $slideText = '';

            foreach ($slide->getShapeCollection() as $shape) {

                // Text shape
                if (method_exists($shape, 'getPlainText')) {

                    $value = $shape->getPlainText();

                    if (is_string($value) && trim($value) !== '') {
                        $slideText .= trim($value) . "\n";
                    }
                }

                // Rich text
                elseif (method_exists($shape, 'getRichTextElements')) {

                    foreach ($shape->getRichTextElements() as $element) {

                        if (method_exists($element, 'getText')) {

                            $value = $element->getText();

                            if (
                                is_string($value) &&
                                trim($value) !== ''
                            ) {
                                $slideText .= trim($value) . "\n";
                            }
                        }
                    }
                }

                // Group shape
                if (method_exists($shape, 'getShapeCollection')) {

                    foreach (
                        $shape->getShapeCollection()
                        as $childShape
                    ) {

                        if (method_exists($childShape, 'getPlainText')) {

                            $value = $childShape->getPlainText();

                            if (
                                is_string($value) &&
                                trim($value) !== ''
                            ) {
                                $slideText .= trim($value) . "\n";
                            }
                        }
                    }
                }
            }

            if (trim($slideText) !== '') {

                $text .=
                    "=== SLIDE ===\n" .
                    trim($slideText) .
                    "\n\n";
            }
        }

        $text = trim($text);

        if ($text === '') {

            throw new \RuntimeException(
                'PowerPoint berhasil dibuka tetapi tidak ditemukan teks.'
            );
        }

        return $text;

    } catch (\Throwable $e) {

        throw new \RuntimeException(
            'Gagal membaca PowerPoint: ' . $e->getMessage(),
            0,
            $e
        );
    }
}

    /**
     * Membaca TXT.
     */
    private function readText(UploadedFile $file): string
    {
        $content = file_get_contents(
            $file->getRealPath()
        );

        if ($content === false) {
            throw new \RuntimeException(
                'Tidak dapat membaca file TXT.'
            );
        }

        return trim($content);
    }
}