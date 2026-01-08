<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('whatsapp.devices.index') }}" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to Devices
                        </a>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 flex items-center space-x-2">
                                <i class="fas fa-info-circle text-indigo-600"></i>
                                <span>API Information</span>
                            </h1>
                            <p class="mt-2 text-gray-600">{{ $device->device_name }} - API endpoints dan script Mikrotik</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-8">
                <!-- API Credentials -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-indigo-100 border-b border-indigo-200">
                        <h3 class="text-lg font-semibold text-indigo-800 flex items-center">
                            <i class="fas fa-key mr-2"></i>
                            API Credentials
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">API Key</label>
                                <div class="flex items-center space-x-2">
                                    <input type="text" value="{{ $apiKey }}" readonly class="flex-1 px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-sm font-mono">
                                    <button onclick="copyToClipboard('{{ $apiKey }}')" class="px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors duration-200">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Device Phone</label>
                                <div class="flex items-center space-x-2">
                                    @php
                                        $phoneDisplay = $device->phone_number 
                                            ? $device->phone_number 
                                            : ($device->isConnected() 
                                                ? 'Phone number unavailable (WhatsApp API limitation)' 
                                                : 'Device not connected');
                                    @endphp
                                    <input type="text" value="{{ $phoneDisplay }}" readonly class="flex-1 px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-sm {{ $device->isConnected() && !$device->phone_number ? 'text-orange-600' : '' }}">
                                    @if($device->phone_number)
                                        <button onclick="copyToClipboard('{{ $device->phone_number }}')" class="px-3 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors duration-200">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    @elseif($device->isConnected())
                                        <span class="px-3 py-2 bg-orange-100 text-orange-700 rounded-md text-sm">
                                            <i class="fas fa-info-circle"></i> Connected
                                        </span>
                                    @endif
                                </div>
                                @if($device->isConnected() && !$device->phone_number)
                                    <p class="mt-1 text-xs text-orange-600">
                                        <i class="fas fa-exclamation-triangle"></i> 
                                        Device is connected and functional, but phone number is not available due to WhatsApp Web API limitations. You can still send and receive messages.
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- API Endpoints -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-green-50 to-green-100 border-b border-green-200">
                        <h3 class="text-lg font-semibold text-green-800 flex items-center">
                            <i class="fas fa-globe mr-2"></i>
                            API Endpoints
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Send Message Endpoint (MPWA Compatible)</label>
                            <div class="space-y-2">
                                <!-- Primary endpoint -->
                                <div class="flex items-center space-x-2">
                                    <input type="text" value="{{ $baseUrl }}/send-message" readonly class="flex-1 px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-sm font-mono">
                                    <button onclick="copyToClipboard('{{ $baseUrl }}/send-message')" class="px-3 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors duration-200">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                                <!-- Alternative endpoint -->
                                <div class="flex items-center space-x-2">
                                    <input type="text" value="{{ $baseUrl }}/api/send-message" readonly class="flex-1 px-3 py-2 border border-gray-300 rounded-md bg-gray-100 text-sm font-mono">
                                    <button onclick="copyToClipboard('{{ $baseUrl }}/api/send-message')" class="px-3 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors duration-200">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="font-medium text-gray-900 mb-2">Parameters (MPWA Compatible):</h4>
                            <ul class="text-sm text-gray-700 space-y-1">
                                <li><code class="bg-gray-200 px-1 rounded">api_key</code> - API key device Anda</li>
                                <li><code class="bg-gray-200 px-1 rounded">sender</code> - Nomor pengirim ({{ $device->phone_number ?: 'device_phone' }})</li>
                                <li><code class="bg-gray-200 px-1 rounded">number</code> - Nomor tujuan (62812xxx atau 120363xxx@g.us untuk grup)</li>
                                <li><code class="bg-gray-200 px-1 rounded">message</code> - Pesan yang akan dikirim</li>
                            </ul>
                            
                            <div class="mt-3 p-3 bg-blue-50 rounded border-l-4 border-blue-400">
                                <p class="text-sm text-blue-700">
                                    <strong>Method:</strong> POST | GET (100% MPWA Compatible)<br>
                                    <strong>POST Format:</strong> JSON Request Body<br>
                                    <strong>GET Format:</strong> URL Parameters<br>
                                    <strong>Phone Format:</strong> 72888xxxx | 62888xxxx | 0888xxxx
                                </p>
                            </div>
                            
                            <!-- Example Requests -->
                            <div class="mt-4 space-y-4">
                                <div>
                                    <h5 class="font-medium text-gray-900 mb-2">Example JSON Request (POST):</h5>
                                    <pre class="bg-gray-800 text-green-400 p-3 rounded text-sm overflow-x-auto"><code>{
  "api_key": "{{ $apiKey }}",
  "sender": "{{ $device->phone_number ?: '62888xxxx' }}",
  "number": "62888xxxx",
  "message": "Hello World"
}</code></pre>
                                </div>
                                
                                <div>
                                    <h5 class="font-medium text-gray-900 mb-2">Example URL Request (GET):</h5>
                                    <pre class="bg-gray-800 text-green-400 p-3 rounded text-sm overflow-x-auto"><code>{{ $baseUrl }}/send-message?api_key={{ $apiKey }}&sender={{ $device->phone_number ?: '62888xxxx' }}&number=62888xxxx&message=Hello World</code></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mikrotik Scripts -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-red-50 to-red-100 border-b border-red-200">
                        <h3 class="text-lg font-semibold text-red-800 flex items-center">
                            <i class="fab fa-raspberry-pi mr-2"></i>
                            Mikrotik PPP Scripts
                        </h3>
                    </div>
                    <div class="p-6 space-y-6">
                        <!-- Login Script -->
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="font-semibold text-gray-900">PPP On-Login Script</h4>
                                <button onclick="copyScript('loginScript')" class="px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700 transition-colors duration-200">
                                    <i class="fas fa-copy mr-1"></i> Copy
                                </button>
                            </div>
                            <pre id="loginScript" class="bg-gray-900 text-green-400 p-4 rounded-lg text-sm overflow-x-auto"><code>:local nama "$user";
:local profile [/ppp secret get [find name=$nama] profile];
:local datetime "Tanggal:%20$[/system clock get date]%0AJam:%20$[/system clock get time]"
:local active [/ppp active print count];
:local totalSecret [/ppp secret print count]; 
:local inactive ($totalSecret - $active);

# Membuat daftar pengguna yang tidak aktif
:local inactiveUsers "";
:local i 0;

# Ambil semua secret
:foreach secret in=[/ppp secret find] do={
    :local secretName [/ppp secret get $secret name];
    :local isActive false;
    
    # Cek apakah secret ini aktif
    :foreach activeConn in=[/ppp active find] do={
        :local activeName [/ppp active get $activeConn name];
        :if ($activeName = $secretName) do={
            :set isActive true;
        }
    }
    
    # Jika tidak aktif, tambahkan ke daftar
    :if ($isActive = false) do={
        :set i ($i + 1);
        # Batasi hanya menampilkan 2000 pengguna untuk menghindari pesan terlalu panjang
        :if ($i <= 2000) do={
            :set inactiveUsers ($inactiveUsers . "%0A" . $i . ".%20" . $secretName);
        }
    }
}

# Jika jumlah tidak aktif > 2000, tambahkan pesan "dan lainnya"
:if ($inactive > 2000) do={
    :set inactiveUsers ($inactiveUsers . "%0Adan%20" . ($inactive - 10) . "%20lainnya...");
}

# Buat pesan notifikasi
:local message "*{{ str_replace($device->device_name, ' ', '%20') }}%E2%9C%85TERHUBUNG*%0ANama%20%3A%20$user%0APaket%20%3A%20$profile%0A$datetime%0ATotal%20Aktif%20%3A%20*$active*%0ATotal%20Tidak%20Aktif%20%3A%20*$inactive*";

# Tambahkan daftar pengguna tidak aktif jika ada
:if ($inactive > 0) do={
    :set message ($message . "%0A%0A*DAFTAR%20USER%20TIDAK%20AKTIF:*" . $inactiveUsers);
}

/tool fetch url="{{ $baseUrl }}/send-message?api_key={{ $apiKey }}&sender={{ $device->phone_number ?: 'YOUR_DEVICE_PHONE' }}&number=YOUR_GROUP_OR_NUMBER&message=$message" keep-result=no;</code></pre>
                        </div>

                        <!-- Logout Script -->
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="font-semibold text-gray-900">PPP On-Logout Script</h4>
                                <button onclick="copyScript('logoutScript')" class="px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700 transition-colors duration-200">
                                    <i class="fas fa-copy mr-1"></i> Copy
                                </button>
                            </div>
                            <pre id="logoutScript" class="bg-gray-900 text-green-400 p-4 rounded-lg text-sm overflow-x-auto"><code>:local nama "$user";
:local profile [/ppp secret get [find name=$nama] profile];
:local datetime "Tanggal:%20$[/system clock get date]%0AJam:%20$[/system clock get time]"
:local active [/ppp active print count];
:local totalSecret [/ppp secret print count];
:local inactive ($totalSecret - $active);

/tool fetch url="{{ $baseUrl }}/send-message?api_key={{ $apiKey }}&sender={{ $device->phone_number ?: 'YOUR_DEVICE_PHONE' }}&number=YOUR_GROUP_OR_NUMBER&message=*{{ str_replace($device->device_name, ' ', '%20') }}%E2%9D%8CTERPUTUS*%0ANama%20%3A%20$user%0APaket%20%3A%20$profile%0A$datetime%0ATotal%20Aktif%20%3A%20*$active*%0ATotal%20Tidak%20Aktif%20%3A%20*$inactive*" keep-result=no;</code></pre>
                        </div>

                        <!-- Setup Instructions -->
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <h4 class="font-semibold text-yellow-800 mb-2 flex items-center">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                Setup Instructions
                            </h4>
                            <div class="text-sm text-yellow-700 space-y-2">
                                <p><strong>1. Replace Variables:</strong></p>
                                <ul class="ml-4 space-y-1">
                                    <li>• <code class="bg-yellow-200 px-1 rounded">YOUR_GROUP_OR_NUMBER</code> - Ganti dengan nomor tujuan atau group ID</li>
                                    @if(!$device->phone_number)
                                        <li>• <code class="bg-yellow-200 px-1 rounded">YOUR_DEVICE_PHONE</code> - Ganti dengan nomor device WhatsApp yang connected</li>
                                    @endif
                                </ul>
                                
                                <p><strong>2. Mikrotik Setup:</strong></p>
                                <ul class="ml-4 space-y-1">
                                    <li>• Copy script login ke: <code class="bg-yellow-200 px-1 rounded">PPP → Profiles → [Your Profile] → Scripts → On Login</code></li>
                                    <li>• Copy script logout ke: <code class="bg-yellow-200 px-1 rounded">PPP → Profiles → [Your Profile] → Scripts → On Logout</code></li>
                                </ul>
                                
                                <p><strong>3. Contoh Group ID:</strong> <code class="bg-yellow-200 px-1 rounded">120363403455212235@g.us</code></p>
                                <p><strong>4. Contoh Nomor:</strong> <code class="bg-yellow-200 px-1 rounded">6281234567890</code></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                // Visual feedback
                const button = event.target.closest('button');
                const originalHTML = button.innerHTML;
                button.innerHTML = '<i class="fas fa-check"></i>';
                setTimeout(() => {
                    button.innerHTML = originalHTML;
                }, 1500);
                
                // Show notification
                showNotification('Copied to clipboard!', 'success');
            }).catch(function(err) {
                showNotification('Failed to copy!', 'error');
            });
        }

        function copyScript(scriptId) {
            const script = document.getElementById(scriptId);
            const text = script.textContent;
            
            navigator.clipboard.writeText(text).then(function() {
                // Visual feedback
                const button = event.target.closest('button');
                const originalHTML = button.innerHTML;
                button.innerHTML = '<i class="fas fa-check mr-1"></i> Copied!';
                setTimeout(() => {
                    button.innerHTML = originalHTML;
                }, 2000);
                
                showNotification('Script copied to clipboard!', 'success');
            }).catch(function(err) {
                showNotification('Failed to copy script!', 'error');
            });
        }

        function showNotification(message, type) {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 px-4 py-2 rounded-lg text-white text-sm font-medium transition-all duration-300 transform translate-x-full`;
            notification.style.backgroundColor = type === 'success' ? '#10B981' : '#EF4444';
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            // Animate in
            setTimeout(() => {
                notification.style.transform = 'translateX(0)';
            }, 100);
            
            // Remove after 3 seconds
            setTimeout(() => {
                notification.style.transform = 'translateX(full)';
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }
    </script>
</x-app-layout> 