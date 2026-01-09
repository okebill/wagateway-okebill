const { Client, LocalAuth, MessageMedia } = require('whatsapp-web.js');
const express = require('express');
const http = require('http');
const socketIo = require('socket.io');
const qrcode = require('qrcode-terminal');
const fs = require('fs');
const path = require('path');
const axios = require('axios');

// Ensure logs directory exists
const logsDir = path.join(__dirname, 'logs');
if (!fs.existsSync(logsDir)) {
    try {
        fs.mkdirSync(logsDir, { recursive: true });
        console.log(`[INIT] Created logs directory: ${logsDir}`);
    } catch (err) {
        console.error(`[INIT] Failed to create logs directory: ${err.message}`);
    }
}

// Also ensure Laravel storage/logs directory exists
const laravelLogsDir = path.join(__dirname, 'storage', 'logs');
if (!fs.existsSync(laravelLogsDir)) {
    try {
        fs.mkdirSync(laravelLogsDir, { recursive: true });
        console.log(`[INIT] Created Laravel logs directory: ${laravelLogsDir}`);
    } catch (err) {
        console.error(`[INIT] Failed to create Laravel logs directory: ${err.message}`);
    }
}

const logFile = path.join(logsDir, 'whatsapp-server.log');
const errorLogFile = path.join(logsDir, 'whatsapp-error.log');
const laravelLogFile = path.join(laravelLogsDir, 'whatsapp-server.log');
const laravelErrorLogFile = path.join(laravelLogsDir, 'whatsapp-error.log');

// Write initial log entry to both locations
const initialLogMessage = `\n\n=== Server Started at ${new Date().toISOString()} ===\n`;
try {
    fs.appendFileSync(logFile, initialLogMessage, 'utf8');
} catch (err) {
    console.error(`[INIT] Failed to write initial log to ${logFile}: ${err.message}`);
}

try {
    fs.appendFileSync(laravelLogFile, initialLogMessage, 'utf8');
} catch (err) {
    console.error(`[INIT] Failed to write initial log to ${laravelLogFile}: ${err.message}`);
}

// Write initial log entry to error logs
try {
    fs.appendFileSync(errorLogFile, initialLogMessage, 'utf8');
} catch (err) {
    console.error(`[INIT] Failed to write initial log to ${errorLogFile}: ${err.message}`);
}

try {
    fs.appendFileSync(laravelErrorLogFile, initialLogMessage, 'utf8');
} catch (err) {
    console.error(`[INIT] Failed to write initial log to ${laravelErrorLogFile}: ${err.message}`);
}

// Logging helper function
function log(message, type = 'info') {
    const timestamp = new Date().toISOString();
    const prefix = `[${timestamp}] [${type.toUpperCase()}]`;
    const logMessage = `${prefix} ${message}`;
    
    // Write to console (stdout/stderr) - PM2 will capture this
    // Use process.stdout.write for better PM2 compatibility
    switch(type) {
        case 'error':
            process.stderr.write(logMessage + '\n');
            break;
        case 'warn':
            process.stdout.write(logMessage + '\n');
            break;
        case 'success':
            // For success, still use stdout but with color (if terminal supports it)
            process.stdout.write(logMessage + '\n');
            break;
        default:
            process.stdout.write(logMessage + '\n');
    }
    
    // Write to main log files (all types)
    // Write to both logs directory and Laravel storage/logs directory
    try {
        fs.appendFileSync(logFile, logMessage + '\n', 'utf8');
    } catch (err) {
        // If file write fails, log error to stderr
        process.stderr.write(`[${timestamp}] [ERROR] Failed to write to log file (${logFile}): ${err.message}\n`);
    }
    
    // Also write to Laravel storage/logs directory
    try {
        fs.appendFileSync(laravelLogFile, logMessage + '\n', 'utf8');
    } catch (err) {
        // If Laravel log write fails, only log to stderr if it's a different error
        // (don't spam if both fail)
        if (err.code !== 'ENOENT') {
            process.stderr.write(`[${timestamp}] [ERROR] Failed to write to Laravel log file (${laravelLogFile}): ${err.message}\n`);
        }
    }
    
    // Write errors and warnings to separate error log files
    if (type === 'error' || type === 'warn') {
        try {
            fs.appendFileSync(errorLogFile, logMessage + '\n', 'utf8');
        } catch (err) {
            process.stderr.write(`[${timestamp}] [ERROR] Failed to write to error log file (${errorLogFile}): ${err.message}\n`);
        }
        
        try {
            fs.appendFileSync(laravelErrorLogFile, logMessage + '\n', 'utf8');
        } catch (err) {
            if (err.code !== 'ENOENT') {
                process.stderr.write(`[${timestamp}] [ERROR] Failed to write to Laravel error log file (${laravelErrorLogFile}): ${err.message}\n`);
            }
        }
    }
}

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
    log(`🚀 Initializing WhatsApp client for device: ${deviceKey}`, 'info');
    
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
        log(`📱 QR Code generated for device: ${deviceKey}`, 'success');
        log(`   QR Code length: ${qr.length} characters`, 'info');
        log(`   QR Code expires in: 5 minutes`, 'info');
        
        // Store QR code
        qrCodes.set(deviceKey, {
            qr: qr,
            timestamp: Date.now(),
            expires_at: Date.now() + (5 * 60 * 1000) // 5 minutes
        });
        
        log(`   QR Code stored in memory. Total active QR codes: ${qrCodes.size}`, 'info');
        
        // Send QR to specific device clients
        io.emit(`qr-${deviceKey}`, {
            qr: qr,
            deviceKey: deviceKey,
            timestamp: Date.now()
        });
        
        log(`   QR Code emitted via Socket.IO to: qr-${deviceKey}`, 'info');
        
        // Update device status
        updateDeviceStatus(deviceKey, 'connecting', 'Waiting for QR scan');
        
        // Generate QR code terminal output for debugging
        log(`   Displaying QR code in terminal:`, 'info');
        qrcode.generate(qr, { small: true });
    });

    // Client ready event
    client.on('ready', async () => {
        log(`✅ WhatsApp client is READY for device: ${deviceKey}`, 'success');
        
        // Wait a moment to ensure client.info is available
        await new Promise(resolve => setTimeout(resolve, 500));
        
        if (client.info) {
        const clientInfo = client.info;
            log(`   Connected as: ${clientInfo.pushname || 'Unknown'} (${clientInfo.wid.user})`, 'success');
            log(`   Platform: ${clientInfo.platform || 'Unknown'}`, 'info');
        
        // Update device status and info
            const deviceInfo = {
            pushname: clientInfo.pushname,
            phone: clientInfo.wid.user,
            platform: clientInfo.platform
            };
            
            updateDeviceStatus(deviceKey, 'connected', 'Connected successfully', deviceInfo);
        
        // Clear QR code
        qrCodes.delete(deviceKey);
            log(`   QR Code cleared from memory`, 'info');
        
            // Emit connection success via Socket.IO
        io.emit(`connected-${deviceKey}`, {
            deviceKey: deviceKey,
            phoneNumber: clientInfo.wid.user,
            pushname: clientInfo.pushname,
            connectedAt: new Date().toISOString()
        });
            
            log(`   Emitted Socket.IO event: connected-${deviceKey}`, 'info');
            
            // Also emit status update
            io.emit(`status-${deviceKey}`, {
                deviceKey: deviceKey,
                status: 'connected',
                message: 'Connected successfully',
                timestamp: new Date().toISOString(),
                deviceInfo: deviceInfo
            });
            
            log(`   Emitted Socket.IO event: status-${deviceKey}`, 'info');
            log(`✅ Connection events emitted successfully for ${deviceKey}`, 'success');
        } else {
            log(`   ⚠️  Client info not available yet for device: ${deviceKey}`, 'warn');
            log(`   Will retry in 1 second...`, 'info');
            
            // Retry after 1 second
            setTimeout(async () => {
                if (client.info) {
                    const clientInfo = client.info;
                    const deviceInfo = {
                        pushname: clientInfo.pushname,
                        phone: clientInfo.wid.user,
                        platform: clientInfo.platform
                    };
                    updateDeviceStatus(deviceKey, 'connected', 'Connected successfully', deviceInfo);
                    log(`   ✅ Client info retrieved on retry for device: ${deviceKey}`, 'success');
                } else {
                    log(`   ⚠️  Client info still not available for device: ${deviceKey}`, 'warn');
                    updateDeviceStatus(deviceKey, 'connected', 'Connected (info pending)');
                }
            }, 1000);
        }
    });

    // Authentication success
    client.on('authenticated', () => {
        log(`🔐 Client authenticated successfully for device: ${deviceKey}`, 'success');
        updateDeviceStatus(deviceKey, 'authenticated', 'Authentication successful');
    });

    // Authentication failure
    client.on('auth_failure', (msg) => {
        log(`❌ Authentication FAILED for device: ${deviceKey}`, 'error');
        log(`   Error message: ${msg}`, 'error');
        updateDeviceStatus(deviceKey, 'error', `Authentication failed: ${msg}`);
    });

    // Disconnected event
    client.on('disconnected', (reason) => {
        log(`🔌 Client DISCONNECTED for device: ${deviceKey}`, 'warn');
        log(`   Reason: ${reason}`, 'warn');
        updateDeviceStatus(deviceKey, 'disconnected', `Disconnected: ${reason}`);
        
        // Clean up
        clients.delete(deviceKey);
        qrCodes.delete(deviceKey);
        log(`   Cleaned up client and QR code for device: ${deviceKey}`, 'info');
        
        io.emit(`disconnected-${deviceKey}`, {
            deviceKey: deviceKey,
            reason: reason,
            disconnectedAt: new Date().toISOString()
        });
        
        log(`   Emitted Socket.IO event: disconnected-${deviceKey}`, 'info');
    });
    
    // Contact added event - log when new contacts are added
    client.on('contact_changed', async (contact, oldContact) => {
        log(`👤 Contact changed for device: ${deviceKey}`, 'info');
        log(`   ID: ${contact.id._serialized}`, 'info');
        log(`   Name: ${contact.name || contact.pushname || 'Unknown'}`, 'info');
    });
    
    // Message received event
    client.on('message', async (message) => {
        log(`💬 Message received for device: ${deviceKey}`, 'info');
        log(`   From: ${message.from}`, 'info');
        log(`   To: ${message.to}`, 'info');
        log(`   Body: ${message.body?.substring(0, 50)}${message.body?.length > 50 ? '...' : ''}`, 'info');
        log(`   Type: ${message.type}`, 'info');
        log(`   Is Group: ${message.isGroup}`, 'info');
        
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
        
        log(`   Message emitted via Socket.IO to: message-${deviceKey}`, 'info');
        
        // Save incoming message to Laravel database
        try {
            await saveIncomingMessage(deviceKey, message);
        } catch (saveError) {
            log(`   ⚠️  Failed to save incoming message: ${saveError.message}`, 'error');
        }
    });
    
    // Loading screen event
    client.on('loading_screen', (percent, message) => {
        log(`⏳ Loading screen for device: ${deviceKey} - ${percent}%: ${message}`, 'info');
    });
    
    // Change state event
    client.on('change_state', (state) => {
        log(`🔄 State changed for device: ${deviceKey} - New state: ${state}`, 'info');
        updateDeviceStatus(deviceKey, 'connecting', `State: ${state}`);
    });
    
    // Remote session saved (session already exists)
    client.on('remote_session_saved', () => {
        log(`💾 Remote session saved for device: ${deviceKey}`, 'info');
    });

    // Store client
    clients.set(deviceKey, client);
    
    // Initialize client
    try {
        log(`⏳ Starting client initialization for device: ${deviceKey}...`, 'info');
        await client.initialize();
        log(`✅ Client initialized successfully for device: ${deviceKey}`, 'success');
        
        // Check if client is already ready (session exists)
        // Wait a bit for client to be ready if session exists
        // Check multiple times because client.info might not be immediately available
        // Also set up a continuous polling mechanism
        let readyCheckInterval = null;
        
        const checkReady = (attempt = 1, maxAttempts = 20) => {
            if (client.info) {
                const currentStatus = deviceStatuses.get(deviceKey);
                // Only update if not already connected
                if (!currentStatus || currentStatus.status !== 'connected' || !currentStatus.deviceInfo) {
                    log(`✅ Client already ready (session exists) for device: ${deviceKey}`, 'success');
                    const clientInfo = client.info;
                    log(`   Connected as: ${clientInfo.pushname || 'Unknown'} (${clientInfo.wid.user})`, 'success');
                    
                    const deviceInfo = {
                        pushname: clientInfo.pushname,
                        phone: clientInfo.wid.user,
                        platform: clientInfo.platform
                    };
                    
                    updateDeviceStatus(deviceKey, 'connected', 'Connected successfully', deviceInfo);
                    
                    // Emit connection success
                    io.emit(`connected-${deviceKey}`, {
                        deviceKey: deviceKey,
                        phoneNumber: clientInfo.wid.user,
                        pushname: clientInfo.pushname,
                        connectedAt: new Date().toISOString()
                    });
                    
                    io.emit(`status-${deviceKey}`, {
                        deviceKey: deviceKey,
                        status: 'connected',
                        message: 'Connected successfully',
                        timestamp: new Date().toISOString(),
                        deviceInfo: deviceInfo
                    });
                    
                    log(`   ✅ Connection events emitted for device: ${deviceKey}`, 'success');
                    
                    // Clear interval if it exists
                    if (readyCheckInterval) {
                        clearInterval(readyCheckInterval);
                        readyCheckInterval = null;
                    }
                    return true; // Success
                }
                return true; // Already connected
            } else {
                if (attempt <= maxAttempts) {
                    // Try again after delay
                    log(`   ⏳ Attempt ${attempt}/${maxAttempts}: client.info not available yet, retrying...`, 'info');
                    setTimeout(() => checkReady(attempt + 1, maxAttempts), 500);
                } else {
                    log(`   ⚠️  Max attempts (${maxAttempts}) reached. Setting up continuous polling...`, 'warn');
                    log(`   This might indicate that event 'ready' is not firing. Investigating...`, 'warn');
                    
                    // Set up continuous polling every 2 seconds
                    let pollCount = 0;
                    const maxPollCount = 30; // Poll for max 60 seconds (30 * 2s)
                    const earlySuccessCount = 5; // Mark as connected after 10 seconds if state is CONNECTED
                    
                    readyCheckInterval = setInterval(async () => {
                        pollCount++;
                        
                        if (client.info) {
                            log(`   ✅ client.info became available during polling for device: ${deviceKey}`, 'success');
                            checkReady(1, 1); // Check once more
                            if (readyCheckInterval) {
                                clearInterval(readyCheckInterval);
                                readyCheckInterval = null;
                            }
                        } else {
                            // Check client state
                            try {
                                const state = await client.getState();
                                log(`   [Poll ${pollCount}/${maxPollCount}] Client state: ${state}, info available: ${!!client.info}`, 'info');
                                
                                // If state is CONNECTED, try alternative methods to get info
                                if ((state === 'CONNECTED' || state === 'READY') && !client.info) {
                                    log(`   ⚠️  State is ${state} but client.info not available. Trying alternative methods...`, 'warn');
                                    
                                    try {
                                        // Check if pupPage is available
                                        if (!client.pupPage) {
                                            log(`   ❌ client.pupPage is not available`, 'error');
                                        } else {
                                            log(`   ✅ client.pupPage is available, attempting to evaluate...`, 'info');
                                            
                                            // Try multiple methods to get phone number
                                            let phoneNumber = null;
                                            
                                            // Method 1: Try to get WID from puppeteer page with multiple paths
                                            try {
                                                phoneNumber = await client.pupPage.evaluate(() => {
                                                    // Log what's available for debugging
                                                    const available = {
                                                        Store: !!window.Store,
                                                        StoreConn: !!(window.Store && window.Store.Conn),
                                                        WWebJS: !!window.WWebJS,
                                                        WAWeb: !!(window.WAWebBackend || window.WAWeb)
                                                    };
                                                    console.log('Available objects:', available);
                                                    
                                                    // Try multiple paths
                                                    if (window.Store && window.Store.Conn && window.Store.Conn.wid) {
                                                        return window.Store.Conn.wid.user || window.Store.Conn.wid._serialized.split('@')[0];
                                                    }
                                                    
                                                    if (window.Store && window.Store.Conn && window.Store.Conn.me) {
                                                        return window.Store.Conn.me.user || window.Store.Conn.me._serialized.split('@')[0];
                                                    }
                                                    
                                                    if (window.WWebJS && window.WWebJS.phoneInfo) {
                                                        return window.WWebJS.phoneInfo.wid.user;
                                                    }
                                                    
                                                    // Try from localStorage
                                                    const localData = localStorage.getItem('WASecretBundle');
                                                    if (localData) {
                                                        try {
                                                            const data = JSON.parse(localData);
                                                            if (data.wid) return data.wid.split('@')[0];
                                                        } catch (e) {}
                                                    }
                                                    
                                                    return null;
                                                });
                                            } catch (evalErr) {
                                                log(`   ⚠️  Evaluation error: ${evalErr.message}`, 'warn');
                                            }
                                            
                                            // Method 2: Try using getContactById on 'me'
                                            if (!phoneNumber) {
                                                try {
                                                    log(`   🔄 Trying Method 2: getContactById('me')...`, 'info');
                                                    const meContact = await client.getContactById('me');
                                                    if (meContact && meContact.id && meContact.id.user) {
                                                        phoneNumber = meContact.id.user;
                                                        log(`   ✅ Got phone from getContactById: ${phoneNumber}`, 'success');
                                                    }
                                                } catch (contactErr) {
                                                    log(`   ⚠️  getContactById error: ${contactErr.message}`, 'warn');
                                                }
                                            }
                                            
                                            // Method 3: Try to get from session data
                                            if (!phoneNumber) {
                                                try {
                                                    log(`   🔄 Trying Method 3: Reading from wa-sessions...`, 'info');
                                                    const sessionPath = path.join(__dirname, 'wa-sessions', `session-${deviceKey}`);
                                                    if (fs.existsSync(sessionPath)) {
                                                        // Try to find phone number in session files
                                                        const sessionFiles = fs.readdirSync(sessionPath);
                                                        for (const file of sessionFiles) {
                                                            if (file.includes('Default') && file.includes('Local Storage')) {
                                                                const filePath = path.join(sessionPath, file, 'leveldb');
                                                                if (fs.existsSync(filePath)) {
                                                                    // Session files exist, phone likely in memory
                                                                    log(`   ℹ️  Session files found but phone not extractable`, 'info');
                                                                    break;
                                                                }
                                                            }
                                                        }
                                                    }
                                                } catch (sessionErr) {
                                                    log(`   ⚠️  Session reading error: ${sessionErr.message}`, 'warn');
                                                }
                                            }
                                            
                                            log(`   📞 Final evaluation result: ${phoneNumber ? phoneNumber : 'null'}`, 'info');
                                            
                                            if (phoneNumber) {
                                                log(`   ✅ Retrieved phone number via alternative method: ${phoneNumber}`, 'success');
                                                
                                                const deviceInfo = {
                                                    pushname: 'WhatsApp User',
                                                    phone: phoneNumber,
                                                    platform: 'unknown'
                                                };
                                                
                                                updateDeviceStatus(deviceKey, 'connected', 'Connected successfully', deviceInfo);
                                                
                                                // Emit connection success
                                                io.emit(`connected-${deviceKey}`, {
                                                    deviceKey: deviceKey,
                                                    phoneNumber: phoneNumber,
                                                    pushname: 'WhatsApp User',
                                                    connectedAt: new Date().toISOString()
                                                });
                                                
                                                io.emit(`status-${deviceKey}`, {
                                                    deviceKey: deviceKey,
                                                    status: 'connected',
                                                    message: 'Connected successfully',
                                                    timestamp: new Date().toISOString(),
                                                    deviceInfo: deviceInfo
                                                });
                                                
                                                log(`   ✅ Connection events emitted for device: ${deviceKey}`, 'success');
                                                
                                                // Clear interval
                                                if (readyCheckInterval) {
                                                    clearInterval(readyCheckInterval);
                                                    readyCheckInterval = null;
                                                }
                                            } else if (pollCount === earlySuccessCount) {
                                                // After earlySuccessCount attempts with CONNECTED state, mark as connected
                                                // This provides better UX than waiting full 60 seconds
                                                log(`   ℹ️  State is CONNECTED for ${pollCount} attempts. Marking as connected...`, 'info');
                                                log(`   ⚠️  Phone number unavailable due to WhatsApp Web API limitation`, 'warn');
                                                log(`   💡 Tip: Device is functional and can send/receive messages`, 'info');
                                                
                                                const message = 'Connected successfully';
                                                const deviceInfo = null; // No phone info available due to bug
                                                
                                                updateDeviceStatus(deviceKey, 'connected', message, deviceInfo);
                                                
                                                // Emit connection success event (even without phone number)
                                                io.emit(`connected-${deviceKey}`, {
                                                    deviceKey: deviceKey,
                                                    phoneNumber: null,
                                                    pushname: null,
                                                    connectedAt: new Date().toISOString(),
                                                    note: 'Phone number unavailable - WhatsApp Web API limitation'
                                                });
                                                
                                                io.emit(`status-${deviceKey}`, {
                                                    deviceKey: deviceKey,
                                                    status: 'connected',
                                                    message: message,
                                                    timestamp: new Date().toISOString(),
                                                    deviceInfo: deviceInfo
                                                });
                                                
                                                log(`   ✅ Marked as connected after ${pollCount} attempts`, 'success');
                                                log(`   ✅ Connection events emitted for device: ${deviceKey}`, 'success');
                                                
                                                // Clear interval - no point in continuing to poll
                                                if (readyCheckInterval) {
                                                    clearInterval(readyCheckInterval);
                                                    readyCheckInterval = null;
                                                    log(`   ⏹️  Stopped monitoring (device is connected)`, 'info');
                                                }
                                            }
                                        }
                                    } catch (altErr) {
                                        log(`   ❌ Alternative method failed: ${altErr.message}`, 'error');
                                        log(`   ❌ Stack trace: ${altErr.stack}`, 'error');
                                        
                                        // If max poll count reached, force disconnect/reconnect
                                        if (pollCount >= maxPollCount) {
                                            log(`   ❌ Client stuck in ${state} without info. Forcing reconnection...`, 'error');
                                            if (readyCheckInterval) {
                                                clearInterval(readyCheckInterval);
                                                readyCheckInterval = null;
                                            }
                                            
                                            // Destroy and remove client
                                            try {
                                                await client.destroy();
                                                clients.delete(deviceKey);
                                                updateDeviceStatus(deviceKey, 'error', 'Client stuck, please reconnect');
                                            } catch (destroyErr) {
                                                log(`   Error destroying stuck client: ${destroyErr.message}`, 'error');
                                            }
                                        }
                                    }
                                }
                            } catch (stateErr) {
                                log(`   Error getting state during poll: ${stateErr.message}`, 'warn');
                            }
                            
                            // Stop polling after max attempts
                            if (pollCount >= maxPollCount) {
                                log(`   ⚠️  Max polling count reached. Stopping...`, 'warn');
                                if (readyCheckInterval) {
                                    clearInterval(readyCheckInterval);
                                    readyCheckInterval = null;
                                }
                            }
                        }
                    }, 2000);
                }
                return false; // Not ready yet
            }
        };
        
        // Start checking after initial delay
        setTimeout(() => checkReady(), 1000);
    } catch (error) {
        log(`❌ Failed to initialize client for device: ${deviceKey}`, 'error');
        log(`   Error: ${error.message}`, 'error');
        log(`   Stack: ${error.stack}`, 'error');
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
    
    log(`📊 Status updated for device: ${deviceKey}`, 'info');
    log(`   Status: ${status}`, 'info');
    log(`   Message: ${message}`, 'info');
    if (deviceInfo) {
        log(`   Phone: ${deviceInfo.phone || 'N/A'}`, 'info');
        log(`   Name: ${deviceInfo.pushname || 'N/A'}`, 'info');
    }
}

// Helper function to check if number exists on WhatsApp and get proper ID
async function getNumberId(client, chatId) {
    log(`🔍 Checking if number exists on WhatsApp: ${chatId}`, 'info');
    
    try {
        // Method 1: Try getNumberId (most reliable for WhatsApp Web.js)
        try {
            const numberId = await client.getNumberId(chatId.replace('@c.us', ''));
            if (numberId) {
                log(`   ✅ Number is registered on WhatsApp: ${numberId._serialized}`, 'success');
                return numberId._serialized;
            }
        } catch (err) {
            log(`   ⚠️  getNumberId failed: ${err.message}`, 'warn');
        }
        
        // Method 2: Try isRegisteredUser
        try {
            const isRegistered = await client.isRegisteredUser(chatId);
            if (isRegistered) {
                log(`   ✅ Number is registered (via isRegisteredUser)`, 'success');
                return chatId;
            } else {
                log(`   ❌ Number is NOT registered on WhatsApp`, 'error');
                return null;
            }
        } catch (err) {
            log(`   ⚠️  isRegisteredUser failed: ${err.message}`, 'warn');
        }
        
        // Method 3: Try to get contact
        try {
            const contact = await client.getContactById(chatId);
            if (contact) {
                log(`   ✅ Contact found: ${contact.id._serialized}`, 'success');
                return contact.id._serialized;
            }
        } catch (err) {
            log(`   ⚠️  getContactById failed: ${err.message}`, 'warn');
        }
        
        // If all methods fail, return original chatId and let send fail gracefully
        log(`   ⚠️  Could not verify number, returning original ID: ${chatId}`, 'warn');
        return chatId;
        
    } catch (error) {
        log(`   ❌ Error in getNumberId: ${error.message}`, 'error');
        return chatId; // Return original and let send handle the error
    }
}

// Helper function to ensure contact exists before sending
async function ensureContact(client, chatId) {
    log(`🔐 Ensuring contact exists: ${chatId}`, 'info');
    
    try {
        // Get verified number ID first
        const verifiedId = await getNumberId(client, chatId);
        
        if (!verifiedId) {
            log(`   ❌ Number not registered on WhatsApp`, 'error');
            return null;
        }
        
        // Try to get or create contact
        try {
            const contact = await client.getContactById(verifiedId);
            if (contact) {
                log(`   ✅ Contact exists: ${contact.name || contact.pushname || 'Unknown'}`, 'success');
                
                // Check if contact has LID issue
                // Sometimes contact exists but LID is not available
                // In this case, try to "ping" the contact by getting chat
                try {
                    const chat = await client.getChatById(verifiedId);
                    if (chat) {
                        log(`   ✅ Chat accessible, LID should be available`, 'success');
                    }
                } catch (chatErr) {
                    log(`   ⚠️  Chat not accessible yet: ${chatErr.message}`, 'warn');
                    log(`   💡 This might cause LID error, but will try anyway`, 'info');
                }
                
                return verifiedId;
            }
        } catch (contactErr) {
            log(`   ⚠️  Could not get contact: ${contactErr.message}`, 'warn');
        }
        
        return verifiedId;
        
    } catch (error) {
        log(`   ❌ Error in ensureContact: ${error.message}`, 'error');
        return chatId; // Return original and let send handle
    }
}

// Save incoming message to Laravel database
async function saveIncomingMessage(deviceKey, message) {
    log(`💾 Attempting to save incoming message...`, 'info');
    log(`   Device: ${deviceKey}`, 'info');
    
    try {
        log(`   Message ID: ${message.id?.id || 'N/A'}`, 'info');
        log(`   From: ${message.from}`, 'info');
        log(`   To: ${message.to}`, 'info');
        log(`   FromMe: ${message.fromMe}`, 'info');
        
        // Normalize chat_id - convert @lid to @c.us or @g.us
        let normalizedChatId = message.from;
        if (normalizedChatId && normalizedChatId.includes('@lid')) {
            // Convert @lid to @c.us for individual chats
            normalizedChatId = normalizedChatId.replace('@lid', '@c.us');
            log(`   🔄 Normalized chat_id from ${message.from} to ${normalizedChatId}`, 'info');
        }
        
        // For incoming messages, chat_id should be the sender (from)
        // For outgoing messages, chat_id should be the recipient (to)
        const chatId = message.fromMe ? message.to : normalizedChatId;
        
        // Prepare message data
        const messageData = {
            device_key: deviceKey,
            message_id: message.id?.id || `msg_${Date.now()}`,
            chat_id: chatId,
            direction: message.fromMe ? 'outgoing' : 'incoming',
            type: message.type || 'text',
            status: 'received',
            content: message.body || '',
            from_number: message.from,
            to_number: message.to,
            is_group: message.isGroup || false,
            timestamp: message.timestamp || Math.floor(Date.now() / 1000),
            metadata: {
                hasMedia: message.hasMedia || false,
                hasQuotedMsg: message.hasQuotedMsg || false,
                author: message.author || null,
            }
        };
        
        log(`   📝 Final chat_id: ${chatId}`, 'info');
        
        log(`   Sending to Laravel API...`, 'info');
        log(`   Method: POST`, 'info');
        log(`   Data keys: ${Object.keys(messageData).join(', ')}`, 'info');
        log(`   Data preview: ${JSON.stringify(messageData).substring(0, 200)}...`, 'info');
        
        // Try multiple URL options: localhost first (if same server), then HTTPS, then HTTP
        const apiUrls = [
            'http://localhost:8000/api/whatsapp/save-incoming-message', // Same server
            'http://127.0.0.1:8000/api/whatsapp/save-incoming-message', // Same server alternative
            'https://wa.okebil.com/api/whatsapp/save-incoming-message', // HTTPS
            'http://wa.okebil.com/api/whatsapp/save-incoming-message'  // HTTP fallback
        ];
        
        let apiUrl = apiUrls[0];
        let lastError = null;
        
        // Try each URL until one works
        for (let i = 0; i < apiUrls.length; i++) {
            apiUrl = apiUrls[i];
            log(`   Trying URL ${i + 1}/${apiUrls.length}: ${apiUrl}`, 'info');
            
            try {
                const response = await axios({
                    method: 'POST',
                    url: apiUrl,
                    data: messageData,
                    timeout: 10000,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'User-Agent': 'WhatsApp-Server-NodeJS/1.0',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    maxRedirects: apiUrl.startsWith('http://') ? 5 : 0, // Allow redirects for HTTP only
                    validateStatus: function (status) {
                        return status >= 200 && status < 500; // Accept 2xx, 3xx, 4xx for debugging
                    }
                });
                
                log(`   ✅ Response received from ${apiUrl}: ${response.status}`, 'info');
                
                if (response.status === 200 || response.status === 201) {
                    if (response.data && response.data.success) {
                        log(`   ✅ Message saved successfully (DB ID: ${response.data.message_id})`, 'success');
                        return; // Success, exit function
                    } else {
                        log(`   ⚠️  Message save returned success=false: ${response.data?.message || 'Unknown error'}`, 'warn');
                        log(`   Full response: ${JSON.stringify(response.data)}`, 'warn');
                        lastError = new Error(`Save failed: ${response.data?.message || 'Unknown error'}`);
                        // Continue to next URL only if not 419 (CSRF) - might work with different URL
                        if (response.status !== 419) {
                            continue;
                        }
                    }
                } else if (response.status === 419) {
                    log(`   ⚠️  CSRF token mismatch (419) - This might be fixed after Laravel config update`, 'warn');
                    log(`   Response: ${JSON.stringify(response.data)}`, 'warn');
                    lastError = new Error(`CSRF token mismatch: ${response.status}`);
                    continue; // Try next URL
                } else if (response.status === 405) {
                    log(`   ❌ Method Not Allowed (405) - Request method was ${response.config?.method || 'UNKNOWN'}`, 'error');
                    log(`   This URL doesn't work, trying next...`, 'warn');
                    lastError = new Error(`Method Not Allowed: ${response.status}`);
                    continue; // Try next URL
                } else {
                    log(`   ⚠️  Unexpected status code: ${response.status}`, 'warn');
                    log(`   Response data: ${JSON.stringify(response.data)}`, 'warn');
                    lastError = new Error(`Unexpected status: ${response.status}`);
                    continue; // Try next URL
                }
            } catch (urlError) {
                log(`   ⚠️  URL ${i + 1} failed: ${urlError.message}`, 'warn');
                lastError = urlError;
                
                // If it's a connection error and we have more URLs, continue
                if (i < apiUrls.length - 1) {
                    continue;
                } else {
                    // Last URL failed, throw error
                    throw urlError;
                }
            }
        }
        
        // If we get here, all URLs failed
        throw lastError || new Error('All API URLs failed');
    } catch (error) {
        log(`   ❌ Failed to save incoming message: ${error.message}`, 'error');
        log(`   Error name: ${error.name}`, 'error');
        log(`   Error code: ${error.code || 'N/A'}`, 'error');
        
        if (error.response) {
            log(`   HTTP Status: ${error.response.status}`, 'error');
            log(`   Response data: ${JSON.stringify(error.response.data)}`, 'error');
            log(`   Response headers: ${JSON.stringify(error.response.headers)}`, 'error');
            
            // Check if it's a method error
            if (error.response.status === 405) {
                log(`   ❌ METHOD NOT ALLOWED - Request was sent as ${error.config?.method || 'UNKNOWN'}`, 'error');
                log(`   ❌ Expected: POST`, 'error');
                log(`   ❌ This usually means the request method was changed by a redirect or proxy`, 'error');
            }
        } else if (error.request) {
            log(`   No response received from server`, 'error');
            log(`   Request config: ${JSON.stringify(error.config)}`, 'error');
        } else {
            log(`   Error stack: ${error.stack}`, 'error');
        }
    }
}

// API Routes

// Get QR Code for device
app.get('/api/qr/:deviceKey', (req, res) => {
    const { deviceKey } = req.params;
    log(`📥 GET /api/qr/${deviceKey} - Request received`, 'info');
    
    const qrData = qrCodes.get(deviceKey);
    
    if (qrData && qrData.expires_at > Date.now()) {
        const timeLeft = Math.floor((qrData.expires_at - Date.now()) / 1000);
        log(`   ✅ QR Code found for device: ${deviceKey}`, 'success');
        log(`   ⏰ Time remaining: ${timeLeft} seconds`, 'info');
        
        res.json({
            success: true,
            qr: qrData.qr,
            expires_at: qrData.expires_at,
            timestamp: qrData.timestamp
        });
    } else {
        log(`   ❌ No QR code available or expired for device: ${deviceKey}`, 'warn');
        if (qrData) {
            log(`   ⏰ QR Code expired ${Math.floor((Date.now() - qrData.expires_at) / 1000)} seconds ago`, 'warn');
        }
        
        res.json({
            success: false,
            message: 'No QR code available or expired'
        });
    }
});

// Initialize device connection
app.post('/api/device/:deviceKey/connect', async (req, res) => {
    const { deviceKey } = req.params;
    log(`📥 POST /api/device/${deviceKey}/connect - Connection request received`, 'info');
    
    try {
        if (clients.has(deviceKey)) {
            log(`   ⚠️  Device ${deviceKey} already exists in clients map`, 'warn');
            const existingClient = clients.get(deviceKey);
            if (existingClient && existingClient.info) {
                log(`   ℹ️  Device is already connected: ${existingClient.info.wid.user}`, 'info');
            } else {
                log(`   ℹ️  Device is already connecting...`, 'info');
            }
            
            res.json({
                success: false,
                message: 'Device already connecting or connected'
            });
            return;
        }
        
        log(`   🚀 Starting connection initialization for device: ${deviceKey}`, 'info');
        await initializeWhatsAppClient(deviceKey);
        
        log(`   ✅ Connection initiated successfully for device: ${deviceKey}`, 'success');
        res.json({
            success: true,
            message: 'Connection initiated. Please scan QR code.',
            deviceKey: deviceKey
        });
    } catch (error) {
        log(`   ❌ Connection initialization failed for device: ${deviceKey}`, 'error');
        log(`   Error: ${error.message}`, 'error');
        res.json({
            success: false,
            message: error.message
        });
    }
});

// Disconnect device
app.post('/api/device/:deviceKey/disconnect', async (req, res) => {
    const { deviceKey } = req.params;
    log(`📥 POST /api/device/${deviceKey}/disconnect - Disconnect request received`, 'info');
    
    try {
        const client = clients.get(deviceKey);
        if (client) {
            log(`   🔌 Disconnecting client for device: ${deviceKey}`, 'info');
            await client.destroy();
            clients.delete(deviceKey);
            qrCodes.delete(deviceKey);
            updateDeviceStatus(deviceKey, 'disconnected', 'Manually disconnected');
            log(`   ✅ Device ${deviceKey} disconnected successfully`, 'success');
        } else {
            log(`   ⚠️  No client found for device: ${deviceKey}`, 'warn');
        }
        
        res.json({
            success: true,
            message: 'Device disconnected successfully'
        });
    } catch (error) {
        log(`   ❌ Error disconnecting device ${deviceKey}: ${error.message}`, 'error');
        res.json({
            success: false,
            message: error.message
        });
    }
});

// Get device status
app.get('/api/device/:deviceKey/status', async (req, res) => {
    const { deviceKey } = req.params;
    log(`📥 GET /api/device/${deviceKey}/status - Status check request`, 'info');
    
    const client = clients.get(deviceKey);
    const status = deviceStatuses.get(deviceKey);
    
    // If client exists, always check actual client status first
    if (client) {
        // Check if client.info is available (client is ready/connected)
        if (client.info) {
            // Client is connected
            const clientInfo = client.info;
            log(`   ✅ Device ${deviceKey} is CONNECTED (client.info available)`, 'success');
            log(`   Phone: ${clientInfo.wid.user}`, 'info');
            log(`   Name: ${clientInfo.pushname || 'Unknown'}`, 'info');
            
            const statusData = {
                success: true,
                deviceKey: deviceKey,
                status: 'connected',
                message: 'Connected successfully',
                timestamp: new Date().toISOString(),
                deviceInfo: {
                    pushname: clientInfo.pushname,
                    phone: clientInfo.wid.user,
                    platform: clientInfo.platform
                }
            };
            
            // Update deviceStatuses cache
            deviceStatuses.set(deviceKey, statusData);
            
            // Emit status update via Socket.IO
            io.emit(`status-${deviceKey}`, statusData);
            
            res.json(statusData);
            return;
        } else {
            // Client exists but not ready yet (still connecting/authenticating)
            log(`   ⏳ Device ${deviceKey} is CONNECTING (client exists but client.info not available)`, 'info');
            
            // Check if client state is CONNECTED/READY
            try {
                if (typeof client.getState === 'function') {
                    const state = await client.getState();
                    log(`   Client getState(): ${state}`, 'info');
                    
                    // If state is CONNECTED or READY, treat as connected even without client.info
                    if (state === 'CONNECTED' || state === 'READY') {
                        log(`   ✅ State is ${state}, treating as connected`, 'success');
                        
                        // Try to get cached device info if available
                        let deviceInfo = status && status.deviceInfo ? status.deviceInfo : null;
                        
                        const statusData = {
                            success: true,
                            deviceKey: deviceKey,
                            status: 'connected',
                            message: deviceInfo ? 'Connected successfully' : 'Connected (phone number pending)',
                            timestamp: new Date().toISOString(),
                            deviceInfo: deviceInfo
                        };
                        
                        // Update cache
                        deviceStatuses.set(deviceKey, statusData);
                        
                        // Emit status update
                        io.emit(`status-${deviceKey}`, statusData);
                        
                        res.json(statusData);
                        return;
                    }
                }
            } catch (err) {
                log(`   Error checking client state: ${err.message}`, 'warn');
            }
            
            // If we have cached status, use it, otherwise return connecting
            if (status && status.status !== 'connected') {
                log(`   ℹ️  Returning cached status: ${status.status}`, 'info');
                res.json({
                    success: true,
                    ...status
                });
            } else {
                res.json({
                    success: true,
                    deviceKey: deviceKey,
                    status: 'connecting',
                    message: 'Waiting for QR scan or authentication',
                    timestamp: new Date().toISOString()
                });
            }
            return;
        }
    }
    
    // No client found
    if (status) {
        // Return cached status
        log(`   ℹ️  Returning cached status for device: ${deviceKey}`, 'info');
        log(`   Status: ${status.status} - ${status.message}`, 'info');
        res.json({
            success: true,
            ...status
        });
    } else {
        log(`   ❌ Device ${deviceKey} is DISCONNECTED (not initialized)`, 'warn');
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
    const { to, message, number } = req.body;
    
    // Support both 'to' and 'number' parameters
    const recipient = to || number;
    
    log(`📤 POST /api/device/${deviceKey}/send-message - Send message request`, 'info');
    log(`   📞 Recipient: ${recipient}`, 'info');
    log(`   📝 Message length: ${message?.length || 0} characters`, 'info');
    
    try {
        log(`   🔍 Looking for client...`, 'info');
        const client = clients.get(deviceKey);
        
        if (!client) {
            log(`   ❌ Device ${deviceKey} not found in clients map`, 'error');
            res.json({
                success: false,
                message: 'Device not initialized'
            });
            return;
        }
        
        log(`   ✅ Client found, checking state...`, 'info');
        
        // Check state instead of relying on client.info
        try {
            const state = await client.getState();
            log(`   📊 Device state: ${state}`, 'info');
            
            if (state !== 'CONNECTED' && state !== 'READY') {
                log(`   ❌ Device not connected. State: ${state}`, 'error');
                res.json({
                    success: false,
                    message: `Device not connected. Current state: ${state}`
                });
                return;
            }
            
            log(`   ✅ Device is ${state}, proceeding to send message...`, 'success');
            
        } catch (stateErr) {
            log(`   ❌ Error getting state: ${stateErr.message}`, 'error');
            res.json({
                success: false,
                message: 'Cannot determine device state'
            });
            return;
        }
        
        // Format chat ID - normalize to ensure correct format
        let chatId = recipient;
        
        // If recipient doesn't have @, add @c.us
        if (!chatId.includes('@')) {
            chatId = `${chatId}@c.us`;
        }
        
        // Normalize @lid to @c.us if present
        if (chatId.includes('@lid')) {
            chatId = chatId.replace('@lid', '@c.us');
            log(`   🔄 Normalized chat_id from ${recipient} to ${chatId}`, 'info');
        }
        
        log(`   📱 Initial chat ID: ${chatId}`, 'info');
        
        // Validate chat ID format (more flexible to handle various formats)
        // Allow: number@c.us, number@g.us, or any format with @
        if (!chatId.includes('@')) {
            log(`   ❌ Invalid chat ID format: ${chatId}`, 'error');
            throw new Error(`Invalid chat ID format: ${chatId}. Must include @`);
        }
        
        // **FIX FOR "No LID for user" ERROR**
        // Ensure contact exists and get verified ID before sending
        // This prevents LID errors by verifying number is registered
        log(`   🔐 Verifying number and ensuring contact exists...`, 'info');
        const verifiedChatId = await ensureContact(client, chatId);
        
        if (!verifiedChatId) {
            log(`   ❌ Number is not registered on WhatsApp: ${chatId}`, 'error');
            throw new Error(`Number is not registered on WhatsApp: ${recipient}`);
        }
        
        // Use verified chat ID for sending
        chatId = verifiedChatId;
        log(`   ✅ Using verified chat ID: ${chatId}`, 'success');
        
        // Send message with retry
        let sentMessage;
        let retries = 0;
        const maxRetries = 3;
        
        while (retries < maxRetries) {
            try {
                log(`   📨 Attempt ${retries + 1}/${maxRetries} to send message to ${chatId}...`, 'info');
                log(`   📝 Message length: ${message.length} characters`, 'info');
                
                // Use sendMessage with proper error handling
                sentMessage = await client.sendMessage(chatId, message);
                
                log(`   ✅ Message sent successfully!`, 'success');
                log(`   📬 Message ID: ${sentMessage.id?.id || 'N/A'}`, 'info');
                log(`   ⏰ Timestamp: ${sentMessage.timestamp || 'N/A'}`, 'info');
                break; // Success, exit loop
            } catch (sendErr) {
                retries++;
                log(`   ⚠️  Attempt ${retries} failed: ${sendErr.message}`, 'warn');
                log(`   ⚠️  Error name: ${sendErr.name}`, 'warn');
                
                // Check for specific error types
                const errorMessage = sendErr.message.toLowerCase();
                
                // Check if it's the "No LID for user" error
                if (errorMessage.includes('no lid for user')) {
                    log(`   ⚠️  LID error detected - Number might not be in contacts or WhatsApp cache`, 'warn');
                    log(`   💡 Possible causes:`, 'info');
                    log(`      1. Number not in WhatsApp contacts`, 'info');
                    log(`      2. Privacy settings prevent messages from non-contacts`, 'info');
                    log(`      3. WhatsApp Web session needs to sync contacts`, 'info');
                    
                    // Try to force refresh contact before retry
                    if (retries < maxRetries) {
                        log(`   🔄 Attempting to refresh contact before retry...`, 'info');
                        try {
                            // Try to get contact again to refresh cache
                            const contact = await client.getContactById(chatId);
                            if (contact) {
                                log(`   ✅ Contact refreshed: ${contact.name || contact.pushname}`, 'success');
                            }
                        } catch (refreshErr) {
                            log(`   ⚠️  Contact refresh failed: ${refreshErr.message}`, 'warn');
                        }
                    }
                } else if (errorMessage.includes('evaluation failed')) {
                    log(`   ⚠️  Browser evaluation error - might be temporary`, 'warn');
                    log(`   💡 This could indicate WhatsApp Web is still loading or chat doesn't exist`, 'info');
                } else if (errorMessage.includes('phone number is not registered')) {
                    log(`   ❌ CRITICAL: Number is not registered on WhatsApp`, 'error');
                    throw new Error(`Number ${recipient} is not registered on WhatsApp`);
                }
                
                if (retries >= maxRetries) {
                    log(`   ❌ Max retries reached. Send message failed`, 'error');
                    
                    // Provide helpful error message based on error type
                    if (errorMessage.includes('no lid for user')) {
                        throw new Error(`Failed to send message: Number ${recipient} might not be in contacts or has privacy settings that prevent messages. Please ensure: 1) Number is registered on WhatsApp, 2) Add number to contacts first, or 3) Recipient must message you first.`);
                    }
                    
                    throw new Error(`Failed to send message after ${maxRetries} attempts: ${sendErr.message}`);
                }
                
                // Wait before retry (exponential backoff)
                const waitTime = Math.min(2000 * retries, 5000); // Max 5 seconds
                log(`   ⏳ Waiting ${waitTime}ms before retry...`, 'info');
                await new Promise(resolve => setTimeout(resolve, waitTime));
            }
        }
        
        res.json({
            success: true,
            messageId: sentMessage.id.id,
            timestamp: sentMessage.timestamp,
            to: recipient
        });
    } catch (error) {
        log(`   ❌ Error sending message for ${deviceKey}: ${error.message}`, 'error');
        log(`   Stack trace: ${error.stack}`, 'error');
        res.json({
            success: false,
            message: error.message
        });
    }
});

// Get contacts
app.get('/api/device/:deviceKey/contacts', async (req, res) => {
    const { deviceKey } = req.params;
    log(`📥 GET /api/device/${deviceKey}/contacts - Contacts sync request`, 'info');
    
    try {
        log(`   🔍 Looking for client in map...`, 'info');
        const client = clients.get(deviceKey);
        log(`   🔍 Client found: ${!!client}`, 'info');
        
        if (!client) {
            log(`   ❌ Device ${deviceKey} not found in clients map`, 'error');
            log(`   📋 Available clients: ${Array.from(clients.keys()).join(', ')}`, 'info');
            res.json({
                success: false,
                message: 'Device not initialized'
            });
            return;
        }
        
        log(`   ✅ Client exists, checking state...`, 'info');
        
        // Check state instead of relying on client.info
        try {
            log(`   📊 Calling client.getState()...`, 'info');
            const state = await client.getState();
            log(`   📊 Device state: ${state}`, 'info');
            
            if (state !== 'CONNECTED' && state !== 'READY') {
                log(`   ❌ Device not connected. State: ${state}`, 'error');
                res.json({
                    success: false,
                    message: `Device not connected. Current state: ${state}`
                });
                return;
            }
            
            log(`   ✅ Device is ${state}, proceeding to get contacts...`, 'success');
            
        } catch (stateErr) {
            log(`   ❌ Error getting state: ${stateErr.message}`, 'error');
            res.json({
                success: false,
                message: 'Cannot determine device state'
            });
            return;
        }
        
        log(`   📞 Fetching contacts from WhatsApp for device: ${deviceKey}`, 'info');
        
        // Try to get contacts with retry mechanism
        let contacts = [];
        let retries = 0;
        const maxRetries = 3;
        
        while (retries < maxRetries) {
            try {
                log(`   🔄 Attempt ${retries + 1}/${maxRetries} to get contacts...`, 'info');
                contacts = await client.getContacts();
                log(`   ✅ Retrieved ${contacts.length} raw contacts`, 'success');
                break; // Success, exit loop
            } catch (contactErr) {
                retries++;
                log(`   ⚠️  Attempt ${retries} failed: ${contactErr.message}`, 'warn');
                
                if (retries >= maxRetries) {
                    log(`   ❌ Max retries reached. getContacts() not available`, 'error');
                    throw new Error('getContacts() method not available. Device may not be fully initialized. Please try reconnecting the device.');
                }
                
                // Wait before retry
                log(`   ⏳ Waiting 2 seconds before retry...`, 'info');
                await new Promise(resolve => setTimeout(resolve, 2000));
            }
        }
        
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
        
        const regularContacts = formattedContacts.filter(c => !c.isGroup).length;
        const groups = formattedContacts.filter(c => c.isGroup).length;
        
        log(`   📊 Formatted ${formattedContacts.length} contacts (${regularContacts} regular, ${groups} groups)`, 'success');
        
        res.json({
            success: true,
            contacts: formattedContacts,
            count: formattedContacts.length
        });
    } catch (error) {
        log(`   ❌ Error getting contacts for ${deviceKey}: ${error.message}`, 'error');
        log(`   Stack trace: ${error.stack}`, 'error');
        res.json({
            success: false,
            message: error.message
        });
    }
});

// Socket.IO connection handling
io.on('connection', (socket) => {
    log(`🔌 Socket.IO client connected: ${socket.id}`, 'success');
    
    socket.on('join-device', (deviceKey) => {
        socket.join(`device-${deviceKey}`);
        log(`   📍 Socket ${socket.id} joined device room: ${deviceKey}`, 'info');
    });
    
    socket.on('disconnect', () => {
        log(`🔌 Socket.IO client disconnected: ${socket.id}`, 'warn');
    });
});

// Health check
app.get('/health', (req, res) => {
    log(`📥 GET /health - Health check request`, 'info');
    log(`   Active clients: ${clients.size}`, 'info');
    log(`   Active QR codes: ${qrCodes.size}`, 'info');
    
    const healthData = {
        status: 'OK',
        timestamp: new Date().toISOString(),
        activeClients: clients.size,
        activeQrCodes: qrCodes.size,
        deviceKeys: Array.from(clients.keys())
    };
    
    res.json(healthData);
});

// Start server
const PORT = process.env.WHATSAPP_PORT || 3001;
server.listen(PORT, () => {
    log(`🚀 WhatsApp Server started successfully!`, 'success');
    log(`   Port: ${PORT}`, 'info');
    log(`   Environment: ${process.env.NODE_ENV || 'development'}`, 'info');
    log(`   Active clients: ${clients.size}`, 'info');
    log(`   Active QR codes: ${qrCodes.size}`, 'info');
    
    // Create sessions directory if it doesn't exist
    const sessionsDir = path.join(__dirname, 'wa-sessions');
    if (!fs.existsSync(sessionsDir)) {
        fs.mkdirSync(sessionsDir, { recursive: true });
        log(`   Created wa-sessions directory: ${sessionsDir}`, 'info');
    } else {
        log(`   Using existing wa-sessions directory: ${sessionsDir}`, 'info');
    }
});

// Graceful shutdown
process.on('SIGINT', async () => {
    log(`🛑 Shutting down WhatsApp server...`, 'warn');
    
    // Disconnect all clients
    log(`   Disconnecting ${clients.size} active clients...`, 'info');
    for (const [deviceKey, client] of clients) {
        try {
            await client.destroy();
            log(`   ✅ Disconnected client: ${deviceKey}`, 'success');
        } catch (error) {
            log(`   ❌ Error disconnecting ${deviceKey}: ${error.message}`, 'error');
        }
    }
    
    log(`👋 Server shutdown complete`, 'info');
    process.exit(0);
}); 