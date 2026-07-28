# 📦 Actualización del Servicio WhatsApp - SGIC 2.0

## Resumen de Cambios Realizados

### ✅ Problema Resuelto
- **OpenWA estaba instalado incorrectamente** en la raíz del proyecto (`node_modules/@open-wa`)
- **Solución aplicada:** Movido a `whatsapp-service/node_modules/` con versión específica `4.63.0`

### 📝 Archivos Modificados/Creados en el Repositorio

1. **`whatsapp-service/index.js`** - Actualizado con:
   - Versión compatible de OpenWA (4.63.0)
   - Configuración optimizada para Windows
   - Ruta explícita de Chrome
   - Modo `headless: false` para primera conexión
   - Timeouts extendidos (90s)

2. **`whatsapp-service/package.json`** - Actualizado con:
   - Versión fija: `"@open-wa/wa-automate": "4.63.0"`
   - Script `prod` para PM2

3. **`whatsapp-service/README.md`** - Creado con:
   - Documentación completa de instalación
   - Guía de integración con Laravel
   - Solución de problemas comunes

4. **`whatsapp-service/.gitignore`** - Creado para excluir:
   - `node_modules/`
   - Sesiones de WhatsApp (datos sensibles)
   - Logs y archivos temporales

5. **`whatsapp-service/start-pm2.bat`** - Script para inicio automático en Windows

---

## 🔄 Pasos para Sincronizar tu Instalación Local

### Opción A: Si ya tienes el servicio corriendo (Recomendado)

1. **Detener el servicio actual:**
   ```powershell
   pm2 stop whatsapp-service
   pm2 delete whatsapp-service
   ```

2. **Ir al directorio del servicio:**
   ```powershell
   cd C:\laragon\www\sgic-2.0\whatsapp-service
   ```

3. **Limpiar instalación anterior:**
   ```powershell
   Remove-Item -Recurse -Force node_modules, package-lock.json -ErrorAction SilentlyContinue
   ```

4. **Instalar dependencias correctas:**
   ```powershell
   npm install
   ```
   *Esto instalará @open-wa/wa-automate@4.63.0*

5. **Iniciar con PM2:**
   ```powershell
   pm2 start index.js --name "whatsapp-service"
   pm2 save
   ```

6. **Verificar:**
   ```powershell
   pm2 status
   curl http://localhost:3000/health
   ```

### Opción B: Reinstalación completa (Si hay errores)

1. **Eliminar todo:**
   ```powershell
   cd C:\laragon\www\sgic-2.0\whatsapp-service
   Remove-Item -Recurse -Force node_modules, package-lock.json, _IGNORE_*, data.json -ErrorAction SilentlyContinue
   ```

2. **Reinstalar:**
   ```powershell
   npm install
   pm2 restart whatsapp-service
   ```

3. **Escanear QR:**
   - Se abrirá Chrome automáticamente
   - Escanea el código QR con tu WhatsApp móvil

---

## 🎯 Verificación Final

### 1. Verificar estado del servicio:
```powershell
pm2 status
```
Debe mostrar `whatsapp-service` en estado `online`.

### 2. Probar endpoint de salud:
```powershell
Invoke-RestMethod http://localhost:3000/health
```
Respuesta esperada:
```json
{
  "success": true,
  "status": "running",
  "whatsapp_connected": true
}
```

### 3. Probar envío de código (opcional):
```powershell
$body = @{
    phone = "+5491112345678"
    code = "123456"
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost:3000/send-code" -Method Post -Body $body -ContentType "application/json"
```

---

## 📌 Notas Importantes

### Para el Repositorio Git

1. **Hacer commit de los cambios:**
   ```bash
   git add whatsapp-service/
   git commit -m "fix: Corregir instalación de OpenWA en whatsapp-service
   - Mover @open-wa/wa-automate a whatsapp-service/node_modules
   - Fijar versión 4.63.0 para compatibilidad
   - Agregar documentación y scripts de inicio
   - Configurar PM2 para producción en Windows"
   git push
   ```

2. **NO subir al repositorio:**
   - `whatsapp-service/node_modules/`
   - `whatsapp-service/data.json`
   - `whatsapp-service/_IGNORE_default/`
   - Cualquier archivo de sesión

### Para Otros Desarrolladores

Cualquier desarrollador que clone el repositorio deberá:

```bash
cd whatsapp-service
npm install
pm2 start index.js --name whatsapp-service
```

Y escanear el QR la primera vez.

---

## 🚨 Solución de Problemas Comunes

### Error: "Module not found: @open-wa/wa-automate"
**Causa:** Dependencias no instaladas en el directorio correcto.
**Solución:**
```powershell
cd whatsapp-service
npm install
```

### Error: "TimeoutError: Waiting failed: 30000ms exceeded"
**Causa:** Chrome no se abre o WhatsApp Web no carga.
**Solución:**
1. Verificar que Chrome esté instalado en `C:\Program Files\Google\Chrome\Application\chrome.exe`
2. Limpiar sesión: `Remove-Item -Recurse -Force _IGNORE_default`
3. Reiniciar: `pm2 restart whatsapp-service`

### Error: "Acceso denegado" al crear tarea programada
**Causa:** PowerShell no está como Administrador.
**Solución:** Abrir PowerShell como Administrador antes de ejecutar comandos de PM2 startup.

### El servicio no inicia después de reiniciar Windows
**Causa:** Tarea programada no configurada correctamente.
**Solución:**
1. Copiar `start-pm2.bat` a `C:\Users\{TU_USUARIO}\`
2. Ejecutar PowerShell como Administrador
3. Recrear la tarea programada (ver README.md)

---

## 📞 Soporte

Para problemas adicionales, revisar:
- Logs de PM2: `pm2 logs whatsapp-service`
- Documentación completa: `whatsapp-service/README.md`
- Issues del repositorio

---

**Fecha de actualización:** Julio 2026
**Versión del servicio:** 1.0.0
**Versión de OpenWA:** 4.63.0
