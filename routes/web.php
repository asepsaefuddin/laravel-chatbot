<?php

use App\Http\Controllers\AIChatBot;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});
// Route::get('/gemini', function(){

//     $response = \Laravel\Ai\agent(
//         instructions: "your assistant helpful"
//     )->prompt("berikan saya pantun yang berirama mengenai error");

//     return (string) $response;

// });

Route::get('/chat', [AIChatBot::class, 'index'])->name('chat');
Route::post('/chat/send', [AIChatBot::class, 'send'])->name('chat.send');
Route::get('/login', [AIChatBot::class, 'login'])->name('login');
// Route::post('/chat/send', function (Request $request) {

//     return response()->json([
//         'success' => true,
//         'message' => $request->input('message'),
//         'file' => $request->file('file')?->getClientOriginalName(),
//         'mime' => $request->file('file')?->getMimeType(),
//         'size' => $request->file('file')?->getSize(),
//         'error' => $request->file('file')?->getError(),
//     ]);

// })->name('chat.send');