<?php

namespace App\Ai\Services;

use Illuminate\Http\UploadedFile;

class UploadService
{
    public function upload(
        UploadedFile $file
    ): array {

        $path = $file->store(
            'ai/uploads',
            'public'
        );

        return [
            'path' => $path,
            'name' => $file->getClientOriginalName(),
            'mime' => $file->getMimeType(),
            'type' => $this->detectType($file),
        ];
    }

    private function detectType(
        UploadedFile $file
    ): string {

        $mime = $file->getMimeType();

        $extension = strtolower(
            $file->getClientOriginalExtension()
        );

        if (str_starts_with($mime, 'image/')) {
            return 'image';
        }

        if (str_starts_with($mime, 'audio/')) {
            return 'audio';
        }

        if ($extension === 'pdf') {
            return 'pdf';
        }

        if (in_array($extension, [
            'doc',
            'docx'
        ])) {
            return 'word';
        }

        if (in_array($extension, [
            'xls',
            'xlsx',
            'csv'
        ])) {
            return 'excel';
        }

        if (in_array($extension, [
            'ppt',
            'pptx'
        ])) {
            return 'powerpoint';
        }

        if ($extension === 'txt') {
            return 'text';
        }

        return 'unknown';
    }
}