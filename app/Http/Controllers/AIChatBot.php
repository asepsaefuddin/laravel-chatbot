<?php

namespace App\Http\Controllers;

use App\Ai\Services\ChatService;
use Illuminate\Http\Request;

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
        $request->validate([
            'message' => 'nullable|string',
            'file' => [
    'nullable',
    'file',
    'max:10240',
    'mimes:jpg,jpeg,png,gif,webp,
          mp3,wav,m4a,
          pdf,
          doc,docx,
          xls,xlsx,csv,
          ppt,pptx,
          txt',
],
        ]);

        $reply = $this->chatService->send(
            $request->input('message'),
            $request->file('file')
        );

        return response()->json([
            'success' => true,
            'message' => $reply,
        ]);
    }
}