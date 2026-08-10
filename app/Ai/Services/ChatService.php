<?php

namespace App\Ai\Services;

use App\Ai\Agents\ChatBot;
use Illuminate\Support\Facades\Log;
// use App\Ai\Agents\ChatBot;
use App\Ai\Services\DocumentReaderService;

class ChatService
{
    public function __construct(

    private ConversationService $conversationService,

    private UploadService $uploadService,

    private DocumentReaderService $documentReaderService

) {
}

    public function send(
    ?string $message,
    $file = null
): string {

    $conversation = $this->conversationService
        ->getCurrentConversation();

    $history = $this->conversationService
        ->getHistory($conversation);

    $upload = null;

    /*
    |--------------------------------------------------------------------------
    | ADA FILE
    |--------------------------------------------------------------------------
    */

    if ($file) {

        $upload = $this->uploadService
            ->upload($file);

        /*
        |--------------------------------------------------------------------------
        | DOCUMENT
        |--------------------------------------------------------------------------
        */

        if (in_array($upload['type'], [
            'pdf',
            'word',
            'excel',
            'powerpoint',
            'text'
        ])) {

            $documentText =
                $this->documentReaderService
                    ->read($file);

            /*
            |--------------------------------------------------------------------------
            | DEBUG
            |--------------------------------------------------------------------------
            */

            \Illuminate\Support\Facades\Log::info(
                'DOCUMENT TEXT',
                [
                    'file' => $upload['name'],
                    'type' => $upload['type'],
                    'text' => $documentText,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Gabungkan isi file dengan pertanyaan user
            |--------------------------------------------------------------------------
            */

            $prompt = <<<PROMPT
Saya memberikan sebuah dokumen kepada Anda.

Nama file:
{$upload['name']}

Isi dokumen:
{$documentText}

Pertanyaan pengguna:
{$message}

Jawablah pertanyaan pengguna berdasarkan isi dokumen tersebut.
Jika informasi yang ditanyakan tidak terdapat di dokumen, katakan bahwa informasi tersebut tidak ditemukan.
PROMPT;

            $agent = new ChatBot($history);

            $reply = (string) $agent->prompt($prompt);
        }

        /*
        |--------------------------------------------------------------------------
        | IMAGE
        |--------------------------------------------------------------------------
        */

        elseif ($upload['type'] === 'image') {

            $reply =
                "Gambar berhasil diupload: "
                . $upload['name'];
        }

        /*
        |--------------------------------------------------------------------------
        | AUDIO
        |--------------------------------------------------------------------------
        */

        elseif ($upload['type'] === 'audio') {

            $reply =
                "Audio berhasil diupload: "
                . $upload['name'];
        }

        /*
        |--------------------------------------------------------------------------
        | FILE TIDAK DIDUKUNG
        |--------------------------------------------------------------------------
        */

        else {

            $reply =
                "File berhasil diupload, tetapi "
                . "jenis file tersebut belum didukung.";
        }

    }

    /*
    |--------------------------------------------------------------------------
    | TANPA FILE
    |--------------------------------------------------------------------------
    */

    else {

        $agent = new ChatBot($history);

        $reply = (string) $agent->prompt(
            $message
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SIMPAN PESAN USER
    |--------------------------------------------------------------------------
    */

    $this->conversationService
        ->saveUserMessage(
            $conversation,
            $message,
            $upload
        );

    /*
    |--------------------------------------------------------------------------
    | SIMPAN JAWABAN AI
    |--------------------------------------------------------------------------
    */

    $this->conversationService
        ->saveAssistantMessage(
            $conversation,
            $reply
        );

    return $reply;
}
}