<?php

namespace App\Ai\Services;

use App\Ai\Agents\ChatBot;

class ChatService
{
     public function __construct(

        private ConversationService $conversationService,

        private UploadService $uploadService

    ) {
    }

    /**
     * Kirim pesan ke AI.
     */
    public function send(
    ?string $message,
    $file=null
): string
    {
        // Ambil conversation aktif
        $conversation = $this->conversationService
            ->getCurrentConversation();

        // Ambil history
        $history = $this->conversationService
            ->getHistory($conversation);

        // Buat agent
        if($file)
{

    $upload =
        $this->uploadService
            ->upload($file);



    switch($upload['type'])
    {


        case 'image':

            $reply =
            "Ini gambar: ".$upload['path'];

            break;



        case 'audio':

            $reply =
            "Ini audio: ".$upload['path'];

            break;



        case 'document':

            $reply =
            "Ini dokumen: ".$upload['name'];

            break;



        default:

            $reply =
            "File tidak didukung";

    }


}
else
{

    $agent = new ChatBot($history);


    $reply =
    (string) $agent->prompt($message);

}

        // Simpan pesan user
        $this->conversationService
->saveUserMessage(
    $conversation,
    $message,
    $upload ?? null
);

        // Simpan balasan AI
        $this->conversationService
            ->saveAssistantMessage(
                $conversation,
                $reply
            );

        return $reply;
    }
}