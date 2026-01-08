<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 flex items-center space-x-2">
                            <i class="fas fa-mobile-alt text-green-600"></i>
                            <span>WhatsApp Devices</span>
                        </h1>
                        <p class="mt-2 text-gray-600">Kelola device WhatsApp Anda untuk API Gateway</p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="text-sm text-gray-500">
                            <span class="font-medium">{{ Auth::user()->getActiveWhatsappDevicesCount() }}</span> / {{ Auth::user()->limit_device }} devices used
                        </div>
                        @if(Auth::user()->canCreateMoreDevices())
                            <a href="{{ route('whatsapp.devices.create') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:from-green-700 hover:to-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-lg">
                                <i class="fas fa-plus mr-2"></i>
                                Add Device
                            </a>
                        @else
                            <span class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-lg font-semibold text-xs text-gray-500 uppercase tracking-widest cursor-not-allowed">
                                <i class="fas fa-ban mr-2"></i>
                                Limit Reached
                            </span>
                        @endif
                    </div>
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

            <!-- Devices Grid -->
            @if($devices->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($devices as $device)
                        <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden border-l-4 {{ $device->isConnected() ? 'border-green-500' : ($device->hasError() ? 'border-red-500' : 'border-gray-300') }}">
                            <!-- Device Header -->
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="h-12 w-12 rounded-lg bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center">
                                            <i class="fas fa-whatsapp text-white text-xl"></i>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900">{{ $device->device_name }}</h3>
                                            <p class="text-sm text-gray-500">{{ $device->phone_number ?: 'Not connected' }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Status Badge -->
                                <div class="mb-4">
                                    {!! $device->status_badge !!}
                                </div>

                                <!-- Device Info -->
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Device Key:</span>
                                        <code class="text-xs bg-gray-100 px-2 py-1 rounded">{{ Str::limit($device->device_key, 12) }}...</code>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Last Seen:</span>
                                        <span class="text-gray-900">{{ $device->last_seen_formatted }}</span>
                                    </div>
                                    @if($device->connected_at)
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Connected:</span>
                                            <span class="text-gray-900">{{ $device->connected_at->format('d M Y H:i') }}</span>
                                        </div>
                                    @endif
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Messages:</span>
                                        <span class="text-gray-900">{{ $device->messages()->count() }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Contacts:</span>
                                        <span class="text-gray-900">{{ $device->contacts()->count() }}</span>
                                    </div>
                                </div>

                                <!-- Webhook Status -->
                                @if($device->webhook_config && isset($device->webhook_config['url']))
                                    <div class="mt-4 p-3 bg-blue-50 rounded-lg">
                                        <div class="flex items-center text-blue-700">
                                            <i class="fas fa-webhook mr-2"></i>
                                            <span class="text-sm font-medium">Webhook Configured</span>
                                        </div>
                                        <p class="text-xs text-blue-600 mt-1">{{ Str::limit($device->webhook_config['url'], 30) }}</p>
                                    </div>
                                @endif

                                <!-- Error Message -->
                                @if($device->hasError() && $device->error_message)
                                    <div class="mt-4 p-3 bg-red-50 rounded-lg">
                                        <div class="flex items-center text-red-700">
                                            <i class="fas fa-exclamation-triangle mr-2"></i>
                                            <span class="text-sm font-medium">Error</span>
                                        </div>
                                        <p class="text-xs text-red-600 mt-1">{{ $device->error_message }}</p>
                                    </div>
                                @endif
                            </div>

                            <!-- Device Actions -->
                            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        <!-- View/Manage -->
                                        <a href="{{ route('whatsapp.devices.show', $device) }}" class="text-indigo-600 hover:text-indigo-900 transition-colors duration-200" title="Manage Device">
                                            <i class="fas fa-cog"></i>
                                        </a>

                                                                <!-- API Info -->
                        <a href="{{ route('whatsapp.devices.api-info', $device) }}" class="text-indigo-600 hover:text-indigo-900 transition-colors duration-200" title="API Info & Mikrotik Scripts">
                            <i class="fas fa-info-circle"></i>
                        </a>

                        <!-- Edit -->
                        <a href="{{ route('whatsapp.devices.edit', $device) }}" class="text-green-600 hover:text-green-900 transition-colors duration-200" title="Edit Device">
                            <i class="fas fa-edit"></i>
                        </a>

                                        <!-- WhatsApp Web Interface (only if connected) -->
                                        @if($device->isConnected())
                                            <a href="{{ route('whatsapp.devices.web-interface', $device) }}" class="text-emerald-600 hover:text-emerald-900 transition-colors duration-200" title="WhatsApp Web Interface" target="_blank">
                                                <i class="fas fa-globe"></i>
                                            </a>
                                        @endif

                                        <!-- Send Message (only if connected) -->
                                        @if($device->isConnected())
                                            <button onclick="openMessageModal('{{ $device->id }}', '{{ $device->device_name }}', '{{ $device->phone_number }}')" class="text-blue-600 hover:text-blue-900 transition-colors duration-200" title="Send Message">
                                                <i class="fas fa-paper-plane"></i>
                                            </button>
                                        @endif

                                        <!-- View Contacts -->
                                        <a href="{{ route('whatsapp.devices.contacts', $device) }}" class="text-purple-600 hover:text-purple-900 transition-colors duration-200" title="View Contacts ({{ $device->contacts()->count() }})">
                                            <i class="fas fa-address-book"></i>
                                        </a>

                                        <!-- Sync Contacts (only if connected) -->
                                        @if($device->isConnected())
                                            <button onclick="syncContacts('{{ $device->id }}', '{{ $device->device_name }}')" class="text-orange-600 hover:text-orange-900 transition-colors duration-200" title="Sync Contacts">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        @endif

                                        <!-- Delete -->
                                        <form method="POST" action="{{ route('whatsapp.devices.destroy', $device) }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus device ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 transition-colors duration-200" title="Delete Device">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>

                                    <!-- Connect/Disconnect -->
                                    <div>
                                        @if($device->isConnected())
                                            <form method="POST" action="{{ route('whatsapp.devices.disconnect', $device) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center px-3 py-1 bg-red-100 text-red-700 text-xs font-medium rounded-lg hover:bg-red-200 transition-colors duration-200">
                                                    <i class="fas fa-unlink mr-1"></i>
                                                    Disconnect
                                                </button>
                                            </form>
                                        @elseif($device->isDisconnected())
                                            <form method="POST" action="{{ route('whatsapp.devices.connect', $device) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center px-3 py-1 bg-green-100 text-green-700 text-xs font-medium rounded-lg hover:bg-green-200 transition-colors duration-200">
                                                    <i class="fas fa-link mr-1"></i>
                                                    Connect
                                                </button>
                                            </form>
                                        @elseif($device->isConnecting())
                                            <span class="inline-flex items-center px-3 py-1 bg-yellow-100 text-yellow-700 text-xs font-medium rounded-lg">
                                                <i class="fas fa-spinner fa-spin mr-1"></i>
                                                Connecting...
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($devices->hasPages())
                    <div class="mt-8">
                        {{ $devices->links() }}
                    </div>
                @endif
            @else
                <!-- Empty State -->
                <div class="text-center py-16">
                    <div class="mx-auto h-24 w-24 text-gray-300">
                        <i class="fas fa-mobile-alt text-6xl"></i>
                    </div>
                    <h3 class="mt-6 text-lg font-medium text-gray-900">Belum ada device WhatsApp</h3>
                    <p class="mt-2 text-gray-500">Mulai dengan menambahkan device WhatsApp pertama Anda untuk menggunakan API Gateway.</p>
                    @if(Auth::user()->canCreateMoreDevices())
                        <div class="mt-6">
                            <a href="{{ route('whatsapp.devices.create') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 border border-transparent rounded-lg font-semibold text-sm text-white hover:from-green-700 hover:to-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-lg">
                                <i class="fas fa-plus mr-2"></i>
                                Add Your First Device
                            </a>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Send Message Modal -->
    <div id="messageModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Send WhatsApp Message</h3>
                    <button onclick="closeMessageModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form id="messageForm" method="POST" action="">
                    @csrf
                    <div class="mb-4">
                        <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-2">
                            Phone Number (with country code)
                        </label>
                        <input type="text" id="phone_number" name="phone_number" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                               placeholder="62812xxxx or +62812xxxx" required>
                        <p class="text-xs text-gray-500 mt-1">Format: 62812345678 atau +62812345678</p>
                    </div>
                    
                    <div class="mb-4">
                        <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
                            Message
                        </label>
                        <textarea id="message" name="message" rows="4" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                  placeholder="Type your message here..." required></textarea>
                    </div>
                    
                    <div class="flex items-center justify-end space-x-3">
                        <button type="button" onclick="closeMessageModal()" 
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors duration-200">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors duration-200">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Send Message
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openMessageModal(deviceId, deviceName, phoneNumber) {
            document.getElementById('modalTitle').textContent = `Send Message via ${deviceName}`;
            document.getElementById('messageForm').action = `/whatsapp/devices/${deviceId}/send-message`;
            document.getElementById('messageModal').classList.remove('hidden');
            document.getElementById('phone_number').focus();
        }

        function closeMessageModal() {
            document.getElementById('messageModal').classList.add('hidden');
            document.getElementById('messageForm').reset();
        }

        function syncContacts(deviceId, deviceName) {
            if (confirm(`Sinkronisasi kontak dari WhatsApp device "${deviceName}"?\n\nIni akan mengambil semua kontak dari WhatsApp dan menyimpannya ke database.`)) {
                // Show loading state
                const button = event.target.closest('button');
                const originalHTML = button.innerHTML;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                button.disabled = true;

                // Make sync request
                fetch(`/whatsapp/devices/${deviceId}/sync-contacts`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(`Berhasil sinkronisasi ${data.count} kontak dari WhatsApp!`);
                        location.reload(); // Refresh to show updated contact count
                    } else {
                        alert('Gagal sinkronisasi kontak: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi error saat sinkronisasi kontak');
                })
                .finally(() => {
                    // Restore button state
                    button.innerHTML = originalHTML;
                    button.disabled = false;
                });
            }
        }

        // Close modal when clicking outside
        document.getElementById('messageModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeMessageModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !document.getElementById('messageModal').classList.contains('hidden')) {
                closeMessageModal();
            }
        });
    </script>
</x-app-layout> 