<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>WhatsApp Web - {{ $device->device_name }}</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        body {
            overflow: hidden;
        }
        .chat-list {
            height: calc(100vh - 120px);
            overflow-y: auto;
        }
        .message-area {
            height: calc(100vh - 180px);
            overflow-y: auto;
        }
        .message-bubble {
            max-width: 65%;
            word-wrap: break-word;
        }
        .message-outgoing {
            background-color: #dcf8c6;
            margin-left: auto;
        }
        .message-incoming {
            background-color: white;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar - Chat List -->
        <div class="w-1/3 bg-white border-r border-gray-200 flex flex-col">
            <!-- Header -->
            <div class="bg-gray-100 p-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-lg font-semibold text-gray-800">WhatsApp Web</h1>
                        <p class="text-xs text-gray-600">{{ $device->phone_number ?? $device->device_name }}</p>
                    </div>
                    <a href="{{ route('whatsapp.devices.index') }}" class="text-gray-600 hover:text-gray-900" title="Kembali">
                        <i class="fas fa-times text-xl"></i>
                    </a>
                </div>
            </div>

            <!-- Search -->
            <div class="p-3 border-b border-gray-200">
                <div class="relative">
                    <input type="text" id="searchChat" placeholder="Cari atau mulai chat baru" class="w-full pl-10 pr-4 py-2 bg-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
            </div>

            <!-- Tabs -->
            <div class="flex border-b border-gray-200">
                <button onclick="switchTab('chats')" id="tab-chats" class="flex-1 py-3 text-sm font-medium text-emerald-600 border-b-2 border-emerald-600">
                    <i class="fas fa-comment-dots mr-1"></i> Chats
                </button>
                <button onclick="switchTab('contacts')" id="tab-contacts" class="flex-1 py-3 text-sm font-medium text-gray-600 hover:bg-gray-50">
                    <i class="fas fa-address-book mr-1"></i> Kontak
                </button>
            </div>

            <!-- Chat List -->
            <div id="chats-list" class="chat-list">
                @forelse($recentChats as $chat)
                    <div class="chat-item p-4 border-b border-gray-100 hover:bg-gray-50 cursor-pointer" 
                         onclick="openChat('{{ $chat->chat_id }}', '{{ $chat->to_number }}', {{ $chat->is_group ? 'true' : 'false' }})">
                        <div class="flex items-start">
                            <div class="w-12 h-12 rounded-full bg-emerald-500 flex items-center justify-center text-white font-semibold mr-3">
                                @if($chat->is_group)
                                    <i class="fas fa-users"></i>
                                @else
                                    {{ substr($chat->to_number, 0, 1) }}
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-baseline">
                                    <h3 class="font-semibold text-gray-900 truncate">
                                        {{ $chat->to_number }}
                                    </h3>
                                    <span class="text-xs text-gray-500 ml-2">
                                        {{ \Carbon\Carbon::parse($chat->last_message_time)->diffForHumans() }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 truncate">
                                    {{ $chat->message_count }} pesan
                                </p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500">
                        <i class="fas fa-comments text-4xl mb-3"></i>
                        <p>Belum ada percakapan</p>
                        <p class="text-sm mt-1">Mulai kirim pesan baru</p>
                    </div>
                @endforelse
            </div>

            <!-- Contacts List -->
            <div id="contacts-list" class="chat-list hidden">
                @forelse($contacts as $contact)
                    <div class="chat-item p-4 border-b border-gray-100 hover:bg-gray-50 cursor-pointer"
                         onclick="openChat('{{ $contact->phone_number }}@c.us', '{{ $contact->phone_number }}', false)">
                        <div class="flex items-center">
                            <div class="w-12 h-12 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold mr-3">
                                {{ substr($contact->name ?? $contact->phone_number, 0, 1) }}
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900">{{ $contact->name ?? 'Unknown' }}</h3>
                                <p class="text-sm text-gray-600">{{ $contact->phone_number }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500">
                        <i class="fas fa-address-book text-4xl mb-3"></i>
                        <p>Belum ada kontak</p>
                        <p class="text-sm mt-1">Sync kontak terlebih dahulu</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Main Chat Area -->
        <div class="flex-1 bg-gray-50 flex flex-col">
            <!-- Empty State -->
            <div id="empty-state" class="flex-1 flex items-center justify-center">
                <div class="text-center">
                    <i class="fas fa-comments text-6xl text-gray-300 mb-4"></i>
                    <h2 class="text-2xl font-semibold text-gray-600 mb-2">WhatsApp Web</h2>
                    <p class="text-gray-500">Pilih chat untuk mulai mengirim pesan</p>
                </div>
            </div>

            <!-- Chat Container -->
            <div id="chat-container" class="flex-1 flex-col hidden">
                <!-- Chat Header -->
                <div class="bg-gray-100 p-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div id="chat-avatar" class="w-10 h-10 rounded-full bg-emerald-500 flex items-center justify-center text-white font-semibold mr-3">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <h2 id="chat-name" class="font-semibold text-gray-900">-</h2>
                                <p id="chat-status" class="text-xs text-gray-600">Pilih chat untuk mulai</p>
                            </div>
                        </div>
                        <button onclick="closeChat()" class="text-gray-600 hover:text-gray-900">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>

                <!-- Messages Area -->
                <div id="messages-area" class="flex-1 p-4 message-area overflow-y-auto" style="background-image: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxkZWZzPjxwYXR0ZXJuIGlkPSJwYXR0ZXJuIiB4PSIwIiB5PSIwIiB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHBhdHRlcm5Vbml0cz0idXNlclNwYWNlT25Vc2UiPjxjaXJjbGUgY3g9IjEiIGN5PSIxIiByPSIxIiBmaWxsPSIjZGRkZGRkMjAiPjwvY2lyY2xlPjwvcGF0dGVybj48L2RlZnM+PHJlY3Qgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgZmlsbD0idXJsKCNwYXR0ZXJuKSI+PC9yZWN0Pjwvc3ZnPg==');">
                    <div class="flex items-center justify-center h-full">
                        <p class="text-gray-500">Memuat pesan...</p>
                    </div>
                </div>

                <!-- Message Input -->
                <div class="bg-gray-100 p-4 border-t border-gray-200">
                    <form id="message-form" class="flex items-center space-x-3">
                        <button type="button" class="text-gray-600 hover:text-gray-900">
                            <i class="fas fa-smile text-2xl"></i>
                        </button>
                        <button type="button" class="text-gray-600 hover:text-gray-900">
                            <i class="fas fa-paperclip text-2xl"></i>
                        </button>
                        <input type="text" id="message-input" placeholder="Ketik pesan" class="flex-1 px-4 py-3 bg-white rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" autocomplete="off">
                        <button type="submit" id="send-button" class="bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-3 rounded-lg transition-colors duration-200">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Current chat state
        let currentChatId = null;
        let currentChatNumber = null;
        let currentIsGroup = false;
        const deviceId = {{ $device->id }};
        const deviceKey = '{{ $device->device_key }}';
        
        // Track chat counts for notifications
        let lastChatCount = 0;
        let originalTitle = document.title;

        // Tab switching
        function switchTab(tab) {
            const chatsTab = document.getElementById('tab-chats');
            const contactsTab = document.getElementById('tab-contacts');
            const chatsList = document.getElementById('chats-list');
            const contactsList = document.getElementById('contacts-list');

            if (tab === 'chats') {
                chatsTab.classList.add('text-emerald-600', 'border-b-2', 'border-emerald-600');
                chatsTab.classList.remove('text-gray-600');
                contactsTab.classList.remove('text-emerald-600', 'border-b-2', 'border-emerald-600');
                contactsTab.classList.add('text-gray-600');
                chatsList.classList.remove('hidden');
                contactsList.classList.add('hidden');
            } else {
                contactsTab.classList.add('text-emerald-600', 'border-b-2', 'border-emerald-600');
                contactsTab.classList.remove('text-gray-600');
                chatsTab.classList.remove('text-emerald-600', 'border-b-2', 'border-emerald-600');
                chatsTab.classList.add('text-gray-600');
                contactsList.classList.remove('hidden');
                chatsList.classList.add('hidden');
            }
        }

        // Open chat
        function openChat(chatId, chatNumber, isGroup) {
            currentChatId = chatId;
            currentChatNumber = chatNumber;
            currentIsGroup = isGroup;

            // Hide empty state
            document.getElementById('empty-state').classList.add('hidden');
            document.getElementById('chat-container').classList.remove('hidden');
            document.getElementById('chat-container').classList.add('flex');

            // Update chat header
            const displayName = chatNumber;
            document.getElementById('chat-name').textContent = displayName;
            document.getElementById('chat-status').textContent = isGroup ? 'Grup' : 'Online';
            
            const avatar = document.getElementById('chat-avatar');
            if (isGroup) {
                avatar.innerHTML = '<i class="fas fa-users"></i>';
            } else {
                avatar.textContent = displayName.substr(0, 1);
            }

            // Load messages
            loadMessages(chatId);
        }

        // Close chat
        function closeChat() {
            currentChatId = null;
            currentChatNumber = null;
            document.getElementById('empty-state').classList.remove('hidden');
            document.getElementById('chat-container').classList.add('hidden');
        }

        // Track last message count to detect new messages
        let lastMessageCount = 0;

        // Load messages for a chat
        async function loadMessages(chatId, isInitial = true) {
            const messagesArea = document.getElementById('messages-area');
            
            if (isInitial) {
                messagesArea.innerHTML = '<div class="flex items-center justify-center h-full"><p class="text-gray-500">Memuat pesan...</p></div>';
            }

            try {
                const response = await fetch(`/whatsapp/devices/${deviceId}/messages/${encodeURIComponent(chatId)}`);
                const data = await response.json();

                if (data.success && data.messages.length > 0) {
                    const hadMessages = messagesArea.querySelector('.message-bubble') !== null;
                    const isScrolledToBottom = messagesArea.scrollHeight - messagesArea.scrollTop <= messagesArea.clientHeight + 100;
                    const newMessageCount = data.messages.length;
                    
                    // Only update if message count changed or it's initial load
                    if (isInitial || newMessageCount !== lastMessageCount) {
                        messagesArea.innerHTML = '';
                        data.messages.forEach(msg => {
                            appendMessage(msg);
                        });
                        
                        // Auto scroll to bottom if:
                        // 1. Initial load
                        // 2. User was already at bottom
                        // 3. New messages arrived
                        if (isInitial || isScrolledToBottom || newMessageCount > lastMessageCount) {
                            scrollToBottom();
                        }
                        
                        lastMessageCount = newMessageCount;
                    }
                } else {
                    messagesArea.innerHTML = '<div class="flex items-center justify-center h-full"><p class="text-gray-500">Belum ada pesan</p></div>';
                    lastMessageCount = 0;
                }
            } catch (error) {
                console.error('Error loading messages:', error);
                if (isInitial) {
                    messagesArea.innerHTML = '<div class="flex items-center justify-center h-full"><p class="text-red-500">Gagal memuat pesan</p></div>';
                }
            }
        }

        // Append message to chat
        function appendMessage(message) {
            const messagesArea = document.getElementById('messages-area');
            
            // Clear "no messages" placeholder if exists
            if (messagesArea.querySelector('.flex.items-center.justify-center')) {
                messagesArea.innerHTML = '';
            }

            const messageDiv = document.createElement('div');
            messageDiv.className = `flex mb-4 ${message.direction === 'outgoing' ? 'justify-end' : 'justify-start'}`;
            
            const bubbleClass = message.direction === 'outgoing' ? 'message-outgoing' : 'message-incoming';
            
            messageDiv.innerHTML = `
                <div class="message-bubble ${bubbleClass} px-4 py-2 rounded-lg shadow">
                    <p class="text-gray-900 break-words">${escapeHtml(message.content)}</p>
                    <div class="flex items-center justify-end mt-1 space-x-1">
                        <span class="text-xs text-gray-500">${formatTime(message.sent_at || message.created_at)}</span>
                        ${message.direction === 'outgoing' ? '<i class="fas fa-check text-xs text-gray-500"></i>' : ''}
                    </div>
                </div>
            `;
            
            messagesArea.appendChild(messageDiv);
        }

        // Send message
        document.getElementById('message-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            if (!currentChatId) return;

            const input = document.getElementById('message-input');
            const message = input.value.trim();
            
            if (!message) return;

            const sendButton = document.getElementById('send-button');
            sendButton.disabled = true;
            sendButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            try {
                const formData = new FormData();
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                formData.append('phone_number', currentChatId);
                formData.append('message', message);

                const response = await fetch(`/whatsapp/devices/${deviceId}/send-message`, {
                    method: 'POST',
                    body: formData
                });

                // Clear input
                input.value = '';

                // Add message to UI optimistically
                const tempMessage = {
                    direction: 'outgoing',
                    content: message,
                    sent_at: new Date().toISOString()
                };
                appendMessage(tempMessage);
                scrollToBottom();

                // Reload messages after a delay to get actual sent message
                setTimeout(() => loadMessages(currentChatId), 1000);

            } catch (error) {
                console.error('Error sending message:', error);
                alert('Gagal mengirim pesan. Silakan coba lagi.');
            } finally {
                sendButton.disabled = false;
                sendButton.innerHTML = '<i class="fas fa-paper-plane"></i>';
            }
        });

        // Search chat
        document.getElementById('searchChat').addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase();
            const activeList = document.getElementById('tab-chats').classList.contains('text-emerald-600') 
                ? 'chats-list' 
                : 'contacts-list';
            
            const items = document.querySelectorAll(`#${activeList} .chat-item`);
            items.forEach(item => {
                const text = item.textContent.toLowerCase();
                item.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // Utility functions
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function formatTime(timestamp) {
            const date = new Date(timestamp);
            return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        }

        function scrollToBottom() {
            const messagesArea = document.getElementById('messages-area');
            messagesArea.scrollTop = messagesArea.scrollHeight;
        }

        // Refresh chat list
        async function refreshChatList() {
            try {
                const response = await fetch(`/whatsapp/devices/${deviceId}/recent-chats`);
                const data = await response.json();

                if (data.success && data.chats) {
                    const chatsList = document.getElementById('chats-list');
                    
                    // Detect new chats
                    const currentChatCount = data.chats.length;
                    if (lastChatCount > 0 && currentChatCount > lastChatCount) {
                        // New chat detected - show notification in title
                        document.title = '🔔 Pesan Baru - ' + originalTitle;
                        setTimeout(() => {
                            document.title = originalTitle;
                        }, 3000);
                    }
                    lastChatCount = currentChatCount;
                    
                    // Save current scroll position
                    const scrollPos = chatsList.scrollTop;
                    
                    // Update chat list HTML
                    if (data.chats.length > 0) {
                        chatsList.innerHTML = '';
                        data.chats.forEach(chat => {
                            const chatItem = document.createElement('div');
                            chatItem.className = 'chat-item p-4 border-b border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors';
                            chatItem.onclick = () => openChat(chat.chat_id, chat.to_number, chat.is_group);
                            
                            // Highlight if current chat
                            if (currentChatId === chat.chat_id) {
                                chatItem.classList.add('bg-gray-100');
                            }
                            
                            const avatarContent = chat.is_group 
                                ? '<i class="fas fa-users"></i>' 
                                : chat.to_number.substr(0, 1);
                            
                            chatItem.innerHTML = `
                                <div class="flex items-start">
                                    <div class="w-12 h-12 rounded-full bg-emerald-500 flex items-center justify-center text-white font-semibold mr-3">
                                        ${avatarContent}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex justify-between items-baseline">
                                            <h3 class="font-semibold text-gray-900 truncate">
                                                ${escapeHtml(chat.to_number)}
                                            </h3>
                                            <span class="text-xs text-gray-500 ml-2 whitespace-nowrap">
                                                ${chat.last_message_time_formatted}
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-600 truncate">
                                            ${chat.last_message_preview ? escapeHtml(chat.last_message_preview) : chat.message_count + ' pesan'}
                                        </p>
                                    </div>
                                </div>
                            `;
                            
                            chatsList.appendChild(chatItem);
                        });
                    } else {
                        chatsList.innerHTML = `
                            <div class="p-8 text-center text-gray-500">
                                <i class="fas fa-comments text-4xl mb-3"></i>
                                <p>Belum ada percakapan</p>
                                <p class="text-sm mt-1">Mulai kirim pesan baru</p>
                            </div>
                        `;
                    }
                    
                    // Restore scroll position
                    chatsList.scrollTop = scrollPos;
                }
            } catch (error) {
                console.error('Error refreshing chat list:', error);
            }
        }

        // Auto-refresh messages every 5 seconds if chat is open
        setInterval(() => {
            if (currentChatId) {
                loadMessages(currentChatId, false); // false = not initial load, preserve scroll position
            }
        }, 5000);

        // Auto-refresh chat list every 3 seconds
        setInterval(() => {
            refreshChatList();
        }, 3000);

        // Initial chat list load
        setTimeout(() => {
            refreshChatList();
        }, 1000);
    </script>
</body>
</html>

