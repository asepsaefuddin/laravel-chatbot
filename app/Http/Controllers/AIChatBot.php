<?php

namespace App\Http\Controllers;

use App\Ai\Agents\ChatBot;
use Illuminate\Http\Request;

class AIChatBot extends Controller
{
    //
    public function index(){
        return view('chat');
    }
    public function send(Request $request){
        // validate
        $request->validate([
            "message" => "required|string"
        ]);
        // agen
        $agen = new ChatBot();
        $response = $agen->prompt($request->message);
        return response()->json([
            "status" => "success",
            "message" => (string) $response
        ]);
    }
}
