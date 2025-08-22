<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 flex items-center space-x-2">
                            <i class="fas fa-sync-alt text-blue-600"></i>
                            <span>System Update</span>
                        </h1>
                        <p class="mt-2 text-gray-600">Kelola update sistem dari GitHub repository</p>
                    </div>
                    <div class="flex space-x-3">
                        <button onclick="checkForUpdates()" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-lg">
                            <i class="fas fa-search mr-2"></i>
                            Check Updates
                        </button>
                    </div>
                </div>
            </div>

            <!-- Success/Error Messages -->
            <div id="alert-container" class="mb-6"></div>

            <!-- Version Information -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow-lg rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-code-branch text-green-600 text-lg"></i>
                                </div>
                            </div>
                            <div class="ml-4 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Current Version</dt>
                                    <dd class="text-lg font-semibold text-gray-900" id="current-version">{{ $currentVersion }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-lg rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <i class="fab fa-github text-blue-600 text-lg"></i>
                                </div>
                            </div>
                            <div class="ml-4 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Latest Version</dt>
                                    <dd class="text-lg font-semibold text-gray-900" id="latest-version">{{ $latestVersion }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-lg rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 {{ $updateAvailable ? 'bg-yellow-100' : 'bg-green-100' }} rounded-lg flex items-center justify-center">
                                    <i class="fas {{ $updateAvailable ? 'fa-exclamation-triangle text-yellow-600' : 'fa-check-circle text-green-600' }} text-lg"></i>
                                </div>
                            </div>
                            <div class="ml-4 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Status</dt>
                                    <dd class="text-lg font-semibold {{ $updateAvailable ? 'text-yellow-600' : 'text-green-600' }}" id="update-status">
                                        {{ $updateAvailable ? 'Update Available' : 'Up to Date' }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Update Actions -->
            @if($updateAvailable)
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-8">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-yellow-400 text-xl"></i>
                        </div>
                        <div class="ml-3 flex-1">
                            <h3 class="text-sm font-medium text-yellow-800">Update Tersedia</h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <p>Ada update baru yang tersedia dari GitHub repository. Sistem akan otomatis membuat backup sebelum melakukan update.</p>
                            </div>
                            <div class="mt-4">
                                <button onclick="performUpdate()" id="update-btn" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 focus:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <i class="fas fa-download mr-2"></i>
                                    Update System
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Backup Management -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-8">
                <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-blue-50 border-b border-indigo-200">
                    <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-database mr-2 text-indigo-600"></i>
                        Backup Management
                    </h3>
                </div>

                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <p class="text-gray-600">Kelola backup sistem untuk restore jika diperlukan</p>
                        <button onclick="createBackup()" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <i class="fas fa-plus mr-2"></i>
                            Create Backup
                        </button>
                    </div>

                    @if(count($backups) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Backup Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Version</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200" id="backup-list">
                                    @foreach($backups as $backup)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <i class="fas fa-archive text-gray-400 mr-2"></i>
                                                    <span class="text-sm font-medium text-gray-900">{{ $backup['name'] }}</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ \Carbon\Carbon::parse($backup['created_at'])->format('d M Y, H:i') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ $backup['version'] }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $backup['size'] }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex items-center justify-end space-x-2">
                                                    <button onclick="restoreBackup('{{ $backup['name'] }}')" class="text-green-600 hover:text-green-900 transition-colors duration-200" title="Restore">
                                                        <i class="fas fa-undo"></i>
                                                    </button>
                                                    <button onclick="deleteBackup('{{ $backup['name'] }}')" class="text-red-600 hover:text-red-900 transition-colors duration-200" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                            <p class="text-gray-500">Belum ada backup yang dibuat</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Update Logs -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-file-alt mr-2 text-gray-600"></i>
                        Update Logs
                    </h3>
                </div>

                <div class="p-6">
                    @if(count($updateLogs) > 0)
                        <div class="bg-gray-900 rounded-lg p-4 overflow-auto max-h-96">
                            <pre class="text-green-400 text-sm font-mono" id="update-logs">{{ implode("\n", $updateLogs) }}</pre>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-file-alt text-4xl text-gray-300 mb-4"></i>
                            <p class="text-gray-500">Belum ada log update</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        let isUpdating = false;

        function showAlert(type, message) {
            const alertContainer = document.getElementById('alert-container');
            const alertClass = type === 'success' ? 'bg-green-50 border-green-200 text-green-700' : 'bg-red-50 border-red-200 text-red-700';
            const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
            
            alertContainer.innerHTML = `
                <div class="${alertClass} border px-4 py-3 rounded-lg flex items-center">
                    <i class="fas ${iconClass} mr-2"></i>
                    ${message}
                </div>
            `;

            // Auto hide after 5 seconds
            setTimeout(() => {
                alertContainer.innerHTML = '';
            }, 5000);
        }

        function setLoading(button, loading) {
            if (loading) {
                button.disabled = true;
                button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Loading...';
            } else {
                button.disabled = false;
                button.innerHTML = button.dataset.originalText || button.innerHTML;
            }
        }

        async function checkForUpdates() {
            const button = event.target.closest('button');
            button.dataset.originalText = button.innerHTML;
            setLoading(button, true);

            try {
                const response = await fetch('{{ route("system.check-updates") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                // Check if response is actually JSON before parsing
                const contentType = response.headers.get('content-type');
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                if (!contentType || !contentType.includes('application/json')) {
                    const textResponse = await response.text();
                    throw new Error(`Server returned HTML instead of JSON: ${textResponse.substring(0, 100)}...`);
                }

                const data = await response.json();

                if (data.success) {
                    document.getElementById('current-version').textContent = data.current_version;
                    document.getElementById('latest-version').textContent = data.latest_version;
                    
                    const statusElement = document.getElementById('update-status');
                    if (data.update_available) {
                        statusElement.textContent = 'Update Available';
                        statusElement.className = 'text-lg font-semibold text-yellow-600';
                        location.reload(); // Reload to show update button
                    } else {
                        statusElement.textContent = 'Up to Date';
                        statusElement.className = 'text-lg font-semibold text-green-600';
                    }
                    
                    showAlert('success', data.message);
                } else {
                    showAlert('error', data.message);
                }
            } catch (error) {
                showAlert('error', 'Error checking for updates: ' + error.message);
            } finally {
                setLoading(button, false);
            }
        }

        async function performUpdate() {
            if (isUpdating) return;
            
            if (!confirm('Apakah Anda yakin ingin melakukan update? System akan membuat backup otomatis sebelum update.')) {
                return;
            }

            isUpdating = true;
            const button = document.getElementById('update-btn');
            button.dataset.originalText = button.innerHTML;
            setLoading(button, true);

            try {
                const response = await fetch('{{ route("system.perform-update") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                // Check if response is actually JSON before parsing
                const contentType = response.headers.get('content-type');
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                if (!contentType || !contentType.includes('application/json')) {
                    const textResponse = await response.text();
                    throw new Error(`Server returned HTML instead of JSON: ${textResponse.substring(0, 100)}...`);
                }

                const data = await response.json();

                if (data.success) {
                    showAlert('success', data.message);
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    showAlert('error', data.message + (data.suggestion ? ' ' + data.suggestion : ''));
                }
            } catch (error) {
                showAlert('error', 'Error performing update: ' + error.message);
            } finally {
                isUpdating = false;
                setLoading(button, false);
            }
        }

        async function createBackup() {
            const button = event.target.closest('button');
            button.dataset.originalText = button.innerHTML;
            setLoading(button, true);

            try {
                const response = await fetch('{{ route("system.create-backup") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                // Check if response is actually JSON before parsing
                const contentType = response.headers.get('content-type');
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                if (!contentType || !contentType.includes('application/json')) {
                    const textResponse = await response.text();
                    throw new Error(`Server returned HTML instead of JSON: ${textResponse.substring(0, 100)}...`);
                }

                const data = await response.json();

                if (data.success) {
                    showAlert('success', data.message);
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showAlert('error', data.message);
                }
            } catch (error) {
                showAlert('error', 'Error creating backup: ' + error.message);
            } finally {
                setLoading(button, false);
            }
        }

        async function restoreBackup(backupName) {
            if (!confirm(`Apakah Anda yakin ingin restore dari backup "${backupName}"? Ini akan mengganti semua file dan database saat ini.`)) {
                return;
            }

            try {
                const response = await fetch('{{ route("system.restore-backup") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        backup_name: backupName
                    })
                });

                // Check if response is actually JSON before parsing
                const contentType = response.headers.get('content-type');
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                if (!contentType || !contentType.includes('application/json')) {
                    const textResponse = await response.text();
                    throw new Error(`Server returned HTML instead of JSON: ${textResponse.substring(0, 100)}...`);
                }

                const data = await response.json();

                if (data.success) {
                    showAlert('success', data.message);
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    showAlert('error', data.message);
                }
            } catch (error) {
                showAlert('error', 'Error restoring backup: ' + error.message);
            }
        }

        async function deleteBackup(backupName) {
            if (!confirm(`Apakah Anda yakin ingin menghapus backup "${backupName}"? Tindakan ini tidak dapat dibatalkan.`)) {
                return;
            }

            try {
                const response = await fetch('{{ route("system.delete-backup") }}', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        backup_name: backupName
                    })
                });

                // Check if response is actually JSON before parsing
                const contentType = response.headers.get('content-type');
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                if (!contentType || !contentType.includes('application/json')) {
                    const textResponse = await response.text();
                    throw new Error(`Server returned HTML instead of JSON: ${textResponse.substring(0, 100)}...`);
                }

                const data = await response.json();

                if (data.success) {
                    showAlert('success', data.message);
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    showAlert('error', data.message);
                }
            } catch (error) {
                showAlert('error', 'Error deleting backup: ' + error.message);
            }
        }
    </script>
</x-app-layout> 