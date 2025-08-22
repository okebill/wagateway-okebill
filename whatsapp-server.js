const { Client, LocalAuth, MessageMedia } = require('whatsapp-web.js');
const express = require('express');
const http = require('http');
const socketIo = require('socket.io');
const qrcode = require('qrcode-terminal');
const fs = require('fs');
const path = require('path');

const app = express();
const server = http.createServer(app);
const io = socketIo(server, {
    cors: {
        origin: ["http://localhost:8000", "http://127.0.0.1:8000"],
        methods: ["GET", "POST"]
    }
});

app.use(express.json());
app.use(express.static('public'));

// Store active WhatsApp clients
const clients = new Map();
const qrCodes = new Map();
const deviceStatuses = new Map();

// CORS middleware
app.use((req, res, next) => {
    res.header('Access-Control-Allow-Origin', '*');
    res.header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    res.header('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, Authorization');
    if (req.method === 'OPTIONS') {
        res.sendStatus(200);
    } else {
        next();
    }
});

// Initialize WhatsApp client for a device
async function initializeWhatsAppClient(deviceKey, socket = null) {
    console.log(`Initializing WhatsApp client for device: ${deviceKey}`);
    
    const client = new Client({
        authStrategy: new LocalAuth({
            clientId: deviceKey,
            dataPath: path.join(__dirname, 'wa-sessions')
        }),
        puppeteer: {
            headless: true,
            executablePath: '/usr/bin/google-chrome-stable',
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--disable-accelerated-2d-canvas',
                '--no-first-run',
                '--no-zygote',
                '--disable-gpu',
                '--disable-extensions',
                '--disable-plugins',
                '--disable-images',
                '--disable-javascript',
                '--disable-default-apps',
                '--disable-sync'
            ]
        }
    });

    // QR Code event
    client.on('qr', (qr) => {
        console.log(`QR Code received for ${deviceKey}`);
        
        // Store QR code
        qrCodes.set(deviceKey, {
            qr: qr,
            timestamp: Date.now(),
            expires_at: Date.now() + (5 * 60 * 1000) // 5 minutes
        });
        
        // Send QR to specific device clients
        io.emit(`qr-${deviceKey}`, {
            qr: qr,
            deviceKey: deviceKey,
            timestamp: Date.now()
        });
        
        // Update device status
        updateDeviceStatus(deviceKey, 'connecting', 'Waiting for QR scan');
        
        // Generate QR code terminal output for debugging
        qrcode.generate(qr, { small: true });
    });

    // Client ready event
    client.on('ready', async () => {
        console.log(`WhatsApp client is ready for ${deviceKey}!`);
        
        const clientInfo = client.info;
        console.log(`Connected as: ${clientInfo.pushname} (${clientInfo.wid.user})`);
        
        // Update device status and info
        updateDeviceStatus(deviceKey, 'connected', 'Connected successfully', {
            pushname: clientInfo.pushname,
            phone: clientInfo.wid.user,
            platform: clientInfo.platform
        });
        
        // Clear QR code
        qrCodes.delete(deviceKey);
        
        // Emit connection success
        io.emit(`connected-${deviceKey}`, {
            deviceKey: deviceKey,
            phoneNumber: clientInfo.wid.user,
            pushname: clientInfo.pushname,
            connectedAt: new Date().toISOString()
        });
    });

    // Authentication success
    client.on('authenticated', () => {
        console.log(`Client authenticated for ${deviceKey}`);
        updateDeviceStatus(deviceKey, 'authenticated', 'Authentication successful');
    });

    // Authentication failure
    client.on('auth_failure', (msg) => {
        console.error(`Authentication failed for ${deviceKey}:`, msg);
        updateDeviceStatus(deviceKey, 'error', `Authentication failed: ${msg}`);
    });

    // Disconnected event
    client.on('disconnected', (reason) => {
        console.log(`Client disconnected for ${deviceKey}:`, reason);
        updateDeviceStatus(deviceKey, 'disconnected', `Disconnected: ${reason}`);
        
        // Clean up
        clients.delete(deviceKey);
        qrCodes.delete(deviceKey);
        
        io.emit(`disconnected-${deviceKey}`, {
            deviceKey: deviceKey,
            reason: reason,
            disconnectedAt: new Date().toISOString()
        });
    });

    // Message received event
    client.on('message', async (message) => {
        console.log(`Message received for ${deviceKey}:`, message.body);
        
        // Emit message to Laravel backend
        io.emit(`message-${deviceKey}`, {
            deviceKey: deviceKey,
            messageId: message.id.id,
            from: message.from,
            to: message.to,
            body: message.body,
            type: message.type,
            timestamp: message.timestamp,
            isGroup: message.isGroup
        });
    });

    // Store client
    clients.set(deviceKey, client);
    
    // Initialize client
    try {
        await client.initialize();
        console.log(`Client initialized for ${deviceKey}`);
    } catch (error) {
        console.error(`Failed to initialize client for ${deviceKey}:`, error);
        updateDeviceStatus(deviceKey, 'error', `Initialization failed: ${error.message}`);
    }
    
    return client;
}

// Update device status
function updateDeviceStatus(deviceKey, status, message = '', deviceInfo = null) {
    const statusData = {
        deviceKey: deviceKey,
        status: status,
        message: message,
        timestamp: new Date().toISOString(),
        deviceInfo: deviceInfo
    };
    
    deviceStatuses.set(deviceKey, statusData);
    
    // Emit status update
    io.emit(`status-${deviceKey}`, statusData);
    
    console.log(`Status updated for ${deviceKey}: ${status} - ${message}`);
}

// API Routes

// Get QR Code for device
app.get('/api/qr/:deviceKey', (req, res) => {
    const { deviceKey } = req.params;
    const qrData = qrCodes.get(deviceKey);
    
    if (qrData && qrData.expires_at > Date.now()) {
        res.json({
            success: true,
            qr: qrData.qr,
            expires_at: qrData.expires_at,
            timestamp: qrData.timestamp
        });
    } else {
        res.json({
            success: false,
            message: 'No QR code available or expired'
        });
    }
});

// Initialize device connection
app.post('/api/device/:deviceKey/connect', async (req, res) => {
    const { deviceKey } = req.params;
    
    try {
        if (clients.has(deviceKey)) {
            res.json({
                success: false,
                message: 'Device already connecting or connected'
            });
            return;
        }
        
        await initializeWhatsAppClient(deviceKey);
        
        res.json({
            success: true,
            message: 'Connection initiated. Please scan QR code.',
            deviceKey: deviceKey
        });
    } catch (error) {
        res.json({
            success: false,
            message: error.message
        });
    }
});

// Disconnect device
app.post('/api/device/:deviceKey/disconnect', async (req, res) => {
    const { deviceKey } = req.params;
    
    try {
        const client = clients.get(deviceKey);
        if (client) {
            await client.destroy();
            clients.delete(deviceKey);
            qrCodes.delete(deviceKey);
            updateDeviceStatus(deviceKey, 'disconnected', 'Manually disconnected');
        }
        
        res.json({
            success: true,
            message: 'Device disconnected successfully'
        });
    } catch (error) {
        res.json({
            success: false,
            message: error.message
        });
    }
});

// Get device status
app.get('/api/device/:deviceKey/status', (req, res) => {
    const { deviceKey } = req.params;
    const status = deviceStatuses.get(deviceKey);
    
    if (status) {
        res.json({
            success: true,
            ...status
        });
    } else {
        res.json({
            success: true,
            deviceKey: deviceKey,
            status: 'disconnected',
            message: 'Device not initialized',
            timestamp: new Date().toISOString()
        });
    }
});

// Send message
app.post('/api/device/:deviceKey/send-message', async (req, res) => {
    const { deviceKey } = req.params;
    const { to, message } = req.body;
    
    try {
        const client = clients.get(deviceKey);
        if (!client || !client.info) {
            res.json({
                success: false,
                message: 'Device not connected'
            });
            return;
        }
        
        const chatId = to.includes('@') ? to : `${to}@c.us`;
        const sentMessage = await client.sendMessage(chatId, message);
        
        res.json({
            success: true,
            messageId: sentMessage.id.id,
            timestamp: sentMessage.timestamp
        });
    } catch (error) {
        res.json({
            success: false,
            message: error.message
        });
    }
});

// Get contacts
app.get('/api/device/:deviceKey/contacts', async (req, res) => {
    const { deviceKey } = req.params;
    
    try {
        const client = clients.get(deviceKey);
        if (!client || !client.info) {
            res.json({
                success: false,
                message: 'Device not connected'
            });
            return;
        }
        
        console.log(`Getting contacts for device: ${deviceKey}`);
        const contacts = await client.getContacts();
        
        // Include both contacts and groups
        const formattedContacts = contacts
            .filter(contact => contact.isWAContact || contact.isGroup)
            .map(contact => ({
                id: contact.id._serialized,
                name: contact.name || contact.pushname || contact.subject || 'Unknown',
                number: contact.number || '',
                pushname: contact.pushname || null,
                isMyContact: contact.isMyContact || false,
                isGroup: contact.isGroup || false,
                groupMetadata: contact.isGroup ? {
                    subject: contact.subject || null,
                    desc: contact.groupMetadata?.desc || null,
                    participantsCount: contact.groupMetadata?.participants?.length || 0,
                    participants: contact.groupMetadata?.participants || []
                } : null,
                profilePicUrl: contact.profilePicUrl || null
            }));
        
        console.log(`Found ${formattedContacts.length} contacts for device: ${deviceKey}`);
        
        res.json({
            success: true,
            contacts: formattedContacts,
            count: formattedContacts.length
        });
    } catch (error) {
        console.error(`Error getting contacts for ${deviceKey}:`, error);
        res.json({
            success: false,
            message: error.message
        });
    }
});

// Socket.IO connection handling
io.on('connection', (socket) => {
    console.log('Client connected:', socket.id);
    
    socket.on('join-device', (deviceKey) => {
        socket.join(`device-${deviceKey}`);
        console.log(`Socket ${socket.id} joined device room: ${deviceKey}`);
    });
    
    socket.on('disconnect', () => {
        console.log('Client disconnected:', socket.id);
    });
});

// Health check
app.get('/health', (req, res) => {
    res.json({
        status: 'OK',
        timestamp: new Date().toISOString(),
        activeClients: clients.size,
        activeQrCodes: qrCodes.size
    });
});

// Start server
const PORT = process.env.WHATSAPP_PORT || 3001;
server.listen(PORT, () => {
    console.log(`WhatsApp Server running on port ${PORT}`);
    
    // Create sessions directory if it doesn't exist
    const sessionsDir = path.join(__dirname, 'wa-sessions');
    if (!fs.existsSync(sessionsDir)) {
        fs.mkdirSync(sessionsDir, { recursive: true });
        console.log('Created wa-sessions directory');
    }
});

// Graceful shutdown
process.on('SIGINT', async () => {
    console.log('Shutting down WhatsApp server...');
    
    // Disconnect all clients
    for (const [deviceKey, client] of clients) {
        try {
            await client.destroy();
            console.log(`Disconnected client: ${deviceKey}`);
        } catch (error) {
            console.error(`Error disconnecting ${deviceKey}:`, error);
        }
    }
    
    process.exit(0);
}); 