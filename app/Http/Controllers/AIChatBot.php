<?php

namespace App\Http\Controllers;

use App\Ai\Services\ChatService;
use Illuminate\Http\Request;
use Throwable;

class AIChatBot extends Controller
{
    public function __construct(
        private ChatService $chatService
    ) {
    }

    public function index()
    {
        return view('chat');
    }

    public function send(Request $request)
    {
        try {

            $request->validate([
                'message' => 'nullable|string',
                'file' => 'nullable|file|max:10240',
            ]);

            $file = $request->file('file');

            $reply = $this->chatService->send(
                $request->input('message'),
                $file
            );

            return response()->json([
                'success' => true,
                'message' => $reply,
            ]);

        } catch (Throwable $e) {

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }
}