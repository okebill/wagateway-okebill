const express = require('express');
const app = express();
const PORT = 3001;

app.use(express.json());

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

// Health check
app.get('/health', (req, res) => {
    res.json({
        status: 'OK',
        timestamp: new Date().toISOString(),
        message: 'Simple test server running'
    });
});

// Test device endpoints
app.post('/api/device/:deviceKey/connect', (req, res) => {
    const { deviceKey } = req.params;
    console.log(`Connect request for device: ${deviceKey}`);
    
    res.json({
        success: true,
        message: 'Connection initiated (test mode)',
        deviceKey: deviceKey
    });
});

app.post('/api/device/:deviceKey/disconnect', (req, res) => {
    const { deviceKey } = req.params;
    console.log(`Disconnect request for device: ${deviceKey}`);
    
    res.json({
        success: true,
        message: 'Device disconnected (test mode)',
        deviceKey: deviceKey
    });
});

app.get('/api/device/:deviceKey/status', (req, res) => {
    const { deviceKey } = req.params;
    console.log(`Status request for device: ${deviceKey}`);
    
    res.json({
        success: true,
        deviceKey: deviceKey,
        status: 'disconnected',
        message: 'Test mode - no real connection',
        timestamp: new Date().toISOString()
    });
});

app.get('/api/qr/:deviceKey', (req, res) => {
    const { deviceKey } = req.params;
    console.log(`QR request for device: ${deviceKey}`);
    
    // Generate dummy QR code
    const dummyQR = `dummy_qr_code_${deviceKey}_${Date.now()}`;
    
    res.json({
        success: true,
        qr: dummyQR,
        expires_at: Date.now() + (5 * 60 * 1000),
        timestamp: Date.now()
    });
});

app.listen(PORT, () => {
    console.log(`Simple WhatsApp Test Server running on port ${PORT}`);
    console.log(`Health check: http://localhost:${PORT}/health`);
    console.log('This is a test server - replace with whatsapp-server.js when dependencies work');
}); 