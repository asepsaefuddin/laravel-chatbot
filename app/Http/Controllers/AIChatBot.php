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
    //  dd("MASUK CONTROLLER");

    $request->validate([

    'message'=>'nullable|string',

    'file'=>'nullable|file|max:10240'

]);


    $reply = $this->chatService
->send(
    $request->message,
    $request->file('file')
);


    return response()->json([

        'status'=>'success',

        'message'=>$reply

    ]);

}
}