/**
 * Servicio de WhatsApp usando whatsapp-web.js
 * 
 * Este microservicio expone endpoints para:
 * - Enviar códigos de verificación por WhatsApp
 * - Verificar el estado de la conexión
 * 
 * Instalación:
 * npm install
 * 
 * Ejecución:
 * node index.js
 */

const { Client, LocalAuthStrategy } = require('whatsapp-web.js');
const qrcode = require('qrcode-terminal');
const express = require('express');
const bodyParser = require('body-parser');

const app = express();
const port = process.env.PORT || 3000;

// Middleware
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));

// Cliente de WhatsApp
let client = null;
let isReady = false;

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
        
        if (!isReady) {
            return res.status(503).json({
                success: false,
                message: 'WhatsApp session not initialized. Please scan QR code first.'
            });
        }
        
        // Formatear número de teléfono (eliminar caracteres no numéricos)
        const formattedPhone = phone.replace(/\D/g, '');
        
        // Obtener ID de chat
        const chatId = `${formattedPhone}@c.us`;
        
        // Verificar si el número está registrado en WhatsApp
        const isRegistered = await client.isRegisteredUser(chatId);
        
        if (!isRegistered) {
            return res.status(404).json({
                success: false,
                message: 'Phone number is not registered on WhatsApp'
            });
        }
        
        // Enviar mensaje con el código
        const message = `🔐 *Código de Verificación*\n\nTu código de verificación es: *${code}*\n\nEste código expira en 10 minutos. No lo compartas con nadie.`;
        
        const result = await client.sendMessage(chatId, message);
        
        if (result && result.id) {
            console.log(`✅ Código enviado exitosamente a ${formattedPhone}`);
            return res.json({
                success: true,
                message: 'Verification code sent successfully',
                messageId: result.id._serialized
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
    res.json({
        success: true,
        status: 'running',
        whatsapp_connected: isReady,
        timestamp: new Date().toISOString()
    });
});

/**
 * Endpoint para obtener estado de la sesión
 * GET /session-status
 */
app.get('/session-status', async (req, res) => {
    try {
        if (!client) {
            return res.json({
                success: false,
                authenticated: false,
                message: 'No session found'
            });
        }
        
        const state = await client.getState();
        
        res.json({
            success: true,
            authenticated: state === 'CONNECTED',
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
        client = new Client({
            authStrategy: new LocalAuthStrategy(),
            puppeteer: {
                headless: true,
                args: [
                    '--no-sandbox',
                    '--disable-setuid-sandbox',
                    '--disable-dev-shm-usage',
                    '--disable-accelerated-2d-canvas',
                    '--no-first-run',
                    '--no-zygote',
                    '--disable-gpu'
                ]
            }
        });

        client.on('qr', (qr) => {
            console.log('\n📱 Escanea el código QR con WhatsApp:\n');
            qrcode.generate(qr, { small: true });
            console.log('\n');
        });

        client.on('ready', () => {
            console.log('✅ ¡Cliente de WhatsApp listo y conectado!');
            isReady = true;
        });

        client.on('disconnected', (reason) => {
            console.log('⚠️ Cliente desconectado:', reason);
            isReady = false;
        });

        client.on('message', async (message) => {
            console.log(`💬 Mensaje recibido de ${message.from}:`);
            console.log(`   Contenido: ${message.body}`);
        });

        await client.initialize();
        
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
    
    if (client) {
        try {
            await client.destroy();
            console.log('✅ Session closed');
        } catch (error) {
            console.error('❌ Error closing session:', error);
        }
    }
    
    process.exit(0);
});

// Iniciar
startServer().catch(console.error);
