<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('whatsapp.devices.show', $device) }}" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to Device
                        </a>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 flex items-center space-x-2">
                                <i class="fas fa-address-book text-purple-600"></i>
                                <span>Contacts</span>
                            </h1>
                            <p class="mt-2 text-gray-600">{{ $device->device_name }} - {{ $contacts->total() }} contacts</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <!-- Sync Contacts Button -->
                        @if($device->isConnected())
                            <button onclick="syncContacts('{{ $device->id }}', '{{ $device->device_name }}')" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <i class="fas fa-sync mr-2"></i>
                                Sync Contacts
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Contacts Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-white rounded-lg shadow p-6 cursor-pointer hover:shadow-lg transition-shadow duration-200" onclick="filterContacts('all')">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-users text-blue-600 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-500">Total Contacts</div>
                            <div class="text-2xl font-bold text-gray-900">{{ number_format($contacts->total()) }}</div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6 cursor-pointer hover:shadow-lg transition-shadow duration-200" onclick="filterContacts('my_contacts')">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-address-card text-green-600 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-500">My Contacts</div>
                            <div class="text-2xl font-bold text-gray-900">{{ number_format($device->contacts()->where('is_my_contact', true)->count()) }}</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6 cursor-pointer hover:shadow-lg transition-shadow duration-200" onclick="filterContacts('with_names')">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-id-card text-yellow-600 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-500">With Names</div>
                            <div class="text-2xl font-bold text-gray-900">{{ number_format($device->contacts()->whereNotNull('name')->count()) }}</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6 cursor-pointer hover:shadow-lg transition-shadow duration-200 {{ request('filter') === 'groups' ? 'ring-2 ring-indigo-500 bg-indigo-50' : '' }}" onclick="filterContacts('groups')">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-users text-indigo-600 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-500">Groups</div>
                            <div class="text-2xl font-bold text-gray-900">{{ number_format($device->contacts()->where('is_group', true)->count()) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search Bar & Filters -->
            <div class="bg-white rounded-lg shadow mb-6 p-4">
                <div class="flex items-center space-x-4 mb-4">
                    <div class="flex-1">
                        <input type="text" id="searchContacts" placeholder="Search contacts by name or phone number..." 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    </div>
                    <button onclick="clearSearch()" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors duration-200">
                        <i class="fas fa-times"></i> Clear
                    </button>
                </div>
                
                <!-- Filter Buttons & Per Page -->
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="flex flex-wrap gap-2">
                        <button onclick="filterContacts('all')" class="filter-btn {{ !request('filter') || request('filter') === 'all' ? 'active' : '' }}" data-filter="all">
                            <i class="fas fa-users mr-1"></i> All ({{ number_format($contacts->total()) }})
                        </button>
                        <button onclick="filterContacts('contacts')" class="filter-btn {{ request('filter') === 'contacts' ? 'active' : '' }}" data-filter="contacts">
                            <i class="fas fa-user mr-1"></i> Contacts Only ({{ number_format($device->contacts()->where('is_group', false)->count()) }})
                        </button>
                        <button onclick="filterContacts('groups')" class="filter-btn {{ request('filter') === 'groups' ? 'active' : '' }}" data-filter="groups">
                            <i class="fas fa-users mr-1"></i> Groups Only ({{ number_format($device->contacts()->where('is_group', true)->count()) }})
                        </button>
                        <button onclick="filterContacts('my_contacts')" class="filter-btn {{ request('filter') === 'my_contacts' ? 'active' : '' }}" data-filter="my_contacts">
                            <i class="fas fa-address-card mr-1"></i> My Contacts ({{ number_format($device->contacts()->where('is_my_contact', true)->count()) }})
                        </button>
                    </div>
                    
                    <!-- Per Page Selector -->
                    <div class="flex items-center space-x-2">
                        <label class="text-sm text-gray-600">Show:</label>
                        <select onchange="changePerPage(this.value)" class="text-sm border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100</option>
                            <option value="500" {{ request('per_page') == '500' ? 'selected' : '' }}>500</option>
                            <option value="1000" {{ request('per_page', '1000') == '1000' ? 'selected' : '' }}>1000</option>
                            <option value="2000" {{ request('per_page') == '2000' ? 'selected' : '' }}>2000</option>
                        </select>
                        <span class="text-sm text-gray-600">per page</span>
                    </div>
                </div>
            </div>

            <style>
                .filter-btn {
                    @apply px-3 py-1 text-sm border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors duration-200;
                }
                .filter-btn.active {
                    @apply bg-indigo-600 text-white border-indigo-600 hover:bg-indigo-700;
                }
            </style>

            <!-- Contacts Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-purple-100 border-b border-purple-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-purple-800 flex items-center">
                            <i class="fas fa-list mr-2"></i>
                            Contact List
                        </h3>
                        <div class="text-sm text-purple-700">
                            Showing {{ $contacts->firstItem() ?: 0 }} to {{ $contacts->lastItem() ?: 0 }} of {{ number_format($contacts->total()) }} entries
                        </div>
                    </div>
                </div>
                
                @if($contacts->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200" id="contactsTable">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Contact
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Phone Number
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Last Synced
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($contacts as $contact)
                                    <tr class="hover:bg-gray-50 contact-row">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    @if($contact->profile_picture_url)
                                                        <img class="h-10 w-10 rounded-full object-cover" src="{{ $contact->profile_picture_url }}" alt="Profile">
                                                    @else
                                                        <div class="h-10 w-10 rounded-full {{ $contact->is_group ? 'bg-indigo-300' : 'bg-gray-300' }} flex items-center justify-center">
                                                            <i class="fas {{ $contact->is_group ? 'fa-users text-indigo-600' : 'fa-user text-gray-500' }}"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900 contact-name flex items-center">
                                                        {{ $contact->display_name }}
                                                        @if($contact->is_group)
                                                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800">
                                                                <i class="fas fa-users mr-1"></i> Group
                                                            </span>
                                                        @endif
                                                    </div>
                                                    @if($contact->push_name && $contact->push_name !== $contact->name)
                                                        <div class="text-sm text-gray-500">
                                                            {{ $contact->push_name }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($contact->is_group)
                                                <div class="text-sm text-gray-500 italic">Group Chat</div>
                                                <div class="text-xs text-gray-500">{{ $contact->whatsapp_id }}</div>
                                            @else
                                                <div class="text-sm text-gray-900 contact-phone">{{ $contact->phone_number }}</div>
                                                <div class="text-xs text-gray-500">{{ $contact->whatsapp_id }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center space-x-2">
                                                @if($contact->is_group)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                                        <i class="fas fa-users mr-1"></i> Group Chat
                                                    </span>
                                                @elseif($contact->is_my_contact)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        <i class="fas fa-check mr-1"></i> My Contact
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                        <i class="fas fa-user-plus mr-1"></i> WhatsApp Only
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $contact->last_synced_formatted }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            @if($contact->is_group)
                                                <button onclick="sendMessage('{{ $contact->whatsapp_id }}', '{{ $contact->display_name }}')" 
                                                        class="text-blue-600 hover:text-blue-900 mr-3" title="Send Message to Group">
                                                    <i class="fas fa-paper-plane"></i>
                                                </button>
                                                <button onclick="copyGroupId('{{ $contact->whatsapp_id }}')" 
                                                        class="text-green-600 hover:text-green-900" title="Copy Group ID">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            @else
                                                <button onclick="sendMessage('{{ $contact->phone_number }}', '{{ $contact->display_name }}')" 
                                                        class="text-blue-600 hover:text-blue-900 mr-3" title="Send Message">
                                                    <i class="fas fa-paper-plane"></i>
                                                </button>
                                                <button onclick="copyNumber('{{ $contact->phone_number }}')" 
                                                        class="text-green-600 hover:text-green-900" title="Copy Number">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($contacts->hasPages())
                        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                            {{ $contacts->links() }}
                        </div>
                    @endif
                @else
                    <!-- Empty State -->
                    <div class="text-center py-16">
                        <div class="mx-auto h-24 w-24 text-gray-300">
                            <i class="fas fa-address-book text-6xl"></i>
                        </div>
                        <h3 class="mt-6 text-lg font-medium text-gray-900">No contacts found</h3>
                        <p class="mt-2 text-gray-500">Sync contacts from WhatsApp to see them here.</p>
                        @if($device->isConnected())
                            <div class="mt-6">
                                <button onclick="syncContacts('{{ $device->id }}', '{{ $device->device_name }}')" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <i class="fas fa-sync mr-2"></i>
                                    Sync Contacts Now
                                </button>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Send Message Modal (reuse from devices index) -->
    <div id="messageModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Send WhatsApp Message</h3>
                    <button onclick="closeMessageModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form id="messageForm" method="POST" action="{{ route('whatsapp.devices.send-message', $device) }}">
                    @csrf
                    <div class="mb-4">
                        <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-2">
                            <span id="recipient-label">Phone Number</span>
                        </label>
                        <input type="text" id="phone_number" name="phone_number" readonly
                               class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 focus:outline-none text-sm">
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
        // Search functionality
        document.getElementById('searchContacts').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('.contact-row');
            
            rows.forEach(row => {
                const name = row.querySelector('.contact-name').textContent.toLowerCase();
                const phone = row.querySelector('.contact-phone').textContent.toLowerCase();
                
                if (name.includes(searchTerm) || phone.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        function clearSearch() {
            document.getElementById('searchContacts').value = '';
            document.querySelectorAll('.contact-row').forEach(row => {
                row.style.display = '';
            });
        }

        function filterContacts(filterType) {
            // Update URL with filter parameter
            const url = new URL(window.location);
            if (filterType === 'all') {
                url.searchParams.delete('filter');
            } else {
                url.searchParams.set('filter', filterType);
            }
            window.location.href = url.toString();
        }

        function changePerPage(perPage) {
            // Update URL with per_page parameter
            const url = new URL(window.location);
            url.searchParams.set('per_page', perPage);
            url.searchParams.delete('page'); // Reset to first page
            window.location.href = url.toString();
        }

        function sendMessage(recipientId, contactName) {
            const isGroup = recipientId.includes('@g.us');
            const modalTitle = isGroup ? `Send Message to Group: ${contactName}` : `Send Message to ${contactName}`;
            const labelText = isGroup ? 'Group ID' : 'Phone Number';
            
            document.getElementById('modalTitle').textContent = modalTitle;
            document.getElementById('recipient-label').textContent = labelText;
            document.getElementById('phone_number').value = recipientId;
            document.getElementById('messageModal').classList.remove('hidden');
            document.getElementById('message').focus();
        }

        function closeMessageModal() {
            document.getElementById('messageModal').classList.add('hidden');
            document.getElementById('messageForm').reset();
        }

        function copyNumber(phoneNumber) {
            navigator.clipboard.writeText(phoneNumber).then(function() {
                // Simple notification
                const button = event.target.closest('button');
                const originalHTML = button.innerHTML;
                button.innerHTML = '<i class="fas fa-check text-green-600"></i>';
                setTimeout(() => {
                    button.innerHTML = originalHTML;
                }, 1000);
            });
        }

        function copyGroupId(groupId) {
            navigator.clipboard.writeText(groupId).then(function() {
                // Simple notification
                const button = event.target.closest('button');
                const originalHTML = button.innerHTML;
                button.innerHTML = '<i class="fas fa-check text-green-600"></i>';
                setTimeout(() => {
                    button.innerHTML = originalHTML;
                }, 1000);
            });
        }

        function syncContacts(deviceId, deviceName) {
            if (confirm(`Sinkronisasi kontak dari WhatsApp device "${deviceName}"?\n\nIni akan mengambil semua kontak dari WhatsApp dan menyimpannya ke database.`)) {
                // Show loading state
                const button = event.target.closest('button');
                const originalHTML = button.innerHTML;
                button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Syncing...';
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
                        location.reload(); // Refresh to show updated contacts
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