<?php

namespace App\Ai\Services;

use App\Models\Conversation;
use Laravel\Ai\Messages\UserMessage;
use Laravel\Ai\Messages\AssistantMessage;

class ConversationService
{
    /**
     * Ambil conversation aktif.
     */
    public function getCurrentConversation(): Conversation
    {
        return Conversation::firstOrCreate(
            [],
            [
                'title' => 'New Chat'
            ]
        );
    }

    /**
     * Ambil seluruh history conversation.
     */
    public function getHistory(Conversation $conversation): array
    {
        $history = [];

        foreach ($conversation->messages()->orderBy('id')->get() as $chat) {

            if ($chat->role === 'user') {

                $history[] = new UserMessage($chat->content);

            } else {

                $history[] = new AssistantMessage($chat->content);

            }

        }

        return $history;
    }

    /**
     * Simpan pesan user.
     */
    public function saveUserMessage(
    Conversation $conversation,
    ?string $message,
    ?array $attachment=null
): void
{


    $conversation->messages()->create([

        'role'=>'user',

        'content'=>$message,

        'attachment'=>$attachment['path'] ?? null,

        'attachment_type'=>$attachment['type'] ?? null

    ]);

}

    /**
     * Simpan balasan AI.
     */
    public function saveAssistantMessage(
        Conversation $conversation,
        string $message
    ): void {

        $conversation->messages()->create([
            'role' => 'assistant',
            'content' => $message
        ]);

    }
}