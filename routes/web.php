<?php

use App\Http\Controllers\AIChatBot;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/gemini', function(){
    $response = \Laravel\Ai\agent(
        instructions: "your asistant helpful"
    )->prompt("berikan saya pantun yang ber irama mengenai error");
    return $response;
});

Route::get('/chat', [AIChatBot::class, 'index'])->name('chat');
Route::post('/chat/send', [AIChatBot::class, 'send'])->name('chat.send');