<?php

namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Files\Image;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Files\Audio;
use Laravel\Ai\Files\Video;
use Laravel\Ai\Promptable;
use Stringable;

class ChatBot implements Agent, Conversational, HasTools
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    protected array $messages = [];

    protected string $model = 'gemini-1.5-flash';

    public function __construct(array $messages = [])
    {
        $this->messages = $messages;
    }

    public function instructions(): Stringable|string
    {
        return 'You are rihana and you a helpful assistant.';
    }

    /**
     * Get the list of messages comprising the conversation so far.
     *
     * @return Message[]
     */
    public function messages(): iterable
{
    return $this->messages;
}

    /**
     * Get the tools available to the agent.
     *
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [];
    }
    /**
     * Menerima prompt teks dan attachment gambar
     */
    public function promptWithImage(string $prompt, string $imagePath)
    {
        return $this->prompt(
            prompt: $prompt,
            attachments: [
                Image::fromPath($imagePath)
            ]
        );
    }
    /**
     * Menerima prompt teks dan attachment audio
     */
    public function promptWithAudio(string $prompt, string $audioPath)
    {
        return $this->prompt(
            prompt: $prompt,
            attachments: [
                Audio::fromPath($audioPath)
            ]
        );
    }
    public function promptWithVideo(string $prompt, string $videoPath)
    {
        return $this->prompt(
            prompt: $prompt,
            attachments: [Video::fromPath($videoPath)]
        );
    }
}
