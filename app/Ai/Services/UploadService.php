<?php

namespace App\Ai\Services;

use Illuminate\Http\UploadedFile;

class UploadService
{
    public function upload(UploadedFile $file): array
    {
        $path = $file->store(
            'ai/uploads',
            'public'
        );

        $type = $this->detectType($file);

        return [
            'path' => $path,
            'name' => $file->getClientOriginalName(),
            'mime' => $file->getMimeType(),
            'type' => $type,
        ];
    }

    private function detectType(UploadedFile $file): string
    {
        $mime = $file->getMimeType();
        $extension = strtolower(
            $file->getClientOriginalExtension()
        );

        // IMAGE
        if (str_starts_with($mime, 'image/')) {
            return 'image';
        }

        // AUDIO
        if (str_starts_with($mime, 'audio/')) {
            return 'audio';
        }

        // PDF
        if ($extension === 'pdf') {
            return 'pdf';
        }

        // WORD
        if (in_array($extension, [
            'doc',
            'docx'
        ])) {
            return 'word';
        }

        // EXCEL
        if (in_array($extension, [
            'xls',
            'xlsx',
            'csv'
        ])) {
            return 'excel';
        }

        // POWERPOINT
        if (in_array($extension, [
            'ppt',
            'pptx'
        ])) {
            return 'powerpoint';
        }

        // TEXT
        if ($extension === 'txt') {
            return 'text';
        }

        return 'unknown';
    }
}