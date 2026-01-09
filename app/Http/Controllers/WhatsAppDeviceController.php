<?php

namespace App\Http\Controllers;

use App\Models\WhatsappDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WhatsAppDeviceController extends Controller
{
    /**
     * Display a listing of the devices.
     */
    public function index()
    {
        $devices = Auth::user()->whatsappDevices()->with(['messages' => function($query) {
            $query->latest()->take(5);
        }])->paginate(10);
        
        return view('whatsapp.devices.index', compact('devices'));
    }

    /**
     * Show the form for creating a new device.
     */
    public function create()
    {
        // Check if user can create more devices
        if (!Auth::user()->canCreateMoreDevices()) {
            return redirect()->route('whatsapp.devices.index')
                ->with('error', 'Anda telah mencapai limit maksimal ' . Auth::user()->limit_device . ' device.');
        }

        return view('whatsapp.devices.create');
    }

    /**
     * Store a newly created device.
     */
    public function store(Request $request)
    {
        // Check device limit
        if (!Auth::user()->canCreateMoreDevices()) {
            return redirect()->route('whatsapp.devices.index')
                ->with('error', 'Anda telah mencapai limit maksimal ' . Auth::user()->limit_device . ' device.');
        }

        $validated = $request->validate([
            'device_name' => ['required', 'string', 'max:255'],
            'webhook_url' => ['nullable', 'url'],
            'webhook_events' => ['nullable', 'array'],
        ]);

        $webhookConfig = null;
        if ($validated['webhook_url']) {
            $webhookConfig = [
                'url' => $validated['webhook_url'],
                'events' => $validated['webhook_events'] ?? ['all'],
                'enabled' => true,
            ];
        }

        $device = Auth::user()->whatsappDevices()->create([
            'device_name' => $validated['device_name'],
            'device_key' => 'wa_' . Str::random(32),
            'webhook_config' => $webhookConfig,
            'status' => 'disconnected',
            'is_active' => true,
        ]);

        return redirect()->route('whatsapp.devices.show', $device)
            ->with('success', 'Device WhatsApp berhasil dibuat! Silakan scan QR code untuk menghubungkan.');
    }

    /**
     * Display the specified device.
     */
    public function show(WhatsappDevice $device)
    {
        $this->authorize('view', $device);
        
        $device->load(['messages' => function($query) {
            $query->latest()->take(10);
        }, 'contacts']);

        return view('whatsapp.devices.show', compact('device'));
    }

    /**
     * Show the form for editing the device.
     */
    public function edit(WhatsappDevice $device)
    {
        $this->authorize('update', $device);
        return view('whatsapp.devices.edit', compact('device'));
    }

    /**
     * Update the specified device.
     */
    public function update(Request $request, WhatsappDevice $device)
    {
        $this->authorize('update', $device);

        $validated = $request->validate([
            'device_name' => ['required', 'string', 'max:255'],
            'webhook_url' => ['nullable', 'url'],
            'webhook_events' => ['nullable', 'array'],
            'is_active' => ['boolean'],
        ]);

        $webhookConfig = $device->webhook_config;
        if ($validated['webhook_url']) {
            $webhookConfig = [
                'url' => $validated['webhook_url'],
                'events' => $validated['webhook_events'] ?? ['all'],
                'enabled' => true,
            ];
        }

        $device->update([
            'device_name' => $validated['device_name'],
            'webhook_config' => $webhookConfig,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('whatsapp.devices.index')
            ->with('success', 'Device berhasil diupdate!');
    }

    /**
     * Remove the specified device.
     */
    public function destroy(WhatsappDevice $device)
    {
        $this->authorize('delete', $device);

        $device->delete();

        return redirect()->route('whatsapp.devices.index')
            ->with('success', 'Device berhasil dihapus!');
    }

    /**
     * Connect device (real WhatsApp connection)
     */
    public function connect(WhatsappDevice $device)
    {
        $this->authorize('update', $device);

        try {
            // Call Real WhatsApp server to initiate connection
            $response = Http::timeout(30)->post("http://localhost:3001/api/device/{$device->device_key}/connect");
            
            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['success']) {
                    $device->update([
                        'status' => 'connecting',
                        'last_seen' => now(),
                        'error_message' => null,
                    ]);
                    
                    return redirect()->route('whatsapp.devices.show', $device)
                        ->with('success', 'Koneksi WhatsApp dimulai. Silakan scan QR code yang muncul.');
                } else {
                    return redirect()->route('whatsapp.devices.show', $device)
                        ->with('error', 'Gagal memulai koneksi: ' . $data['message']);
                }
            } else {
                throw new \Exception('WhatsApp server tidak merespons');
            }
        } catch (\Exception $e) {
            $device->update([
                'status' => 'error',
                'error_message' => 'Connection failed: ' . $e->getMessage(),
            ]);
            
            return redirect()->route('whatsapp.devices.show', $device)
                ->with('error', 'Gagal menghubungkan device: ' . $e->getMessage());
        }
    }

    /**
     * Disconnect device
     */
    public function disconnect(WhatsappDevice $device)
    {
        $this->authorize('update', $device);

        try {
            // Call Real WhatsApp server to disconnect
            $response = Http::timeout(30)->post("http://localhost:3001/api/device/{$device->device_key}/disconnect");
            
            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['success']) {
                    $device->update([
                        'status' => 'disconnected',
                        'connected_at' => null,
                        'qr_code' => null,
                        'phone_number' => null,
                        'error_message' => null,
                    ]);
                    
                    return redirect()->route('whatsapp.devices.show', $device)
                        ->with('success', 'Device berhasil disconnected.');
                } else {
                    return redirect()->route('whatsapp.devices.show', $device)
                        ->with('error', 'Gagal disconnect: ' . $data['message']);
                }
            } else {
                throw new \Exception('WhatsApp server tidak merespons');
            }
        } catch (\Exception $e) {
            // Force update status even if server call fails
            $device->update([
                'status' => 'disconnected',
                'connected_at' => null,
                'qr_code' => null,
                'phone_number' => null,
                'error_message' => 'Disconnect error: ' . $e->getMessage(),
            ]);
            
            return redirect()->route('whatsapp.devices.show', $device)
                ->with('success', 'Device disconnected (with warnings).');
        }
    }

    /**
     * Get device QR code (from WhatsApp server)
     */
    public function qrCode(WhatsappDevice $device)
    {
        $this->authorize('view', $device);

        try {
            // First check if WhatsApp server is running
            try {
                $healthCheck = Http::timeout(5)->get("http://localhost:3001/health");
                
                if (!$healthCheck->successful()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'WhatsApp server tidak berjalan. Pastikan server Node.js berjalan di port 3001.',
                        'error_type' => 'server_down'
                    ]);
                }
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'WhatsApp server tidak berjalan. Pastikan server Node.js berjalan di port 3001. Jalankan: npm run whatsapp-server',
                    'error_type' => 'server_down'
                ]);
            }

            // Check if device is connected/connecting
            $statusResponse = Http::timeout(10)->get("http://localhost:3001/api/device/{$device->device_key}/status");
            
            if ($statusResponse->successful()) {
                $statusData = $statusResponse->json();
                
                // If device is not initialized, we need to connect first
                if (isset($statusData['status']) && $statusData['status'] === 'disconnected' && 
                    (!isset($statusData['deviceKey']) || $statusData['message'] === 'Device not initialized')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Device belum di-connect. Silakan klik tombol "Connect & Generate QR" terlebih dahulu.',
                        'error_type' => 'device_not_connected',
                        'needs_connect' => true
                    ]);
                }
            }

            // Get QR code from Real WhatsApp server
            $response = Http::timeout(30)->get("http://localhost:3001/api/qr/{$device->device_key}");
            
            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['success']) {
                    // Generate QR code image URL from the QR string
                    $qrImageUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=256x256&data=' . urlencode($data['qr']);
                    
                    return response()->json([
                        'success' => true,
                        'qr_code' => [
                            'code' => $data['qr'],
                            'url' => $qrImageUrl,
                            'expires_at' => $data['expires_at'],
                            'timestamp' => $data['timestamp']
                        ],
                    ]);
                } else {
                    // QR code not available - might be expired or not generated yet
                    $message = $data['message'] ?? 'QR code belum tersedia. Pastikan device sudah di-connect dan tunggu beberapa detik.';
                    
                    return response()->json([
                        'success' => false,
                        'message' => $message,
                        'error_type' => 'qr_not_available',
                        'needs_connect' => $device->isDisconnected()
                    ]);
                }
            } else {
                throw new \Exception('WhatsApp server tidak merespons dengan status: ' . $response->status());
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat terhubung ke WhatsApp server. Pastikan server Node.js berjalan di port 3001. Jalankan: npm run whatsapp-server',
                'error_type' => 'connection_failed'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendapatkan QR code: ' . $e->getMessage(),
                'error_type' => 'unknown_error'
            ]);
        }
    }

    /**
     * Get device status (from WhatsApp server)
     */
    public function status(WhatsappDevice $device)
    {
        $this->authorize('view', $device);

        try {
            // Get status from Real WhatsApp server
            $response = Http::timeout(30)->get("http://localhost:3001/api/device/{$device->device_key}/status");
            
            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['success']) {
                    // Update local database with server status
                    $updateData = [
                        'status' => $data['status'],
                        'last_seen' => now(),
                    ];
                    
                    // If status is connected
                    if ($data['status'] === 'connected') {
                        // Always clear QR code and errors when connected
                        $updateData['qr_code'] = null;
                        $updateData['error_message'] = null;
                        
                        // Update phone number if available
                        if (isset($data['deviceInfo']['phone'])) {
                            $updateData['phone_number'] = $data['deviceInfo']['phone'];
                            $updateData['device_info'] = $data['deviceInfo'];
                            \Log::info("Device {$device->device_key} phone number updated: {$data['deviceInfo']['phone']}");
                        } else {
                            \Log::info("Device {$device->device_key} connected but phone number not available");
                        }
                        
                        // Update connected_at if status changed or if not set yet
                        if ($device->status !== 'connected' || !$device->connected_at) {
                            $updateData['connected_at'] = now();
                            \Log::info("Device {$device->device_key} status changed to connected at " . now());
                        }
                    }
                    
                    $device->update($updateData);
                    
                    // Log the update
                    \Log::info("Device {$device->device_key} status updated in database: " . $data['status']);
                    
                    return response()->json([
                        'success' => true,
                        'status' => $data['status'],
                        'last_seen' => $device->last_seen_formatted,
                        'phone_number' => $device->phone_number,
                        'connected_at' => $device->connected_at?->format('d M Y H:i'),
                        'message' => $data['message'] ?? '',
                        'device_info' => $device->device_info,
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to get status from server'
                    ]);
                }
            } else {
                throw new \Exception('WhatsApp server tidak merespons');
            }
        } catch (\Exception $e) {
            // Return local status if server is down
            return response()->json([
                'success' => true,
                'status' => $device->status,
                'last_seen' => $device->last_seen_formatted,
                'phone_number' => $device->phone_number,
                'connected_at' => $device->connected_at?->format('d M Y H:i'),
                'message' => 'Server error: ' . $e->getMessage(),
                'offline' => true,
            ]);
        }
    }

    /**
     * Send WhatsApp message through device
     */
    public function sendMessage(Request $request, WhatsappDevice $device)
    {
        $this->authorize('view', $device);

        // Validate request
        $validated = $request->validate([
            'phone_number' => ['required', 'string', 'max:100'], // Increased for group IDs
            'message' => ['required', 'string', 'max:4096'],
        ]);

        // Refresh device data from database to get latest status
        $device->refresh();
        
        \Log::info("📤 Send message called for device: {$device->device_key}", [
            'device_status' => $device->status,
            'recipient' => $validated['phone_number'],
            'message_length' => strlen($validated['message'])
        ]);

        // Check if device is connected
        if (!$device->isConnected()) {
            \Log::warning("Send message failed: Device {$device->device_key} is not connected (status: {$device->status})");
            return redirect()->route('whatsapp.devices.index')
                ->with('error', 'Device harus dalam status connected untuk mengirim pesan.');
        }

        try {
            $recipientId = $validated['phone_number'];
            
            // Check if it's already a WhatsApp ID (group or contact)
            if (str_contains($recipientId, '@g.us') || str_contains($recipientId, '@c.us')) {
                // Already in WhatsApp format (group ID or contact ID)
                $whatsappId = $recipientId;
            } else {
                // It's a phone number, process normally
                $phoneNumber = preg_replace('/[^0-9]/', '', $recipientId);
                
                // Ensure phone number starts with country code
                if (!str_starts_with($phoneNumber, '62') && !str_starts_with($phoneNumber, '1')) {
                    // If starts with 0, replace with 62
                    if (str_starts_with($phoneNumber, '0')) {
                        $phoneNumber = '62' . substr($phoneNumber, 1);
                    } else {
                        // Assume Indonesian number, add 62
                        $phoneNumber = '62' . $phoneNumber;
                    }
                }
                
                // Add @c.us suffix for individual contacts
                $whatsappId = $phoneNumber . '@c.us';
            }

            \Log::info("Sending request to Node.js server", [
                'url' => "http://localhost:3001/api/device/{$device->device_key}/send-message",
                'whatsapp_id' => $whatsappId
            ]);
            
            // Send message to WhatsApp server
            $response = Http::timeout(30)->post("http://localhost:3001/api/device/{$device->device_key}/send-message", [
                'to' => $whatsappId,
                'message' => $validated['message']
            ]);

            \Log::info("Node.js response received", [
                'status' => $response->status(),
                'success' => $response->successful()
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                \Log::info("Response data", [
                    'success' => $data['success'] ?? false,
                    'message_id' => $data['messageId'] ?? null
                ]);
                
                if ($data['success']) {
                    // Determine success message based on recipient type
                    $recipientName = str_contains($whatsappId, '@g.us') ? 'grup' : $recipientId;
                    
                    \Log::info("✅ Message sent successfully to {$recipientName}", [
                        'message_id' => $data['messageId'] ?? null,
                        'timestamp' => $data['timestamp'] ?? null
                    ]);
                    
                    // Save message to database
                    try {
                        $messageRecord = $device->messages()->create([
                            'message_id' => $data['messageId'] ?? null,
                            'chat_id' => $whatsappId,
                            'direction' => 'outgoing',
                            'type' => 'text',
                            'status' => 'sent',
                            'content' => $validated['message'],
                            'from_number' => $device->phone_number,
                            'to_number' => $whatsappId,
                            'is_group' => str_contains($whatsappId, '@g.us'),
                            'sent_at' => now(),
                            'metadata' => [
                                'timestamp' => $data['timestamp'] ?? null,
                                'sent_via_api' => true,
                            ]
                        ]);
                        
                        \Log::info("💾 Message saved to database", [
                            'db_id' => $messageRecord->id,
                            'message_id' => $messageRecord->message_id,
                            'chat_id' => $messageRecord->chat_id
                        ]);
                    } catch (\Exception $saveError) {
                        // Log error but don't fail the request
                        \Log::error("⚠️ Failed to save message to database: " . $saveError->getMessage(), [
                            'exception' => get_class($saveError),
                            'trace' => $saveError->getTraceAsString()
                        ]);
                    }
                    
                    return redirect()->route('whatsapp.devices.index')
                        ->with('success', "Pesan berhasil dikirim ke {$recipientName}!");
                } else {
                    \Log::error("❌ Node.js returned success=false", [
                        'message' => $data['message'] ?? 'Unknown error'
                    ]);
                    
                    return redirect()->route('whatsapp.devices.index')
                        ->with('error', 'Gagal mengirim pesan: ' . ($data['message'] ?? 'Unknown error'));
                }
            } else {
                \Log::error("WhatsApp server returned non-successful status", [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                throw new \Exception('WhatsApp server tidak merespons (HTTP ' . $response->status() . ')');
            }
        } catch (\Exception $e) {
            \Log::error("❌ Send message exception for device {$device->device_key}: " . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return redirect()->route('whatsapp.devices.index')
                ->with('error', 'Gagal mengirim pesan: ' . $e->getMessage());
        }
    }

    /**
     * Sync contacts from WhatsApp device
     */
    public function syncContacts(WhatsappDevice $device)
    {
        $this->authorize('view', $device);

        // Refresh device data from database to get latest status
        $device->refresh();
        
        \Log::info("Sync contacts called for device: {$device->device_key}, current status: {$device->status}");

        // Check if device is connected
        if (!$device->isConnected()) {
            \Log::warning("Sync contacts failed: Device {$device->device_key} is not connected (status: {$device->status})");
            return response()->json([
                'success' => false,
                'message' => 'Device not connected. Status: ' . $device->status
            ]);
        }

        try {
            \Log::info("Sending request to Node.js server: GET http://localhost:3001/api/device/{$device->device_key}/contacts");
            
            // Get contacts from WhatsApp server
            $response = Http::timeout(60)->get("http://localhost:3001/api/device/{$device->device_key}/contacts");

            \Log::info("Node.js response status: " . $response->status());
            
            if ($response->successful()) {
                $data = $response->json();
                \Log::info("Response data received", ['success' => $data['success'] ?? false, 'contacts_count' => count($data['contacts'] ?? [])]);
                
                if ($data['success']) {
                    $contacts = $data['contacts'];
                    $syncedCount = 0;

                    \Log::info("Starting to sync " . count($contacts) . " contacts to database...");

                    // Save contacts and groups to database
                    foreach ($contacts as $contactData) {
                        try {
                            $device->contacts()->updateOrCreate(
                                [
                                    'device_id' => $device->id,
                                    'whatsapp_id' => $contactData['id'] ?? null,
                                ],
                                [
                                    'name' => $contactData['name'] ?? null,
                                    'phone_number' => $contactData['number'] ?? null,
                                    'push_name' => $contactData['pushname'] ?? null,
                                    'is_my_contact' => $contactData['isMyContact'] ?? false,
                                    'is_group' => $contactData['isGroup'] ?? false,
                                    'group_participants' => isset($contactData['groupMetadata']) ? $contactData['groupMetadata']['participants'] ?? null : null,
                                    'profile_picture_url' => $contactData['profilePicUrl'] ?? null,
                                    'metadata' => $contactData['groupMetadata'] ?? null,
                                    'last_synced_at' => now(),
                                ]
                            );
                            $syncedCount++;
                        } catch (\Exception $contactErr) {
                            \Log::warning("Failed to sync contact: " . $contactErr->getMessage(), ['contact_id' => $contactData['id'] ?? 'unknown']);
                        }
                    }

                    \Log::info("✅ Sync completed successfully! Synced {$syncedCount} contacts for device {$device->device_key}");

                    return response()->json([
                        'success' => true,
                        'message' => "Berhasil sinkronisasi {$syncedCount} kontak",
                        'count' => $syncedCount
                    ]);
                } else {
                    \Log::error("Node.js returned success=false", ['message' => $data['message'] ?? 'unknown']);
                    return response()->json([
                        'success' => false,
                        'message' => $data['message'] ?? 'Gagal mengambil kontak dari WhatsApp'
                    ]);
                }
            } else {
                \Log::error("WhatsApp server returned non-successful status", ['status' => $response->status(), 'body' => $response->body()]);
                throw new \Exception('WhatsApp server tidak merespons (HTTP ' . $response->status() . ')');
            }
        } catch (\Exception $e) {
            \Log::error("❌ Sync contacts exception for device {$device->device_key}: " . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal sinkronisasi kontak: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Show contacts for device
     */
    public function contacts(Request $request, WhatsappDevice $device)
    {
        $this->authorize('view', $device);

        $query = $device->contacts();
        
        // Apply filters based on request
        $filter = $request->get('filter');
        switch ($filter) {
            case 'groups':
                $query->where('is_group', true);
                break;
            case 'contacts':
                $query->where('is_group', false);
                break;
            case 'my_contacts':
                $query->where('is_my_contact', true);
                break;
            case 'with_names':
                $query->whereNotNull('name');
                break;
            // 'all' or no filter - show everything
        }

        // Get per page from request, default to 1000
        $perPage = $request->get('per_page', 1000);
        $perPage = in_array($perPage, [50, 100, 500, 1000, 2000]) ? $perPage : 1000;

        $contacts = $query->orderBy('is_group', 'desc') // Groups first
            ->orderBy('name')
            ->orderBy('push_name')
            ->orderBy('phone_number')
            ->paginate($perPage)
            ->appends($request->query()); // Preserve query parameters in pagination

        return view('whatsapp.devices.contacts', compact('device', 'contacts'));
    }

    /**
     * Show API information and Mikrotik scripts
     */
    public function apiInfo(WhatsappDevice $device)
    {
        $this->authorize('view', $device);

        // Generate API endpoints
        $baseUrl = request()->getSchemeAndHttpHost();
        $apiKey = $device->device_key; // Using device key as API key
        
        return view('whatsapp.devices.api-info', compact('device', 'baseUrl', 'apiKey'));
    }

    /**
     * MPWA Compatible API endpoint for sending messages
     * Method: POST | GET 
     * Endpoint: /send-message
     * 100% Compatible with MPWA format
     */
    public function sendMessageApi(Request $request)
    {
        // Handle both JSON (POST) and URL parameters (GET) like MPWA
        $input = $request->all();
        
        // If POST with JSON body, merge with request data
        if ($request->isMethod('post') && $request->isJson()) {
            $jsonData = $request->json()->all();
            $input = array_merge($input, $jsonData);
        }

        // Validate API parameters exactly like MPWA
        $validator = validator($input, [
            'api_key' => ['required', 'string'],
            'sender' => ['required', 'string'],
            'number' => ['required', 'string'],
            'message' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . $validator->errors()->first()
            ], 400);
        }

        $validated = $validator->validated();

        try {
            // Find device by API key (device_key)
            $device = WhatsappDevice::where('device_key', $validated['api_key'])->first();
            
            if (!$device) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid API key'
                ], 401);
            }

        // Refresh device data from database to get latest status
        $device->refresh();
        
        // Check if device is connected
        if (!$device->isConnected()) {
            \Log::warning("Send message failed: Device {$device->device_key} is not connected (status: {$device->status})");
            return response()->json([
                'success' => false,
                'message' => 'Device not connected. Current status: ' . $device->status . '. Please wait for device to connect.'
            ], 400);
        }

            // Process phone numbers like MPWA (support 72888xxxx|62888xxxx format)
            $senderNumber = $this->normalizePhoneNumber($validated['sender']);
            $deviceNumber = $this->normalizePhoneNumber($device->phone_number ?? '');
            
            // Verify sender matches device phone (if device has phone number)
            // RELAXED VALIDATION: Only log warning but still allow sending
            // This allows flexibility when device phone number is not set or incorrect
            if ($deviceNumber && $senderNumber !== $deviceNumber) {
                \Log::warning("Sender number mismatch", [
                    'device_key' => $device->device_key,
                    'device_phone' => $deviceNumber,
                    'sender_requested' => $senderNumber,
                    'note' => 'Sending anyway - device might be multi-number or phone not synced'
                ]);
                
                // Don't block - just log and continue
                // Some devices may support multiple numbers or phone number not synced yet
            }

            // Process recipient number
            $recipientNumber = $validated['number'];
            
            // Check if it's already a WhatsApp ID (group or contact)
            if (str_contains($recipientNumber, '@g.us') || str_contains($recipientNumber, '@c.us')) {
                // Already in WhatsApp format
                $whatsappId = $recipientNumber;
            } else {
                // Normalize phone number for WhatsApp
                $normalizedNumber = $this->normalizePhoneNumber($recipientNumber);
                $whatsappId = $normalizedNumber . '@c.us';
            }

            // Send message to WhatsApp server
            $response = Http::timeout(30)->post("http://localhost:3001/api/device/{$device->device_key}/send-message", [
                'to' => $whatsappId,
                'message' => $validated['message']
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['success']) {
                    // Log the message for tracking
                    try {
                        $device->messages()->create([
                            'message_id' => $data['messageId'] ?? 'msg_' . time() . '_' . uniqid(),
                            'chat_id' => $whatsappId,
                            'to_number' => $whatsappId,
                            'content' => $validated['message'],
                            'direction' => 'outgoing',
                            'type' => 'text',
                            'status' => 'sent',
                            'from_number' => $device->phone_number ?? $validated['sender'],
                            'sent_at' => now(),
                            'metadata' => [
                                'sent_via_api' => true,
                                'timestamp' => $data['timestamp'] ?? time(),
                            ]
                        ]);
                    } catch (\Exception $saveError) {
                        // Log error but don't fail the request
                        \Log::error("Failed to save outgoing message to database: " . $saveError->getMessage());
                    }

                    // MPWA Compatible Response Format
                    return response()->json([
                        'success' => true,
                        'message' => 'Message sent successfully',
                        'data' => [
                            'message_id' => $data['messageId'] ?? null,
                            'timestamp' => $data['timestamp'] ?? time(),
                            'to' => $whatsappId,
                            'sender' => $validated['sender'],
                        ]
                    ], 200);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => $data['message'] ?? 'Failed to send message'
                    ], 500);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'WhatsApp server not responding'
                ], 500);
            }

        } catch (\Exception $e) {
            \Log::error("❌ Error in sendMessageApi: " . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Normalize phone number to international format (like MPWA)
     * Supports Indonesian (62) and other country codes
     */
    private function normalizePhoneNumber($phoneNumber)
    {
        // Remove all non-numeric characters
        $number = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // Handle empty number
        if (empty($number)) {
            return $number;
        }

        // If starts with 0, replace with 62 (Indonesian)
        if (str_starts_with($number, '0')) {
            return '62' . substr($number, 1);
        }
        
        // If starts with 8 and less than 11 digits, assume Indonesian mobile
        if (str_starts_with($number, '8') && strlen($number) < 11) {
            return '62' . $number;
        }
        
        // If already has country code, return as is
        if (str_starts_with($number, '62') || str_starts_with($number, '1') || str_starts_with($number, '44') || str_starts_with($number, '91')) {
            return $number;
        }
        
        // Default: assume Indonesian if not recognized
        return '62' . $number;
    }

    /**
     * Display WhatsApp Web Interface for the device
     */
    public function webInterface(WhatsappDevice $device)
    {
        $this->authorize('view', $device);

        // Check if device is connected
        if (!$device->isConnected()) {
            return redirect()->route('whatsapp.devices.index')
                ->with('error', 'Device harus dalam status connected untuk menggunakan Web Interface.');
        }

        // Get recent chats (from messages grouped by chat_id)
        $recentChats = $device->messages()
            ->select('chat_id', 'to_number', 'is_group')
            ->selectRaw('MAX(created_at) as last_message_time')
            ->selectRaw('COUNT(*) as message_count')
            ->groupBy('chat_id', 'to_number', 'is_group')
            ->orderByDesc('last_message_time')
            ->limit(50)
            ->get();

        // Get contacts for quick access
        $contacts = $device->contacts()
            ->orderBy('name')
            ->limit(100)
            ->get();

        return view('whatsapp.devices.web-interface', compact('device', 'recentChats', 'contacts'));
    }

    /**
     * Get recent chats for web interface (API endpoint for auto-refresh)
     */
    public function getRecentChats(WhatsappDevice $device)
    {
        $this->authorize('view', $device);

        try {
            // Get all unique chat_ids with their latest message info
            $allChats = $device->messages()
                ->select('chat_id', 'to_number', 'is_group', 'created_at', 'content')
                ->orderByDesc('created_at')
                ->get();
            
            // Group by normalized chat_id (convert @lid to @c.us)
            $groupedChats = [];
            foreach ($allChats as $msg) {
                $normalizedChatId = str_replace('@lid', '@c.us', $msg->chat_id);
                
                if (!isset($groupedChats[$normalizedChatId])) {
                    $groupedChats[$normalizedChatId] = [
                        'chat_id' => $normalizedChatId,
                        'to_number' => $msg->to_number,
                        'is_group' => $msg->is_group,
                        'last_message_time' => $msg->created_at,
                        'last_message_preview' => $msg->content,
                        'message_count' => 0
                    ];
                }
                
                $groupedChats[$normalizedChatId]['message_count']++;
                
                // Update if this message is newer
                if ($msg->created_at > $groupedChats[$normalizedChatId]['last_message_time']) {
                    $groupedChats[$normalizedChatId]['last_message_time'] = $msg->created_at;
                    $groupedChats[$normalizedChatId]['last_message_preview'] = $msg->content;
                }
            }
            
            // Convert to array and sort by last_message_time
            $recentChats = collect($groupedChats)
                ->sortByDesc('last_message_time')
                ->take(50)
                ->map(function ($chat) {
                    return [
                        'chat_id' => $chat['chat_id'],
                        'to_number' => $chat['to_number'],
                        'is_group' => $chat['is_group'],
                        'last_message_time' => $chat['last_message_time'],
                        'last_message_time_formatted' => \Carbon\Carbon::parse($chat['last_message_time'])->diffForHumans(),
                        'message_count' => $chat['message_count'],
                        'last_message_preview' => \Illuminate\Support\Str::limit($chat['last_message_preview'], 50)
                    ];
                })
                ->values();

            return response()->json([
                'success' => true,
                'chats' => $recentChats
            ]);
        } catch (\Exception $e) {
            \Log::error("Error getting recent chats: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil daftar chat: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get messages for a specific chat
     */
    public function getChatMessages(WhatsappDevice $device, $chatId)
    {
        $this->authorize('view', $device);

        try {
            // Decode chat ID if it's URL encoded
            $chatId = urldecode($chatId);
            
            // Normalize chat_id - handle both @lid and @c.us formats
            $normalizedChatId = $chatId;
            if (str_contains($normalizedChatId, '@lid')) {
                $normalizedChatId = str_replace('@lid', '@c.us', $normalizedChatId);
            }

            // Get messages for this chat - try both original and normalized chat_id
            $messages = $device->messages()
                ->where(function($query) use ($chatId, $normalizedChatId) {
                    $query->where('chat_id', $chatId)
                          ->orWhere('chat_id', $normalizedChatId);
                })
                ->orderBy('created_at', 'asc')
                ->limit(100)
                ->get()
                ->map(function ($message) {
                    return [
                        'id' => $message->id,
                        'message_id' => $message->message_id,
                        'direction' => $message->direction,
                        'type' => $message->type,
                        'content' => $message->content,
                        'sent_at' => $message->sent_at,
                        'created_at' => $message->created_at,
                        'from_number' => $message->from_number,
                        'to_number' => $message->to_number,
                    ];
                });

            return response()->json([
                'success' => true,
                'messages' => $messages
            ]);
        } catch (\Exception $e) {
            \Log::error("Error getting messages for chat {$chatId}: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil pesan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save incoming message from Node.js server (API endpoint)
     */
    public function saveIncomingMessage(Request $request)
    {
        try {
            \Log::info("📥 Incoming message webhook received", [
                'device_key' => $request->device_key,
                'message_id' => $request->message_id,
                'from' => $request->from_number,
                'to' => $request->to_number,
                'chat_id' => $request->chat_id,
                'direction' => $request->direction,
                'content_preview' => \Illuminate\Support\Str::limit($request->content ?? '', 50)
            ]);

            // Find device by device_key
            $device = WhatsappDevice::where('device_key', $request->device_key)->first();
            
            if (!$device) {
                \Log::error("Device not found: {$request->device_key}");
                return response()->json([
                    'success' => false,
                    'message' => 'Device not found'
                ], 404);
            }

            // Check if message already exists (prevent duplicates)
            $existingMessage = $device->messages()
                ->where('message_id', $request->message_id)
                ->first();

            if ($existingMessage) {
                \Log::info("Message already exists, skipping: {$request->message_id}");
                return response()->json([
                    'success' => true,
                    'message' => 'Message already exists',
                    'message_id' => $existingMessage->id,
                    'duplicate' => true
                ]);
            }

            // Normalize chat_id - convert @lid to @c.us or @g.us
            $chatId = $request->chat_id;
            if ($chatId && str_contains($chatId, '@lid')) {
                // Convert @lid to @c.us for individual chats
                $chatId = str_replace('@lid', '@c.us', $chatId);
                \Log::info("🔄 Normalized chat_id from {$request->chat_id} to {$chatId}");
            }
            
            // Skip system messages like status@broadcast
            if ($chatId && (str_contains($chatId, 'status@broadcast') || str_contains($chatId, '@broadcast'))) {
                \Log::info("⏭️  Skipping system/broadcast message: {$chatId}");
                return response()->json([
                    'success' => true,
                    'message' => 'System message skipped',
                    'skipped' => true
                ]);
            }
            
            // Also normalize from_number and to_number if needed
            $fromNumber = $request->from_number;
            if ($fromNumber && str_contains($fromNumber, '@lid')) {
                $fromNumber = str_replace('@lid', '@c.us', $fromNumber);
            }
            
            $toNumber = $request->to_number;
            if ($toNumber && str_contains($toNumber, '@lid')) {
                $toNumber = str_replace('@lid', '@c.us', $toNumber);
            }

            // Validate required fields
            if (empty($chatId)) {
                \Log::warning("Empty chat_id, using from_number as fallback");
                $chatId = $fromNumber ?: 'unknown';
            }
            
            if (empty($request->message_id)) {
                \Log::warning("Empty message_id, generating one");
                $messageId = 'msg_' . time() . '_' . uniqid();
            } else {
                $messageId = $request->message_id;
            }
            
            // Normalize message type - map WhatsApp types to database ENUM values
            $messageType = $request->type ?? 'text';
            $validTypes = ['text', 'image', 'document', 'audio', 'video', 'location', 'contact', 'sticker'];
            
            // Map common WhatsApp types to valid database types
            $typeMapping = [
                'chat' => 'text',        // WhatsApp "chat" type = text message
                'ptt' => 'audio',        // Push-to-talk = audio
                'ptv' => 'video',        // Push-to-talk video = video
                'gif' => 'image',        // GIF = image
                'media' => 'image',       // Generic media = image
            ];
            
            if (isset($typeMapping[$messageType])) {
                $messageType = $typeMapping[$messageType];
                \Log::info("🔄 Mapped message type from {$request->type} to {$messageType}");
            } elseif (!in_array($messageType, $validTypes)) {
                \Log::warning("⚠️  Invalid message type '{$messageType}', defaulting to 'text'");
                $messageType = 'text';
            }
            
            // Normalize status - map to valid database ENUM values
            $messageStatus = $request->status ?? 'pending';
            $validStatuses = ['pending', 'sent', 'delivered', 'read', 'failed', 'received'];
            
            // Map "received" to "pending" for incoming messages
            if ($messageStatus === 'received') {
                $messageStatus = 'pending';
            } elseif (!in_array($messageStatus, ['pending', 'sent', 'delivered', 'read', 'failed'])) {
                $messageStatus = 'pending';
            }

            // Create new message record
            try {
                $message = $device->messages()->create([
                    'message_id' => $messageId,
                    'chat_id' => $chatId,
                    'direction' => $request->direction ?? 'incoming',
                    'type' => $messageType,
                    'status' => $messageStatus,
                    'content' => $request->content ?? '',
                    'from_number' => $fromNumber ?? $chatId,
                    'to_number' => $toNumber ?? $device->phone_number,
                    'is_group' => $request->is_group ?? false,
                    'sent_at' => $request->timestamp ? \Carbon\Carbon::createFromTimestamp($request->timestamp) : now(),
                    'metadata' => $request->metadata ?? null
                ]);
            } catch (\Exception $createError) {
                \Log::error("❌ Failed to create message record: " . $createError->getMessage(), [
                    'exception' => get_class($createError),
                    'trace' => $createError->getTraceAsString(),
                    'data' => [
                        'message_id' => $messageId,
                        'chat_id' => $chatId,
                        'from_number' => $fromNumber,
                        'to_number' => $toNumber
                    ]
                ]);
                throw $createError;
            }

            \Log::info("✅ Incoming message saved successfully", [
                'db_id' => $message->id,
                'message_id' => $message->message_id,
                'chat_id' => $message->chat_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Message saved successfully',
                'message_id' => $message->id
            ]);

        } catch (\Exception $e) {
            \Log::error("❌ Error saving incoming message: " . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save message: ' . $e->getMessage()
            ], 500);
        }
    }
}
