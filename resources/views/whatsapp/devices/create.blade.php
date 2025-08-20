<x-app-layout>
    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('whatsapp.devices.index') }}" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Devices
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 flex items-center space-x-2">
                            <i class="fas fa-plus-circle text-green-600"></i>
                            <span>Add WhatsApp Device</span>
                        </h1>
                        <p class="mt-2 text-gray-600">Create a new WhatsApp device for your API Gateway</p>
                    </div>
                </div>
            </div>

            <!-- Device Limit Info -->
            <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-center">
                    <i class="fas fa-info-circle text-blue-600 mr-3"></i>
                    <div>
                        <h3 class="text-sm font-medium text-blue-800">Device Limit</h3>
                        <p class="text-sm text-blue-700 mt-1">
                            You can create <strong>{{ Auth::user()->limit_device - Auth::user()->getActiveWhatsappDevicesCount() }}</strong> more devices. 
                            Currently using <strong>{{ Auth::user()->getActiveWhatsappDevicesCount() }}</strong> of <strong>{{ Auth::user()->limit_device }}</strong> allowed devices.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-mobile-alt mr-2 text-green-600"></i>
                        Device Configuration
                    </h3>
                </div>

                <form method="POST" action="{{ route('whatsapp.devices.store') }}" class="p-6 space-y-6">
                    @csrf

                    <!-- Device Name -->
                    <div>
                        <label for="device_name" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-tag mr-1 text-gray-400"></i>
                            Device Name <span class="text-red-500">*</span>
                        </label>
                        <input id="device_name" 
                               name="device_name" 
                               type="text" 
                               value="{{ old('device_name') }}" 
                               required 
                               autofocus 
                               class="block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm @error('device_name') border-red-300 @enderror"
                               placeholder="e.g., Customer Service Bot, Marketing Device">
                        <p class="mt-1 text-sm text-gray-500">Choose a descriptive name to identify this device</p>
                        @error('device_name')
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Webhook Configuration -->
                    <div class="border-t border-gray-200 pt-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-webhook mr-2 text-blue-600"></i>
                            Webhook Configuration
                        </h4>
                        <p class="text-sm text-gray-600 mb-4">Configure webhook to receive real-time notifications about messages and events</p>

                        <!-- Webhook URL -->
                        <div class="mb-4">
                            <label for="webhook_url" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-link mr-1 text-gray-400"></i>
                                Webhook URL
                            </label>
                            <input id="webhook_url" 
                                   name="webhook_url" 
                                   type="url" 
                                   value="{{ old('webhook_url') }}" 
                                   class="block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm @error('webhook_url') border-red-300 @enderror"
                                   placeholder="https://yoursite.com/webhook/whatsapp">
                            <p class="mt-1 text-sm text-gray-500">Your endpoint to receive webhook notifications (optional)</p>
                            @error('webhook_url')
                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Webhook Events -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-bell mr-1 text-gray-400"></i>
                                Webhook Events
                            </label>
                            <div class="grid grid-cols-2 gap-4">
                                <label class="flex items-center">
                                    <input type="checkbox" name="webhook_events[]" value="message" 
                                           class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
                                           {{ in_array('message', old('webhook_events', [])) ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-700">
                                        <i class="fas fa-comment mr-1 text-blue-500"></i>
                                        Messages
                                    </span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="webhook_events[]" value="status" 
                                           class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
                                           {{ in_array('status', old('webhook_events', [])) ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-700">
                                        <i class="fas fa-check-circle mr-1 text-green-500"></i>
                                        Message Status
                                    </span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="webhook_events[]" value="connection" 
                                           class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
                                           {{ in_array('connection', old('webhook_events', [])) ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-700">
                                        <i class="fas fa-wifi mr-1 text-purple-500"></i>
                                        Connection Events
                                    </span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="webhook_events[]" value="all" 
                                           class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
                                           {{ in_array('all', old('webhook_events', ['all'])) ? 'checked' : 'checked' }}>
                                    <span class="ml-2 text-sm text-gray-700">
                                        <i class="fas fa-globe mr-1 text-gray-500"></i>
                                        All Events
                                    </span>
                                </label>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">Select which events you want to receive via webhook</p>
                        </div>
                    </div>

                    <!-- Usage Guidelines -->
                    <div class="border-t border-gray-200 pt-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-lightbulb mr-2 text-yellow-600"></i>
                            Quick Setup Guide
                        </h4>
                        <div class="bg-yellow-50 rounded-lg p-4">
                            <div class="space-y-2 text-sm text-yellow-800">
                                <div class="flex items-start">
                                    <span class="font-semibold mr-2">1.</span>
                                    <span>Create your device with a descriptive name</span>
                                </div>
                                <div class="flex items-start">
                                    <span class="font-semibold mr-2">2.</span>
                                    <span>After creation, you'll see a QR code to scan with WhatsApp</span>
                                </div>
                                <div class="flex items-start">
                                    <span class="font-semibold mr-2">3.</span>
                                    <span>Once connected, you can start sending/receiving messages via API</span>
                                </div>
                                <div class="flex items-start">
                                    <span class="font-semibold mr-2">4.</span>
                                    <span>Use the device key in your API calls for authentication</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                        <a href="{{ route('whatsapp.devices.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <i class="fas fa-times mr-2"></i>
                            Cancel
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-widest hover:from-green-700 hover:to-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-lg">
                            <i class="fas fa-plus mr-2"></i>
                            Create Device
                        </button>
                    </div>
                </form>
            </div>

            <!-- API Integration Info -->
            <div class="mt-8 bg-gray-50 rounded-xl p-6">
                <h4 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-code mr-2 text-indigo-600"></i>
                    API Integration
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h5 class="font-medium text-gray-700 mb-2">API Endpoint</h5>
                        <code class="block text-sm bg-gray-800 text-green-400 p-3 rounded">{{ url('/api/whatsapp') }}</code>
                    </div>
                    <div>
                        <h5 class="font-medium text-gray-700 mb-2">Authentication</h5>
                        <p class="text-sm text-gray-600">Use your device key as Bearer token in Authorization header</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 