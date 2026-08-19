<?php

namespace App\Ai\Services;

use App\Ai\Agents\ChatBot;
use Illuminate\Support\Facades\Log;
use App\Ai\Services\DocumentReaderService;
use Illuminate\Support\Facades\Http;

class ChatService
{
    public function __construct(
        private ConversationService $conversationService,
        private UploadService $uploadService,
        private DocumentReaderService $documentReaderService
    ) {
    }

    public function send(?string $message = null, $file = null): string
    {
        set_time_limit(120);

    // Mengatur timeout Guzzle HTTP Client agar fleksibel saat memproses file
    Http::globalOptions([
        'timeout' => 120,         // Batas total waktu menunggu respon
        'connect_timeout' => 15,  // Batas waktu mencoba koneksi ke Google
    ]);
        $conversation = $this->conversationService->getCurrentConversation();
        $history = $this->conversationService->getHistory($conversation);

        $upload = null;
        
        $cleanMessage = trim((string) $message);
        $hasCustomMessage = !empty($cleanMessage);

        /*
        |--------------------------------------------------------------------------
        | ADA FILE
        |--------------------------------------------------------------------------
        */
        if ($file) {
            $upload = $this->uploadService->upload($file);

            $extension = strtolower($file->getClientOriginalExtension());
            $documentExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'ppt', 'pptx', 'txt'];

            /*
            |--------------------------------------------------------------------------
            | DOKUMEN (PDF, Word, Excel, PPT, Text)
            |--------------------------------------------------------------------------
            */
            if (in_array($extension, $documentExtensions) || in_array($upload['type'] ?? '', ['pdf', 'word', 'excel', 'powerpoint', 'text'])) {

                $documentText = $this->documentReaderService->read($file);

                Log::info('DOCUMENT TEXT READ', [
                    'file' => $upload['name'] ?? $file->getClientOriginalName(),
                    'extension' => $extension,
                    'text_length' => strlen($documentText),
                ]);

                if ($hasCustomMessage) {
                    $prompt = <<<PROMPT
Saya memberikan sebuah dokumen kepada Anda.

Nama file:
{$upload['name']}

Isi dokumen:
{$documentText}

Pertanyaan pengguna:
{$cleanMessage}

Jawablah pertanyaan pengguna berdasarkan isi dokumen tersebut. 
Jika informasi yang ditanyakan tidak terdapat di dokumen, katakan bahwa informasi tersebut tidak ditemukan.
PROMPT;
                } else {
                    $prompt = <<<PROMPT
Saya memberikan sebuah dokumen kepada Anda tanpa instruksi khusus.

Nama file:
{$upload['name']}

Isi dokumen:
{$documentText}

Tugas Anda:
1. Berikan ringkasan dan deskripsi singkat mengenai isi dokumen ini.
2. Jelaskan poin-poin penting utama yang dibahas di dalamnya.
PROMPT;
                }

                $agent = new ChatBot($history);
                $reply = (string) $agent->prompt($prompt);
            }

            /*
            |--------------------------------------------------------------------------
            | GAMBAR (GEMINI VISION VIA LARAVEL AI SDK)
            |--------------------------------------------------------------------------
            */
            elseif (($upload['type'] ?? '') === 'image' || in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {

                $promptText = $hasCustomMessage 
                    ? $cleanMessage 
                    : "Deskripsikan gambar ini secara detail dan sebutkan poin-poin penting yang ada di dalamnya.";

                $agent = new ChatBot($history);

                // Memanggil method promptWithImage langsung dari ChatBot
                $reply = (string) $agent->promptWithImage(
                    prompt: $promptText,
                    imagePath: $file->getRealPath()
                );
            }

            /*
            |--------------------------------------------------------------------------
            | AUDIO & LAINNYA
            |--------------------------------------------------------------------------
            */
            /*
|--------------------------------------------------------------------------
| AUDIO (GEMINI MULTIMODAL AUDIO)
|--------------------------------------------------------------------------
*/
elseif (($upload['type'] ?? '') === 'audio' || in_array($extension, ['mp3', 'wav', 'ogg', 'm4a', 'aac'])) {

    // 1. Tentukan prompt default jika user tidak mengetikkan pesan khusus
    $promptText = $hasCustomMessage 
        ? $cleanMessage 
        : "Dengarkan file audio ini. Transkripsikan stands teksnya secara akurat dan berikan rangkuman mengenai isi pembicaraan di dalam audio tersebut.";

    $agent = new ChatBot($history);

    // 2. Panggil promptWithAudio dengan path file real
    $reply = (string) $agent->promptWithAudio(
        prompt: $promptText,
        audioPath: $file->getRealPath()
    );
}

        }

        /*
        |--------------------------------------------------------------------------
        | TANPA FILE
        |--------------------------------------------------------------------------
        */
        else {
            if (!$hasCustomMessage) {
                return "Silakan ketik pesan atau upload dokumen terlebih dahulu.";
            }

            $agent = new ChatBot($history);
            $reply = (string) $agent->prompt($cleanMessage);
        }

        /*
        |--------------------------------------------------------------------------
        | SIMPAN PESAN KE RIWAYAT
        |--------------------------------------------------------------------------
        */
        $fileName = $upload['name'] ?? 'Dokumen';
        $userSavedText = $hasCustomMessage ? $cleanMessage : "[Mengirim File: {$fileName}]";

        $this->conversationService->saveUserMessage(
            $conversation,
            $userSavedText,
            $upload
        );

        $this->conversationService->saveAssistantMessage(
            $conversation,
            $reply
        );

        return $reply;
    }
}