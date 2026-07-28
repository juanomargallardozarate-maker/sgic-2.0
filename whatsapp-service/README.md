# Servicio de WhatsApp - SGIC 2.0

Microservicio para envío de códigos de verificación y notificaciones por WhatsApp usando OpenWA.

## 📋 Requisitos Previos

- **Node.js** v18 o superior
- **Google Chrome** instalado en `C:\Program Files\Google\Chrome\Application\chrome.exe`
- **npm** (incluido con Node.js)
- **PM2** (para producción)

## 🚀 Instalación

### 1. Instalar dependencias

```bash
cd whatsapp-service
npm install
```

Esto instalará:
- `@open-wa/wa-automate@4.63.0` - Librería principal de WhatsApp
- `express` - Servidor HTTP
- `body-parser` - Middleware para parsear JSON

### 2. Verificar instalación

```bash
npm list @open-wa/wa-automate
```

Debe mostrar la versión 4.63.0.

## ⚙️ Configuración

El servicio está configurado para:
- Puerto: **3000** (configurable con variable de entorno `PORT`)
- Sesión: **default**
- Chrome: Usa instalación local de Google Chrome
- QR: Sin tiempo de expiración (`qrTimeout: 0`)

### Modificar configuración (opcional)

Edita `index.js` en la función `initWhatsApp()`:

```javascript
const client = await wa.create({
    sessionId: 'default',      // Cambiar nombre de sesión
    authTimeout: 90000,        // Tiempo de autenticación (ms)
    headless: false,           // true para modo invisible (después de escanear QR)
    executablePath: 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
    // ... más opciones
});
```

## 🔧 Ejecución

### Modo Desarrollo (manual)

```bash
cd whatsapp-service
npm start
```

**Primera ejecución:**
1. Se abrirá una ventana de Chrome
2. Escanea el código QR con tu WhatsApp móvil
3. La sesión quedará guardada para futuras ejecuciones

### Modo Producción (con PM2)

```bash
# Instalar PM2 globalmente
npm install -g pm2

# Iniciar servicio
pm2 start index.js --name "whatsapp-service"

# Guardar configuración para reinicio automático
pm2 save

# Configurar inicio con Windows (requiere PowerShell como Administrador)
pm2 install pm2-windows-startup
pm2-startup install
```

### Comandos útiles de PM2

```bash
pm2 status              # Ver estado de servicios
pm2 logs whatsapp-service   # Ver logs en tiempo real
pm2 restart whatsapp-service # Reiniciar servicio
pm2 stop whatsapp-service    # Detener servicio
pm2 monit               # Monitor gráfico
```

## 📡 Endpoints API

### 1. Enviar Código de Verificación

**POST** `/send-code`

**Body:**
```json
{
    "phone": "+5491112345678",
    "code": "123456"
}
```

**Respuesta Exitosa:**
```json
{
    "success": true,
    "message": "Verification code sent successfully",
    "messageId": "ABC123..."
}
```

**Respuestas de Error:**
- `400`: Phone/code requeridos o código no es de 6 dígitos
- `404`: Número no registrado en WhatsApp
- `503`: Sesión de WhatsApp no inicializada (escanear QR primero)
- `500`: Error interno del servidor

### 2. Verificar Estado del Servicio

**GET** `/health`

**Respuesta:**
```json
{
    "success": true,
    "status": "running",
    "whatsapp_connected": true,
    "timestamp": "2026-07-22T02:41:12.311Z"
}
```

### 3. Obtener Estado de Sesión

**GET** `/session-status`

**Respuesta:**
```json
{
    "success": true,
    "authenticated": true,
    "state": "CONNECTED",
    "timestamp": "2026-07-22T02:41:12.311Z"
}
```

## 🔗 Integración con Laravel

### 1. Configurar URL en `.env`

```env
WHATSAPP_SERVICE_URL=http://localhost:3000
```

### 2. Crear Servicio en Laravel

`app/Services/WhatsAppService.php`:

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.whatsapp.url');
    }

    public function sendVerificationCode(string $phone, string $code): array
    {
        $response = Http::timeout(30)->post("{$this->baseUrl}/send-code", [
            'phone' => $phone,
            'code' => $code,
        ]);

        return $response->json();
    }

    public function isConnected(): bool
    {
        try {
            $response = Http::get("{$this->baseUrl}/health");
            return $response->json('whatsapp_connected', false);
        } catch (\Exception $e) {
            return false;
        }
    }
}
```

### 3. Registrar en `config/services.php`

```php
'whatsapp' => [
    'url' => env('WHATSAPP_SERVICE_URL', 'http://localhost:3000'),
],
```

### 4. Usar en Controlador

```php
use App\Services\WhatsAppService;

public function enviarCodigo(Request $request, WhatsAppService $whatsapp)
{
    $resultado = $whatsapp->sendVerificationCode(
        $request->phone,
        $request->code
    );

    if ($resultado['success']) {
        return response()->json(['mensaje' => 'Código enviado']);
    }

    return response()->json(['error' => $resultado['message']], 400);
}
```

## 🛠️ Solución de Problemas

### El servicio no inicia / TimeoutError

1. **Limpiar sesión corrupta:**
   ```bash
   cd whatsapp-service
   Remove-Item -Recurse -Force "_IGNORE_default" -ErrorAction SilentlyContinue
   Remove-Item -Recurse -Force "data.json" -ErrorAction SilentlyContinue
   ```

2. **Forzar descarga de Chromium:**
   ```bash
   npx @open-wa/wa-automate-force-download
   ```

3. **Ejecutar en modo visible:**
   Asegúrate que `headless: false` en `index.js`

### Chrome no se abre

Verifica que Chrome esté instalado en la ruta especificada:
```
C:\Program Files\Google\Chrome\Application\chrome.exe
```

Si está en otra ubicación, actualiza `executablePath` en `index.js`.

### Error "Init system not found" con PM2

Es normal en Windows. Usa la alternativa:
```bash
pm2 install pm2-windows-startup
pm2-startup install
```

O crea una tarea programada manualmente (ver sección de PM2).

### Mensajes no se envían

1. Verifica que el número tenga código de país (ej: `+549...`)
2. Confirma que el número esté registrado en WhatsApp
3. Revisa los logs: `pm2 logs whatsapp-service`

## 📝 Notas Importantes

- **Primera conexión:** Requiere escanear QR manualmente
- **Persistencia:** La sesión se guarda automáticamente después del primer escaneo
- **Multi-dispositivo:** Habilitado (`multiDevice: true`)
- **Reconexión:** Automática en caso de caída (`restartOnCrash: true`)
- **Seguridad:** No compartas el archivo de sesión (`data.json`)

## 🔄 Actualización

Para actualizar la librería OpenWA:

```bash
npm uninstall @open-wa/wa-automate
npm install @open-wa/wa-automate@4.63.0
pm2 restart whatsapp-service
```

## 📄 Licencia

Uso interno para SGIC 2.0
