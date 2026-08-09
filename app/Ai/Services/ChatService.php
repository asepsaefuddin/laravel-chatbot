<?php

namespace App\Ai\Services;

use App\Ai\Agents\ChatBot;
use Illuminate\Support\Facades\Log;

class ChatService
{
    public function __construct(
        private ConversationService $conversationService,
        private UploadService $uploadService,
        private PdfService $pdfService
    ) {
    }

    public function send(
        ?string $message,
        $file = null
    ): string {

        // =========================
        // CONVERSATION
        // =========================

        $conversation = $this->conversationService
            ->getCurrentConversation();

        $history = $this->conversationService
            ->getHistory($conversation);

        $upload = null;

        // =========================
        // ADA FILE
        // =========================

        if ($file) {

            $upload = $this->uploadService
                ->upload($file);

            // =========================
            // DOCUMENT
            // =========================

            if ($upload['type'] === 'document') {

                $fullPath = storage_path(
                    'app/public/' . $upload['path']
                );

                // Baca isi PDF
                $documentText = $this->pdfService
                    ->extractText($fullPath);

                // Debug sementara
                Log::info('PDF TEXT:', [
    'text' => $documentText
]);

                $agent = new ChatBot($history);

                $prompt = <<<PROMPT
User mengunggah sebuah dokumen.

Nama file:
{$upload['name']}

Isi dokumen:
{$documentText}

Pertanyaan user:
{$message}

Jawablah pertanyaan user berdasarkan isi dokumen tersebut.
Jika informasi tidak ditemukan dalam dokumen, katakan bahwa informasi tersebut tidak ditemukan.
PROMPT;

                $reply = (string) $agent->prompt($prompt);

            } else {

                $reply = match ($upload['type']) {

                    'image' =>
                        "Gambar berhasil diterima: {$upload['name']}",

                    'audio' =>
                        "Audio berhasil diterima: {$upload['name']}",

                    default =>
                        "File tidak didukung."
                };
            }

        } else {

            // =========================
            // TANPA FILE
            // =========================

            $agent = new ChatBot($history);

            $reply = (string) $agent->prompt($message);
        }

        // =========================
        // SAVE USER MESSAGE
        // =========================

        $this->conversationService
            ->saveUserMessage(
                $conversation,
                $message,
                $upload
            );

        // =========================
        // SAVE AI MESSAGE
        // =========================

        $this->conversationService
            ->saveAssistantMessage(
                $conversation,
                $reply
            );

        return $reply;
    }
}