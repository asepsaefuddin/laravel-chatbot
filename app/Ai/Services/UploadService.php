<?php

namespace App\Ai\Services;

use Illuminate\Http\UploadedFile;

class UploadService
{

    public function upload(
        UploadedFile $file
    ): array
    {

        $path = $file->store(
            'ai/uploads',
            'public'
        );


        return [

            'path' => $path,

            'name' => $file->getClientOriginalName(),

            'mime' => $file->getMimeType(),

            'type' => $this->detectType($file)

        ];

    }



    private function detectType(
        UploadedFile $file
    ): string
    {

        $mime = $file->getMimeType();



        if(str_contains($mime, 'image'))
        {
            return 'image';
        }



        if(str_contains($mime, 'audio'))
        {
            return 'audio';
        }



        if(
            str_contains($mime,'pdf') ||
            str_contains($mime,'word') ||
            str_contains($mime,'spreadsheet') ||
            str_contains($mime,'presentation') ||
            str_contains($mime,'text')
        )
        {
            return 'document';
        }



        return 'unknown';

    }

}