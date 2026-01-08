<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('whatsapp.devices.index') }}" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to Devices
                        </a>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 flex items-center space-x-2">
                                <i class="fas fa-mobile-alt text-green-600"></i>
                                <span>{{ $device->device_name }}</span>
                            </h1>
                            @if($device->phone_number)
                                <p class="mt-2 text-gray-600">{{ $device->phone_number }}</p>
                            @elseif($device->isConnected())
                                <p class="mt-2 text-orange-600">
                                    <i class="fas fa-check-circle"></i> Connected - Phone number unavailable
                                </p>
                            @else
                                <p class="mt-2 text-gray-600">Device not connected yet</p>
                            @endif
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        {!! $device->status_badge !!}
                        
                        <!-- Edit Button -->
                        <a href="{{ route('whatsapp.devices.edit', $device) }}" class="inline-flex items-center px-3 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <i class="fas fa-edit mr-2"></i>
                            Edit Device
                        </a>
                    </div>
                </div>
            </div>

            <!-- Real Mode Notice -->
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <div>
                    <strong>Real WhatsApp Mode:</strong> Terhubung ke server WhatsApp real di port 3001. 
                    QR code yang ditampilkan adalah asli dari WhatsApp Web API dan dapat digunakan untuk koneksi real.
                </div>
            </div>

            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column - Device Info & QR Code -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- QR Code Section -->
                    @if($device->isDisconnected() || $device->isConnecting())
                        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                            <div class="px-6 py-4 bg-gradient-to-r from-green-50 to-green-100 border-b border-green-200">
                                <h3 class="text-lg font-semibold text-green-800 flex items-center">
                                    <i class="fas fa-qrcode mr-2"></i>
                                    WhatsApp QR Code
                                </h3>
                            </div>
                            <div class="p-6">
                                <div class="text-center">
                                    <div class="mb-6" id="qr-container">
                                        <div id="qr-loading" class="hidden">
                                            <div class="mx-auto w-64 h-64 border-4 border-gray-200 rounded-lg shadow-lg flex items-center justify-center">
                                                <div class="text-center">
                                                    <i class="fas fa-spinner fa-spin text-4xl text-gray-400 mb-4"></i>
                                                    <p class="text-gray-500">Generating QR Code...</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="qr-image-container">
                                            <img id="qr-image" src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjU2IiBoZWlnaHQ9IjI1NiIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjU2IiBoZWlnaHQ9IjI1NiIgZmlsbD0iI2YzZjRmNiIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBkb21pbmFudC1iYXNlbGluZT0ibWlkZGxlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBmb250LWZhbWlseT0ic2Fucy1zZXJpZiIgZm9udC1zaXplPSIxNHB4IiBmaWxsPSIjNmI3MjgwIj5Waiting for QR Code</text></svg>" alt="WhatsApp QR Code" class="mx-auto border-4 border-green-200 rounded-lg shadow-lg">
                                        </div>
                                        <div id="qr-error" class="hidden">
                                            <div class="mx-auto w-64 h-64 border-4 border-red-200 rounded-lg shadow-lg flex items-center justify-center bg-red-50">
                                                <div class="text-center px-4">
                                                    <i class="fas fa-exclamation-triangle text-4xl text-red-400 mb-4"></i>
                                                    <p class="text-red-500 text-sm mb-2">Failed to load QR Code</p>
                                                    <button onclick="refreshQR()" class="mt-2 text-sm text-blue-600 hover:text-blue-800 underline">Try Again</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <h4 class="text-lg font-medium text-gray-900 mb-2">Scan QR Code with WhatsApp</h4>
                                    <div class="text-gray-600 mb-4">
                                        <p class="mb-2"><strong>Langkah-langkah:</strong></p>
                                        <ol class="text-sm text-left max-w-md mx-auto space-y-1">
                                            <li>1. Pastikan device sudah di-connect (klik "Connect & Generate QR")</li>
                                            <li>2. Buka WhatsApp di HP Anda</li>
                                            <li>3. Pergi ke <strong>Menu → Linked Devices</strong></li>
                                            <li>4. Klik <strong>"Link a Device"</strong></li>
                                            <li>5. Scan QR code di atas</li>
                                        </ol>
                                    </div>
                                    
                                    <!-- Auto-refresh QR -->
                                    <div class="bg-blue-50 rounded-lg p-4 mb-4">
                                        <div class="flex items-center justify-center text-blue-700">
                                            <i class="fas fa-sync-alt mr-2" id="qr-refresh-icon"></i>
                                            <span id="qr-timer">QR code expires in <span id="timer-countdown">5:00</span></span>
                                        </div>
                                    </div>

                                    <!-- Connection buttons -->
                                    <div class="flex justify-center space-x-3">
                                        @if($device->isDisconnected())
                                            <form method="POST" action="{{ route('whatsapp.devices.connect', $device) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                                    <i class="fas fa-link mr-2"></i>
                                                    Connect & Generate QR
                                                </button>
                                            </form>
                                        @else
                                            <button onclick="refreshQR()" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                                <i class="fas fa-sync-alt mr-2"></i>
                                                Refresh QR
                                            </button>
                                        @endif
                                        
                                        <button onclick="checkStatus()" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            <i class="fas fa-search mr-2"></i>
                                            Check Status
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Device Statistics -->
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                        <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                                <i class="fas fa-chart-bar mr-2 text-indigo-600"></i>
                                Device Statistics
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-blue-600">{{ $device->messages()->count() }}</div>
                                    <div class="text-sm text-gray-500">Total Messages</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-green-600">{{ $device->messages()->where('direction', 'outgoing')->count() }}</div>
                                    <div class="text-sm text-gray-500">Sent</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-purple-600">{{ $device->messages()->where('direction', 'incoming')->count() }}</div>
                                    <div class="text-sm text-gray-500">Received</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-orange-600">{{ $device->contacts()->count() }}</div>
                                    <div class="text-sm text-gray-500">Contacts</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Messages -->
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                        <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                                <i class="fas fa-comments mr-2 text-blue-600"></i>
                                Recent Messages
                            </h3>
                        </div>
                        <div class="p-6">
                            @if($device->messages->count() > 0)
                                <div class="space-y-4">
                                    @foreach($device->messages->take(5) as $message)
                                        <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg">
                                            <div class="flex-shrink-0">
                                                @if($message->direction === 'incoming')
                                                    <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
                                                        <i class="fas fa-arrow-down text-green-600 text-sm"></i>
                                                    </div>
                                                @else
                                                    <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                                        <i class="fas fa-arrow-up text-blue-600 text-sm"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center justify-between">
                                                    <p class="text-sm font-medium text-gray-900">
                                                        {{ $message->direction === 'incoming' ? $message->from_number : $message->to_number }}
                                                    </p>
                                                    <p class="text-xs text-gray-500">{{ $message->created_at->diffForHumans() }}</p>
                                                </div>
                                                <p class="text-sm text-gray-600 mt-1">{{ Str::limit($message->content, 100) }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <i class="fas fa-comments text-gray-300 text-4xl mb-4"></i>
                                    <p class="text-gray-500">No messages yet</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right Column - Device Details -->
                <div class="space-y-6">
                    <!-- Device Information -->
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                        <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                                <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                                Device Information
                            </h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <label class="text-sm font-medium text-gray-500">Device Name</label>
                                <p class="text-gray-900">{{ $device->device_name }}</p>
                            </div>
                            
                            <div>
                                <label class="text-sm font-medium text-gray-500">Device Key</label>
                                <div class="flex items-center space-x-2">
                                    <code class="text-xs bg-gray-100 px-2 py-1 rounded flex-1">{{ $device->device_key }}</code>
                                    <button onclick="copyToClipboard('{{ $device->device_key }}')" class="text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div>
                                <label class="text-sm font-medium text-gray-500">Phone Number</label>
                                <p class="text-gray-900">{{ $device->phone_number ?: 'Not connected' }}</p>
                            </div>
                            
                            <div>
                                <label class="text-sm font-medium text-gray-500">Status</label>
                                <p class="text-gray-900">{{ ucfirst($device->status) }}</p>
                            </div>
                            
                            <div>
                                <label class="text-sm font-medium text-gray-500">Last Seen</label>
                                <p class="text-gray-900">{{ $device->last_seen_formatted }}</p>
                            </div>
                            
                            @if($device->connected_at)
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Connected At</label>
                                    <p class="text-gray-900">{{ $device->connected_at->format('d M Y H:i') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Webhook Configuration -->
                    @if($device->webhook_config && isset($device->webhook_config['url']))
                        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                            <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-blue-100 border-b border-blue-200">
                                <h3 class="text-lg font-semibold text-blue-800 flex items-center">
                                    <i class="fas fa-webhook mr-2"></i>
                                    Webhook Configuration
                                </h3>
                            </div>
                            <div class="p-6 space-y-4">
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Webhook URL</label>
                                    <p class="text-sm text-gray-900 break-all">{{ $device->webhook_config['url'] }}</p>
                                </div>
                                
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Events</label>
                                    <div class="flex flex-wrap gap-2 mt-1">
                                        @foreach($device->webhook_config['events'] ?? ['all'] as $event)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ ucfirst($event) }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Connection Actions -->
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                        <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                                <i class="fas fa-cogs mr-2 text-gray-600"></i>
                                Device Actions
                            </h3>
                        </div>
                        <div class="p-6 space-y-3">
                            @if($device->isConnected())
                                <form method="POST" action="{{ route('whatsapp.devices.disconnect', $device) }}">
                                    @csrf
                                    <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        <i class="fas fa-unlink mr-2"></i>
                                        Disconnect Device
                                    </button>
                                </form>
                            @elseif($device->isDisconnected())
                                <form method="POST" action="{{ route('whatsapp.devices.connect', $device) }}">
                                    @csrf
                                    <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        <i class="fas fa-link mr-2"></i>
                                        Connect Device
                                    </button>
                                </form>
                            @endif
                            
                            <a href="{{ route('whatsapp.devices.edit', $device) }}" class="w-full inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <i class="fas fa-edit mr-2"></i>
                                Edit Device
                            </a>
                            
                            <form method="POST" action="{{ route('whatsapp.devices.destroy', $device) }}" onsubmit="return confirm('Are you sure you want to delete this device?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <i class="fas fa-trash mr-2"></i>
                                    Delete Device
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Socket.IO CDN -->
    <script src="https://cdn.socket.io/4.7.4/socket.io.min.js"></script>
    
    <!-- JavaScript for real WhatsApp functionality -->
    <script>
        let countdownTimer;
        let timeLeft = 300; // 5 minutes
        let socket;
        let isConnected = false;
        const deviceKey = '{{ $device->device_key }}';

        // Initialize Socket.IO connection
        function initializeSocket() {
            try {
                // Detect Socket.IO server URL (use same host as current page)
                const socketUrl = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1' 
                    ? 'http://localhost:3001' 
                    : `http://${window.location.hostname}:3001`;
                
                console.log('Connecting to Socket.IO server:', socketUrl);
                
                socket = io(socketUrl, {
                    timeout: 10000,
                    transports: ['websocket', 'polling'],
                    reconnection: true,
                    reconnectionDelay: 1000,
                    reconnectionAttempts: 5
                });

                socket.on('connect', function() {
                    console.log('Connected to WhatsApp server');
                    socket.emit('join-device', deviceKey);
                    showConnectionStatus('Connected to WhatsApp server', 'success');
                });

                socket.on('disconnect', function() {
                    console.log('Disconnected from WhatsApp server');
                    showConnectionStatus('Disconnected from WhatsApp server', 'warning');
                });

                socket.on('connect_error', function(error) {
                    console.error('Connection error:', error);
                    showConnectionStatus('Cannot connect to WhatsApp server', 'error');
                });

                // Listen for QR code updates
                socket.on(`qr-${deviceKey}`, function(data) {
                    console.log('QR code received:', data);
                    displayQRCode(data.qr);
                    startCountdown();
                });

                // Listen for connection status updates
                socket.on(`status-${deviceKey}`, function(data) {
                    console.log('Status update via Socket.IO:', data);
                    updateDeviceStatus(data);
                    
                    // If status is connected, reload page
                    if (data.status === 'connected') {
                        console.log('Device connected via Socket.IO! Reloading...');
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    }
                });

                // Listen for successful connection
                socket.on(`connected-${deviceKey}`, function(data) {
                    console.log('Device connected:', data);
                    showConnectionSuccess(data);
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                });

                // Listen for disconnection
                socket.on(`disconnected-${deviceKey}`, function(data) {
                    console.log('Device disconnected:', data);
                    updateDeviceStatus({
                        status: 'disconnected',
                        message: data.reason || 'Device disconnected'
                    });
                });

                // Listen for incoming messages
                socket.on(`message-${deviceKey}`, function(data) {
                    console.log('Message received:', data);
                    // You can add message notification here
                });

            } catch (error) {
                console.error('Socket initialization error:', error);
                showConnectionStatus('Failed to initialize connection', 'error');
            }
        }

        function showConnectionStatus(message, type) {
            // You can implement a toast notification here
            console.log(`[${type.toUpperCase()}] ${message}`);
        }

        function showConnectionSuccess(data) {
            const container = document.getElementById('qr-container');
            container.innerHTML = `
                <div class="mx-auto w-64 h-64 border-4 border-green-200 rounded-lg shadow-lg flex items-center justify-center bg-green-50">
                    <div class="text-center">
                        <i class="fas fa-check-circle text-5xl text-green-500 mb-4"></i>
                        <h3 class="text-lg font-medium text-green-800 mb-2">Connected!</h3>
                        <p class="text-sm text-green-600">Phone: ${data.phoneNumber}</p>
                        <p class="text-sm text-green-600">Name: ${data.pushname}</p>
                        <p class="text-xs text-green-500 mt-2">Reloading page...</p>
                    </div>
                </div>
            `;
        }

        function displayQRCode(qrData) {
            const qrImageUrl = `https://api.qrserver.com/v1/create-qr-code/?size=256x256&data=${encodeURIComponent(qrData)}`;
            
            document.getElementById('qr-loading').classList.add('hidden');
            document.getElementById('qr-error').classList.add('hidden');
            document.getElementById('qr-image-container').classList.remove('hidden');
            
            const qrImage = document.getElementById('qr-image');
            qrImage.src = qrImageUrl;
        }

        function showQRLoading() {
            document.getElementById('qr-loading').classList.remove('hidden');
            document.getElementById('qr-error').classList.add('hidden');
            document.getElementById('qr-image-container').classList.add('hidden');
        }

        function showQRError(message = 'Failed to load QR Code') {
            document.getElementById('qr-loading').classList.add('hidden');
            document.getElementById('qr-error').classList.remove('hidden');
            document.getElementById('qr-image-container').classList.add('hidden');
            
            // Update error message if provided
            const errorDiv = document.getElementById('qr-error');
            if (errorDiv && message !== 'Failed to load QR Code') {
                const errorText = errorDiv.querySelector('p.text-red-500');
                if (errorText) {
                    errorText.textContent = message;
                }
            }
        }

        function updateDeviceStatus(data) {
            // Update status badge if exists
            const statusElement = document.querySelector('.status-badge');
            if (statusElement) {
                // Update status display
                console.log('Status updated:', data.status, data.message);
            }
            
            // If status is connected, show notification and reload
            if (data.status === 'connected') {
                console.log('Device connected detected! Phone:', data.phone_number || data.deviceInfo?.phone);
                
                // Show success message
                const container = document.getElementById('qr-container');
                if (container) {
                    container.innerHTML = `
                        <div class="mx-auto w-64 h-64 border-4 border-green-200 rounded-lg shadow-lg flex items-center justify-center bg-green-50">
                            <div class="text-center">
                                <i class="fas fa-check-circle text-5xl text-green-500 mb-4"></i>
                                <h3 class="text-lg font-medium text-green-800 mb-2">Connected!</h3>
                                <p class="text-sm text-green-600">Phone: ${data.phone_number || data.deviceInfo?.phone || 'N/A'}</p>
                                <p class="text-xs text-green-500 mt-2">Reloading page...</p>
                            </div>
                        </div>
                    `;
                }
                
                // Reload page after short delay
                setTimeout(() => {
                    location.reload();
                }, 1500);
            }
        }

        function startCountdown() {
            clearInterval(countdownTimer);
            timeLeft = 300;
            
            countdownTimer = setInterval(function() {
                timeLeft--;
                updateTimerDisplay();
                
                if (timeLeft <= 0) {
                    clearInterval(countdownTimer);
                    refreshQR();
                }
            }, 1000);
        }

        function updateTimerDisplay() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            const countdown = document.getElementById('timer-countdown');
            if (countdown) {
                countdown.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
            }
        }

        function refreshQR() {
            const icon = document.getElementById('qr-refresh-icon');
            if (icon) icon.classList.add('fa-spin');
            
            showQRLoading();
            
            // First ensure device is connected to WhatsApp server
            connectDeviceIfNeeded().then(() => {
                // Wait a bit for QR code to be generated
                setTimeout(() => {
                    // Then fetch QR code
                    fetch('{{ route("whatsapp.devices.qr-code", $device) }}')
                        .then(response => response.json())
                        .then(data => {
                            if (data.success && data.qr_code) {
                                displayQRCode(data.qr_code.code);
                                startCountdown();
                            } else {
                                showQRError(data.message || 'Gagal memuat QR code');
                                console.error('QR Error:', data.message);
                                
                                // If device needs to be connected, show helpful message
                                if (data.needs_connect && data.error_type === 'device_not_connected') {
                                    showQRConnectionHint();
                                }
                            }
                        })
                        .catch(error => {
                            console.error('QR Fetch Error:', error);
                            showQRError('Terjadi kesalahan saat mengambil QR code. Pastikan WhatsApp server berjalan.');
                        })
                        .finally(() => {
                            if (icon) icon.classList.remove('fa-spin');
                        });
                }, 2000); // Wait 2 seconds for QR generation
            }).catch(error => {
                console.error('Connect Error:', error);
                showQRError('Gagal menghubungkan device. Pastikan WhatsApp server berjalan di port 3001.');
                if (icon) icon.classList.remove('fa-spin');
            });
        }
        
        function showQRConnectionHint() {
            const errorDiv = document.getElementById('qr-error');
            if (errorDiv) {
                const hint = document.createElement('div');
                hint.className = 'mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg';
                hint.innerHTML = `
                    <p class="text-sm text-yellow-800 mb-2">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Tips:</strong> Klik tombol "Connect & Generate QR" di bawah untuk memulai koneksi.
                    </p>
                `;
                errorDiv.appendChild(hint);
            }
        }

        function connectDeviceIfNeeded() {
            return new Promise((resolve, reject) => {
                // Check if device needs to be connected
                fetch('{{ route("whatsapp.devices.status", $device) }}')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && (data.status === 'connecting' || data.status === 'connected')) {
                            // Already connecting or connected
                            resolve();
                        } else {
                            // Need to initiate connection
                            console.log('Initiating device connection...');
                            fetch('{{ route("whatsapp.devices.connect", $device) }}', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                redirect: 'manual'
                            })
                            .then(response => {
                                // Connection request made, wait for QR generation
                                console.log('Connection request sent, waiting for QR generation...');
                                setTimeout(() => resolve(), 3000);
                            })
                            .catch(error => {
                                console.error('Failed to connect device:', error);
                                reject(error);
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Failed to check device status:', error);
                        reject(error);
                    });
            });
        }

        function checkStatus() {
            const statusButton = document.querySelector('button[onclick="checkStatus()"]');
            if (statusButton) {
                const icon = statusButton.querySelector('i');
                if (icon) icon.classList.add('fa-spin');
            }
            
            fetch('{{ route("whatsapp.devices.status", $device) }}')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Status check result:', data);
                        updateDeviceStatus(data);
                        
                        // If connected, reload page immediately
                        if (data.status === 'connected') {
                            console.log('Device is connected! Reloading page...');
                            location.reload();
                        } else if (data.status === 'connecting') {
                            // Still connecting, check again soon
                            console.log('Device still connecting...');
                        }
                    } else {
                        console.error('Status check failed:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Status Error:', error);
                })
                .finally(() => {
                    if (statusButton) {
                        const icon = statusButton.querySelector('i');
                        if (icon) icon.classList.remove('fa-spin');
                    }
                });
        }

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                alert('Device key copied to clipboard!');
            });
        }

        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', function() {
            @if($device->isDisconnected() || $device->isConnecting())
                // Initialize real-time connection with Socket.IO
                console.log('Running in Real Mode - Initializing Socket.IO');
                initializeSocket();
                
                // Wait for socket connection then get QR code
                setTimeout(() => {
                    // If device is disconnected, try to connect first
                    if ('{{ $device->status }}' === 'disconnected') {
                        console.log('Device is disconnected, attempting to connect...');
                        connectDeviceIfNeeded().then(() => {
                            // Wait a bit longer for QR generation after connection
                            setTimeout(() => {
                                refreshQR();
                            }, 3000);
                        }).catch(error => {
                            console.error('Auto-connect failed:', error);
                            // Still try to refresh QR in case it's already available
                            setTimeout(() => {
                                refreshQR();
                            }, 2000);
                        });
                    } else {
                        // Device is already connecting, just refresh QR
                        refreshQR();
                    }
                }, 1000);
                
                // Auto-check status every 5 seconds (more aggressive polling)
                // This is important as fallback if Socket.IO doesn't work
                setInterval(checkStatus, 5000);
                
                // Also check status immediately
                setTimeout(checkStatus, 2000);
            @endif
        });

        // Cleanup on page unload
        window.addEventListener('beforeunload', function() {
            if (socket) {
                socket.disconnect();
            }
            if (countdownTimer) {
                clearInterval(countdownTimer);
            }
        });
    </script>
</x-app-layout> 