<?php

namespace App\Ai\Services;

use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser;

class PdfService
{
    public function extractText(string $path): string
    {
        $parser = new Parser();

        $pdf = $parser->parseFile($path);

        $text = $pdf->getText();

        Log::info('PDF TEXT:', [
            'text' => $text
        ]);

        return $text;
    }
}