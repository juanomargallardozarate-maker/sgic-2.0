@echo off
REM Script de inicio automático para WhatsApp Service
REM Este archivo debe estar en C:\Users\{USUARIO}\start-pm2.bat

cd /d C:\laragon\www\sgic-2.0\whatsapp-service

REM Verificar si PM2 está disponible
where pm2 >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] PM2 no está instalado o no está en el PATH
    echo Ejecutar: npm install -g pm2
    exit /b 1
)

REM Iniciar todos los procesos guardados en PM2
echo [INFO] Iniciando WhatsApp Service con PM2...
pm2 start all

REM Esperar 5 segundos para que PM2 inicie los procesos
timeout /t 5 /nobreak >nul

REM Verificar estado
pm2 status whatsapp-service

echo [INFO] WhatsApp Service iniciado correctamente
exit /b 0
