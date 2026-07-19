/**
 * Servicio de WhatsApp usando OpenWA
 * 
 * Este microservicio expone endpoints para:
 * - Enviar códigos de verificación por WhatsApp
 * - Verificar el estado de la conexión
 * 
 * Instalación:
 * npm install @open-wa/wa-automate express body-parser
 * 
 * Ejecución:
 * node whatsapp-service.js
 */

const wa = require('@open-wa/wa-automate');
const express = require('express');
const bodyParser = require('body-parser');

const app = express();
const port = process.env.PORT || 3000;

// Middleware
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));

// Almacenamiento temporal de sesiones (en producción usar Redis o similar)
const sessions = new Map();

/**
 * Endpoint para enviar código de verificación
 * POST /send-code
 * Body: { phone: string, code: string }
 */
app.post('/send-code', async (req, res) => {
    try {
        const { phone, code } = req.body;
        
        if (!phone || !code) {
            return res.status(400).json({
                success: false,
                message: 'Phone and code are required'
            });
        }
        
        // Validar formato del código (6 dígitos)
        if (!/^\d{6}$/.test(code)) {
            return res.status(400).json({
                success: false,
                message: 'Code must be 6 digits'
            });
        }
        
        // Formatear número de teléfono (eliminar caracteres no numéricos)
        const formattedPhone = phone.replace(/\D/g, '');
        
        // Obtener o crear sesión
        let client = sessions.get('default');
        
        if (!client) {
            return res.status(503).json({
                success: false,
                message: 'WhatsApp session not initialized. Please scan QR code first.'
            });
        }
        
        // Verificar si el número está registrado en WhatsApp
        const isRegistered = await client.isRegisteredUser(formattedPhone + '@c.us');
        
        if (!isRegistered) {
            return res.status(404).json({
                success: false,
                message: 'Phone number is not registered on WhatsApp'
            });
        }
        
        // Enviar mensaje con el código
        const message = `🔐 *Código de Verificación*\n\nTu código de verificación es: *${code}*\n\nEste código expira en 10 minutos. No lo compartas con nadie.`;
        
        const result = await client.sendText(`${formattedPhone}@c.us`, message);
        
        if (result && result.id) {
            console.log(`✅ Código enviado exitosamente a ${formattedPhone}`);
            return res.json({
                success: true,
                message: 'Verification code sent successfully',
                messageId: result.id
            });
        } else {
            throw new Error('Failed to send message');
        }
        
    } catch (error) {
        console.error('Error sending WhatsApp message:', error);
        return res.status(500).json({
            success: false,
            message: 'Error sending WhatsApp message',
            error: error.message
        });
    }
});

/**
 * Endpoint para verificar estado del servicio
 * GET /health
 */
app.get('/health', (req, res) => {
    const client = sessions.get('default');
    const isConnected = client ? true : false;
    
    res.json({
        success: true,
        status: 'running',
        whatsapp_connected: isConnected,
        timestamp: new Date().toISOString()
    });
});

/**
 * Endpoint para obtener estado de la sesión
 * GET /session-status
 */
app.get('/session-status', async (req, res) => {
    try {
        const client = sessions.get('default');
        
        if (!client) {
            return res.json({
                success: false,
                authenticated: false,
                message: 'No session found'
            });
        }
        
        const isLogged = await client.isLoggedIn();
        const state = await client.getState();
        
        res.json({
            success: true,
            authenticated: isLogged,
            state: state,
            timestamp: new Date().toISOString()
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            error: error.message
        });
    }
});

/**
 * Inicializar cliente de WhatsApp
 */
async function initWhatsApp() {
    console.log('🔄 Initializing WhatsApp session...');
    
    try {
        const client = await wa.create({
            sessionId: 'default',
            multiDevice: true,
            authTimeout: 60000,
            cacheEnabled: false,
            useChrome: true,
            killProcessOnBrowserClose: true,
            throwErrorOnTosBlock: false,
            chromiumArgs: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--aggressive-cache-discard',
                '--disable-cache',
                '--disable-application-cache',
                '--disable-offline-load-stale-cache',
                '--disk-cache-size=0'
            ]
        });
        
        client.onStateChanged(async (state) => {
            console.log('📱 State changed:', state);
            
            if (state === 'CONFLICT' || state === 'UNLAUNCHED') {
                console.log('⚠️ Session conflict, restarting...');
                client.forceRefocus();
            }
        });
        
        client.onAnyMessage(async (message) => {
            console.log('💬 Message received from:', message.from);
        });
        
        sessions.set('default', client);
        console.log('✅ WhatsApp session initialized successfully');
        
    } catch (error) {
        console.error('❌ Error initializing WhatsApp:', error);
        process.exit(1);
    }
}

/**
 * Iniciar servidor
 */
async function startServer() {
    await initWhatsApp();
    
    app.listen(port, '0.0.0.0', () => {
        console.log(`🚀 WhatsApp Service running on http://localhost:${port}`);
        console.log(`   Endpoints:`);
        console.log(`   - POST /send-code`);
        console.log(`   - GET /health`);
        console.log(`   - GET /session-status`);
    });
}

// Manejar cierre graceful
process.on('SIGINT', async () => {
    console.log('\n🛑 Shutting down...');
    
    for (const [sessionId, client] of sessions.entries()) {
        try {
            await client.close();
            console.log(`✅ Session ${sessionId} closed`);
        } catch (error) {
            console.error(`❌ Error closing session ${sessionId}:`, error);
        }
    }
    
    process.exit(0);
});

// Iniciar
startServer().catch(console.error);
