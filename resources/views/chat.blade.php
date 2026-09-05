<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <title>Rihana AI</title>
    <!-- Marked.js untuk memproses format Markdown -->
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

    <!-- Prism.js CSS (Syntax Highlighting Ala Gemini / Dark Theme) -->
    <link href="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/themes/prism-tomorrow.min.css" rel="stylesheet" />

    <!-- Custom CSS untuk mempercantik elemen Markdown di balasan AI -->
    <style>
        .markdown-content h1, 
        .markdown-content h2, 
        .markdown-content h3 {
            font-weight: 700;
            color: #1e293b;
            margin-top: 0.75rem;
            margin-bottom: 0.35rem;
        }
        .markdown-content h1 { font-size: 1.25rem; }
        .markdown-content h2 { font-size: 1.1rem; }
        .markdown-content h3 { font-size: 1rem; color: #2563eb; }
        
        .markdown-content p {
            margin-bottom: 0.5rem;
            line-height: 1.6;
        }
        .markdown-content p:last-child {
            margin-bottom: 0;
        }
        .markdown-content ul, 
        .markdown-content ol {
            padding-left: 1.25rem;
            margin-top: 0.25rem;
            margin-bottom: 0.5rem;
        }
        .markdown-content ul { list-style-type: disc; }
        .markdown-content ol { list-style-type: decimal; }
        .markdown-content li {
            margin-bottom: 0.2rem;
        }
        .markdown-content hr {
            border: 0;
            border-top: 1px solid #e2e8f0;
            margin: 0.75rem 0;
        }
        .markdown-content strong {
            color: #0f172a;
            font-weight: 600;
        }
        /* Inline Code */
        .markdown-content :not(pre) > code {
            background-color: #f1f5f9;
            color: #0f172a;
            padding: 0.15rem 0.35rem;
            border-radius: 0.25rem;
            font-size: 0.875em;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        }

        /* Container Code Block Ala Gemini */
        .code-block-wrapper {
            margin: 1rem 0;
            border-radius: 0.5rem;
            overflow: hidden;
            background-color: #1d1f21;
            border: 1px solid #374151;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .code-block-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #2d3748;
            padding: 0.4rem 1rem;
            font-size: 0.75rem;
            color: #cbd5e1;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            border-bottom: 1px solid #374151;
        }
        .copy-code-btn {
            background: transparent;
            border: none;
            color: #cbd5e1;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.375rem;
            font-size: 0.75rem;
            padding: 0.2rem 0.5rem;
            border-radius: 0.25rem;
            transition: all 0.2s;
        }
        .copy-code-btn:hover {
            background-color: #4a5568;
            color: #ffffff;
        }
        .code-block-wrapper pre {
            margin: 0 !important;
            padding: 1rem !important;
            background: transparent !important;
            font-size: 0.875rem !important;
            overflow-x: auto;
        }
    </style>
</head>

<body class="bg-slate-100">

<div class="h-screen p-5">

    <div class="max-w-5xl h-full mx-auto bg-white rounded-2xl shadow-2xl flex flex-col overflow-hidden">

        <!-- Header -->
        <header class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-4 flex justify-between items-center">

            <div class="flex items-center gap-3">

                <div class="w-12 h-12 rounded-full bg-white text-blue-600 flex items-center justify-center text-xl shadow">
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

            <span class="bg-green-500 px-3 py-1 rounded-full text-xs font-medium">
                ● Online
            </span>

        </header>

        <!-- Chat Display -->
        <main
            id="chatDisplay"
            class="flex-1 overflow-y-auto bg-slate-50 p-6 space-y-4">

        </main>

        <!-- Footer / Input Area -->
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

                            <label for="videoInput" class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 cursor-pointer text-sm">
                                <span class="text-lg">🎥</span>
                                <span>Upload Video</span>
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
                    <input id="fileInput" type="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.ppt,.pptx,.txt" class="hidden">
                    <input id="audioInput" type="file" accept="audio/*" class="hidden">
                    <input id="videoInput" type="file" accept="video/*" class="hidden">

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

<!-- Prism.js Script untuk Syntax Highlighting -->
<script src="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/prism.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/plugins/autoloader/prism-autoloader.min.js"></script>

<script>
const input = document.getElementById("chatInput");
const button = document.getElementById("sendButton");
const display = document.getElementById("chatDisplay");

const uploadButton = document.getElementById("uploadButton");
const uploadMenu = document.getElementById("uploadMenu");

const imageInput = document.getElementById("imageInput");
const fileInput = document.getElementById("fileInput");
const audioInput = document.getElementById("audioInput");
const videoInput = document.getElementById("videoInput");

const previewArea = document.getElementById("previewArea");

let selectedFile = null;

// ================================
// Custom Marked Renderer (Kompatibel Semua Versi Marked.js)
// ================================
const renderer = new marked.Renderer();

renderer.code = function(codeArg, langArg) {
    // Handling fleksibel: Jika Marked versi baru mengirim object, ambil dari codeArg.text & codeArg.lang
    let codeText = typeof codeArg === 'object' ? (codeArg.text || '') : (codeArg || '');
    let language = typeof codeArg === 'object' ? (codeArg.lang || 'plaintext') : (langArg || 'plaintext');

    // Pastikan codeText adalah string sebelum di-replace
    codeText = String(codeText);

    // Escape karakter HTML khusus
    const escapedCode = codeText
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");

    return `
        <div class="code-block-wrapper">
            <div class="code-block-header">
                <span class="font-semibold text-xs text-slate-300 uppercase">${language}</span>
                <button type="button" class="copy-code-btn" onclick="copyCode(this)">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    <span>Copy code</span>
                </button>
            </div>
            <pre><code class="language-${language}">${escapedCode}</code></pre>
        </div>
    `;
};

marked.setOptions({
    renderer: renderer,
    gfm: true,
    breaks: true
});

// ================================
// Function Copy Code to Clipboard
// ================================
window.copyCode = function(buttonEl) {
    const wrapper = buttonEl.closest('.code-block-wrapper');
    const codeEl = wrapper.querySelector('code');
    const textToCopy = codeEl.innerText;

    navigator.clipboard.writeText(textToCopy).then(() => {
        const span = buttonEl.querySelector('span');
        const originalText = span.innerText;
        
        span.innerText = "Copied!";
        buttonEl.classList.add('text-green-400');

        setTimeout(() => {
            span.innerText = originalText;
            buttonEl.classList.remove('text-green-400');
        }, 2000);
    }).catch(err => {
        console.error('Gagal menyalin kode:', err);
    });
};

// ================================
// CSRF
// ================================
const csrf = document
    .querySelector('meta[name="csrf-token"]')
    .getAttribute("content");

// ================================
// Upload Menu Toggle
// ================================
uploadButton.addEventListener("click", (e) => {
    e.stopPropagation();
    uploadMenu.classList.toggle("hidden");
});

document.addEventListener("click", (e) => {
    if (!uploadButton.contains(e.target) && !uploadMenu.contains(e.target)) {
        uploadMenu.classList.add("hidden");
    }
});

// ================================
// Choose File
// ================================
imageInput.addEventListener("change", chooseFile);
fileInput.addEventListener("change", chooseFile);
audioInput.addEventListener("change", chooseFile);
videoInput.addEventListener("change", chooseFile);

function chooseFile(e) {
    const file = e.target.files[0];
    if (!file) return;

    const MAX_SIZE = 2 * 1024 * 1024; 

    if (file.size > MAX_SIZE) {
        previewArea.classList.remove("hidden");
        previewArea.innerHTML = `
            <div class="bg-red-100 border border-red-300 text-red-700 rounded-xl p-3 flex justify-between items-center text-sm">
                <div>
                    ⚠️ Ukuran file <strong>${file.name}</strong> melebihi 2 MB. File tidak dapat diunggah.
                </div>
                <button class="text-red-700 font-bold px-2" onclick="removeFile()">✕</button>
            </div>
        `;
        
        e.target.value = "";
        selectedFile = null;
        return;
    }

    selectedFile = file;
    previewArea.classList.remove("hidden");
    previewArea.innerHTML = `
        <div class="bg-slate-100 rounded-xl p-3 flex justify-between items-center text-sm">
            <div class="font-medium text-slate-700 truncate max-w-[80%]">
                📎 ${selectedFile.name}
            </div>
            <button class="text-red-500 hover:text-red-700 font-bold px-2 py-1" onclick="removeFile()">
                ✕
            </button>
        </div>
    `;
}

// ================================
// Remove File
// ================================
window.removeFile = function () {
    selectedFile = null;
    imageInput.value = "";
    fileInput.value = "";
    audioInput.value = "";
    videoInput.value = "";

    previewArea.classList.add("hidden");
    previewArea.innerHTML = "";
}

// ================================
// Send Message
// ================================
async function sendMessage() {
    const message = input.value.trim();

    if (message === "" && !selectedFile) {
        return;
    }

    // Render Chat Bubble User
    display.insertAdjacentHTML("beforeend", `
        <div class="flex justify-end mb-4">
            <div class="bg-blue-600 text-white px-4 py-2.5 rounded-2xl rounded-br-md shadow-md max-w-[80%] text-[15px]">
                ${message ? `<div>${message}</div>` : ''}
                ${selectedFile ? `<div class="mt-1 text-xs bg-blue-700/50 px-2.5 py-1 rounded-md border border-blue-400/30">📎 ${selectedFile.name}</div>` : ''}
            </div>
        </div>
    `);

    input.value = "";
    display.scrollTop = display.scrollHeight;

    // Render Indicator AI Typing...
    display.insertAdjacentHTML("beforeend", `
        <div id="typing" class="flex items-start gap-3 mb-4">
            <div class="w-9 h-9 rounded-full bg-indigo-600 text-white flex items-center justify-center shrink-0 text-sm shadow">
                🤖
            </div>
            <div class="bg-white border border-slate-200 text-slate-500 rounded-2xl rounded-tl-md shadow-sm px-4 py-2.5 text-sm flex items-center gap-2">
                <span>Rihana is thinking</span>
                <span class="animate-pulse">...</span>
            </div>
        </div>
    `);

    display.scrollTop = display.scrollHeight;

    try {
        const formData = new FormData();
        formData.append("message", message);
        if (selectedFile) {
            formData.append("file", selectedFile);
        }

        const response = await axios.post("/chat/send", formData, {
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Content-Type': 'multipart/form-data'
            }
        });

        document.getElementById("typing")?.remove();

        const rawAiReply = typeof response.data === 'string' ? response.data : (response.data.message || response.data.reply || JSON.stringify(response.data));

        // Convert Markdown ke HTML
        const formattedHtml = marked.parse(rawAiReply);

        // Render Chat Bubble AI
        display.insertAdjacentHTML("beforeend", `
            <div class="flex items-start gap-3 mb-4">
                <div class="w-9 h-9 rounded-full bg-indigo-600 text-white flex items-center justify-center shrink-0 text-sm shadow mt-1">
                    🤖
                </div>
                <div class="bg-white border border-slate-200 text-slate-800 rounded-2xl rounded-tl-md shadow-sm px-5 py-3.5 max-w-[85%] text-[15px] markdown-content overflow-x-auto">
                    ${formattedHtml}
                </div>
            </div>
        `);

        // Trigger Prism syntax highlighting pada blok kode yang baru dibuat
        Prism.highlightAllUnder(display);

        removeFile();
    } 
    catch (error) {
        console.error(error);
        document.getElementById("typing")?.remove();

        const errorMessage = error.response?.data?.error || error.response?.data?.message || "Terjadi kesalahan saat menghubungkan ke server.";

        display.insertAdjacentHTML("beforeend", `
            <div class="flex justify-start mb-4">
                <div class="bg-red-50 border border-red-200 text-red-600 rounded-2xl px-4 py-2.5 text-sm max-w-[80%]">
                    ⚠️ ${errorMessage}
                </div>
            </div>
        `);
    }

    display.scrollTop = display.scrollHeight;
}

// ================================
// Events
// ================================
button.addEventListener("click", sendMessage);

input.addEventListener("keypress", (e) => {
    if (e.key === "Enter") {
        sendMessage();
    }
});

// ================================
// Voice Input (Speech Recognition - Toggle Stop & Change Icon)
// ================================
const voiceButton = document.getElementById("voiceButton");
const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

if (SpeechRecognition) {
    const recognition = new SpeechRecognition();

    recognition.lang = "id-ID";
    recognition.interimResults = true; 
    recognition.continuous = true;     

    let isListening = false;
    let finalTranscript = "";

    voiceButton.addEventListener("click", () => {
        if (!isListening) {
            isListening = true;
            finalTranscript = input.value; 
            
            try {
                recognition.start();
            } catch (e) {
                console.error("Gagal memulai perekaman:", e);
            }
        } else {
            isListening = false;
            recognition.stop();
        }
    });

    recognition.onstart = () => {
        voiceButton.innerHTML = "⏹️";
        voiceButton.classList.add("bg-red-100", "text-red-600", "animate-pulse");
        input.placeholder = "Merekam suara... Klik tombol STOP untuk berhenti.";
    };

    recognition.onresult = (event) => {
        let interimTranscript = "";

        for (let i = event.resultIndex; i < event.results.length; ++i) {
            if (event.results[i].isFinal) {
                finalTranscript += event.results[i][0].transcript + " ";
            } else {
                interimTranscript += event.results[i][0].transcript;
            }
        }

        input.value = finalTranscript + interimTranscript;
    };

    recognition.onerror = (event) => {
        console.error("Speech Recognition Error:", event.error);
        if (event.error === "not-allowed") {
            alert("Akses mikrofon ditolak. Silakan izinkan akses mikrofon di pengaturan browser.");
        }
        isListening = false;
    };

    recognition.onend = () => {
        voiceButton.innerHTML = "🎤";
        voiceButton.classList.remove("bg-red-100", "text-red-600", "animate-pulse");
        input.placeholder = "Message Rihana AI...";

        if (isListening) {
            try {
                recognition.start();
            } catch (e) {
                isListening = false;
            }
        }
    };

} else {
    voiceButton.addEventListener("click", () => {
        alert("Fitur Voice Input tidak didukung oleh browser ini. Gunakan Google Chrome atau Edge.");
    });
}
</script>

</body>
</html>