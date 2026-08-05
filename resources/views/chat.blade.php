<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <title>Rihana AI</title>
</head>

<body class="bg-slate-100">

<div class="h-screen p-5">

    <div class="max-w-5xl h-full mx-auto bg-white rounded-2xl shadow-2xl flex flex-col overflow-hidden">

        <!-- Header -->
        <header class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-4 flex justify-between items-center">

            <div class="flex items-center gap-3">

                <div class="w-12 h-12 rounded-full bg-white text-blue-600 flex items-center justify-center text-xl">
                    🤖
                </div>

                <div>
                    <h1 class="font-bold text-lg">
                        Rihana AI
                    </h1>

                    <p class="text-sm text-blue-100">
                        Your Smart Assistant
                    </p>
                </div>

            </div>

            <span class="bg-green-500 px-3 py-1 rounded-full text-xs">
                ● Online
            </span>

        </header>

        <!-- Chat -->
        <main
            id="chatDisplay"
            class="flex-1 overflow-y-auto bg-slate-50 p-6 space-y-4">

        </main>

        <!-- Input -->
        <footer class="bg-white border-t p-4">

            <div class="flex gap-3">
                <input
                    id="chatInput"
                    type="text"
                    placeholder="Type your message..."
                    class="flex-1 border rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-blue-500">

                <button
                    id="sendButton"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 rounded-xl transition">
                    Send
                </button>

            </div>

        </footer>

    </div>

</div>

<script>

const input = document.getElementById("chatInput");
const button = document.getElementById("sendButton");
const display = document.getElementById("chatDisplay");

async function sendMessage(){

    const message = input.value.trim();

    if(message === "") return;

    // User Bubble
    display.insertAdjacentHTML("beforeend", `
<div class="flex justify-end mb-4">

    <div class="bg-blue-600 text-white px-4 py-2 rounded-2xl rounded-br-md
                shadow-md w-fit max-w-[380px]
                text-[15px] leading-6 break-words">
        ${message}
    </div>

</div>
`);

    input.value = "";

    display.scrollTop = display.scrollHeight;

    // Loading Bubble
    display.insertAdjacentHTML("beforeend", `
<div id="typing" class="flex items-end gap-3 mb-4">

    <div class="w-10 h-10 rounded-full bg-indigo-600 text-white flex items-center justify-center text-lg">
        🤖
    </div>

    <div class="bg-white border border-slate-200 px-4 py-2 rounded-2xl shadow-md text-gray-500 text-[15px]">
        Rihana is typing...
    </div>

</div>
`);

    display.scrollTop = display.scrollHeight;

    try{

        const response = await axios.post("/chat/send",{
            message
        });

        document.getElementById("typing").remove();

        display.insertAdjacentHTML("beforeend", `
<div class="flex items-end gap-3 mb-4">

    <div class="w-10 h-10 rounded-full bg-indigo-600 text-white
                flex items-center justify-center text-lg">
        🤖
    </div>

    <div class="bg-white border border-slate-200
                px-4 py-2
                rounded-2xl rounded-bl-md
                shadow-md
                w-fit
                max-w-[380px]
                text-[15px]
                leading-6
                break-words">

        ${response.data.message}

    </div>

</div>
`);

    }catch(error){

        document.getElementById("typing").remove();

        display.insertAdjacentHTML("beforeend", `
            <div class="flex justify-start">
                <div class="max-w-[75%] bg-red-100 text-red-700 border border-red-300 rounded-2xl rounded-bl-md px-4 py-3 shadow">
                    Failed to connect to AI.
                </div>
            </div>
        `);

        console.error(error);

    }

    display.scrollTop = display.scrollHeight;

}

button.addEventListener("click", sendMessage);

input.addEventListener("keypress",(e)=>{
    if(e.key==="Enter"){
        sendMessage();
    }
});

</script>

</body>
</html>