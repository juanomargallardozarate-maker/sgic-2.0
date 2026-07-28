const { create } = require('@open-wa/wa-automate');

function start(client) {
  console.log('✅ ¡Bot conectado y listo para recibir mensajes!');
  
  client.onMessage(async (message) => {
    // Ignorar mensajes de grupos para evitar spam accidental
    if (message.isGroupMsg) return;

    if (message.body.toLowerCase() === 'hola') {
      await client.sendText(message.from, '¡Hola! 👋 Soy el bot de SGIC 2.0. ¿En qué puedo ayudarte?');
    }
    
    if (message.body.toLowerCase() === 'estado') {
      await client.sendText(message.from, '🟢 El sistema está funcionando correctamente.');
    }
  });
}

create({
  sessionId: 'SGIC_BOT',
  multiDevice: true,
  headless: false,     // <--- CAMBIO: false para que veas la ventana y el QR (cámbialo a true luego)
  qrTimeout: 0,
  authTimeout: 0,
  cacheEnabled: false,
  useChrome: true      // <--- CAMBIO: Obliga a usar tu Google Chrome instalado, que es más estable
})
  .then((client) => start(client))
  .catch((error) => {
    console.error('❌ Error al iniciar el bot:', error);
  });