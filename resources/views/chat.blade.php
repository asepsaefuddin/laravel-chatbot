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
        <!-- Footer Modern ChatGPT Style -->
<footer class="bg-white border-t border-slate-200 p-4">

    <!-- Preview File -->
    <div id="previewArea" class="hidden mb-3"></div>

    <!-- Input Container -->
    <div class="relative bg-white border border-slate-300 rounded-3xl shadow-sm hover:shadow-md transition-all duration-200 focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-blue-500 px-3 py-2">

        <div class="flex items-center gap-2">

            <!-- Plus Button -->
            <div class="relative">

                <button
                    id="uploadButton"
                    type="button"
                    class="w-10 h-10 rounded-full hover:bg-slate-100 flex items-center justify-center text-2xl text-slate-600 transition">
                    +
                </button>

                <!-- Upload Menu -->
                <div
                    id="uploadMenu"
                    class="hidden absolute bottom-14 left-0 w-56 bg-white rounded-2xl shadow-2xl border border-slate-200 py-2 z-50">

                    <label for="imageInput"
                        class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 cursor-pointer text-sm">
                        <span class="text-lg">🖼️</span>
                        <span>Upload Image</span>
                    </label>

                    <label for="fileInput"
                        class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 cursor-pointer text-sm">
                        <span class="text-lg">📄</span>
                        <span>Upload File</span>
                    </label>

                    <label for="audioInput"
                        class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 cursor-pointer text-sm">
                        <span class="text-lg">🎵</span>
                        <span>Upload Audio</span>
                    </label>

                </div>

            </div>

            <!-- Hidden Inputs -->
            <input id="imageInput" type="file" accept="image/*" class="hidden">
            <input id="fileInput" type="file" accept=".pdf,.doc,.docx,.txt" class="hidden">
            <input id="audioInput" type="file" accept="audio/*" class="hidden">

            <!-- Text Input -->
            <input
                id="chatInput"
                type="text"
                placeholder="Message Rihana AI..."
                class="flex-1 bg-transparent border-0 outline-none focus:ring-0 text-slate-800 placeholder:text-slate-400 text-[15px] px-1 py-2">

            <!-- Voice Button -->
            <button
                id="voiceButton"
                type="button"
                class="w-10 h-10 rounded-full hover:bg-slate-100 flex items-center justify-center text-lg text-slate-600 transition">
                🎤
            </button>

            <!-- Send Button -->
            <button
                id="sendButton"
                type="button"
                class="w-10 h-10 rounded-full bg-blue-600 hover:bg-blue-700 text-white flex items-center justify-center shadow-md transition-all duration-200 active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7-7l7 7-7 7"/>
                </svg>
            </button>

        </div>

    </div>

    <p class="text-xs text-center text-slate-400 mt-2">
        Rihana AI can make mistakes. Check important information.
    </p>

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
const uploadButton = document.getElementById('uploadButton');
const uploadMenu = document.getElementById('uploadMenu');

uploadButton.addEventListener('click', (e) => {
    e.stopPropagation();
    uploadMenu.classList.toggle('hidden');
});

document.addEventListener('click', (e) => {
    if (!uploadButton.contains(e.target) && !uploadMenu.contains(e.target)) {
        uploadMenu.classList.add('hidden');
    }
});
</script>

</body>
</html>