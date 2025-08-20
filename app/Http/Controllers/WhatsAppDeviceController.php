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
                    return response()->json([
                        'success' => false,
                        'message' => $data['message'] ?? 'No QR code available'
                    ]);
                }
            } else {
                throw new \Exception('WhatsApp server tidak merespons');
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendapatkan QR code: ' . $e->getMessage()
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
                    
                    // Update phone number if connected
                    if ($data['status'] === 'connected' && isset($data['deviceInfo']['phone'])) {
                        $updateData['phone_number'] = $data['deviceInfo']['phone'];
                        $updateData['connected_at'] = now();
                        $updateData['device_info'] = $data['deviceInfo'];
                    }
                    
                    $device->update($updateData);
                    
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

        // Check if device is connected
        if (!$device->isConnected()) {
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

            // Send message to WhatsApp server
            $response = Http::timeout(30)->post("http://localhost:3001/api/device/{$device->device_key}/send-message", [
                'to' => $whatsappId,
                'message' => $validated['message']
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['success']) {
                    // Determine success message based on recipient type
                    $recipientName = str_contains($whatsappId, '@g.us') ? 'grup' : $recipientId;
                    return redirect()->route('whatsapp.devices.index')
                        ->with('success', "Pesan berhasil dikirim ke {$recipientName}!");
                } else {
                    return redirect()->route('whatsapp.devices.index')
                        ->with('error', 'Gagal mengirim pesan: ' . ($data['message'] ?? 'Unknown error'));
                }
            } else {
                throw new \Exception('WhatsApp server tidak merespons');
            }
        } catch (\Exception $e) {
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

        // Check if device is connected
        if (!$device->isConnected()) {
            return response()->json([
                'success' => false,
                'message' => 'Device harus dalam status connected untuk sinkronisasi kontak.'
            ]);
        }

        try {
            // Get contacts from WhatsApp server
            $response = Http::timeout(60)->get("http://localhost:3001/api/device/{$device->device_key}/contacts");

            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['success']) {
                    $contacts = $data['contacts'];
                    $syncedCount = 0;

                    // Save contacts and groups to database
                    foreach ($contacts as $contactData) {
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
                    }

                    return response()->json([
                        'success' => true,
                        'message' => "Berhasil sinkronisasi {$syncedCount} kontak",
                        'count' => $syncedCount
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => $data['message'] ?? 'Gagal mengambil kontak dari WhatsApp'
                    ]);
                }
            } else {
                throw new \Exception('WhatsApp server tidak merespons');
            }
        } catch (\Exception $e) {
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
        if ($request->isMethod('post') && $request->getContentType() === 'json') {
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

            // Check if device is connected
            if (!$device->isConnected()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Device not connected'
                ], 400);
            }

            // Process phone numbers like MPWA (support 72888xxxx|62888xxxx format)
            $senderNumber = $this->normalizePhoneNumber($validated['sender']);
            $deviceNumber = $this->normalizePhoneNumber($device->phone_number ?? '');
            
            // Verify sender matches device phone (if device has phone number)
            if ($deviceNumber && $senderNumber !== $deviceNumber) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sender number does not match device phone number'
                ], 400);
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
                    $device->messages()->create([
                        'to_number' => $whatsappId,
                        'content' => $validated['message'],
                        'direction' => 'outgoing',
                        'status' => 'sent',
                        'whatsapp_message_id' => $data['messageId'] ?? null,
                    ]);

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
}
