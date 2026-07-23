<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Customer;

class WhatsAppService
{
    protected string $baseUrl;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('services.whatsapp.url', 'http://localhost:3000');
        $this->timeout = config('services.whatsapp.timeout', 30);
    }

    /**
     * Enviar código de verificación a un cliente
     */
    public function sendVerificationCode(Customer $customer): array
    {
        // Generar código de 6 dígitos
        $code = sprintf('%06d', mt_rand(0, 999999));
        
        // Guardar código en el cliente
        $customer->verification_code = $code;
        $customer->verification_code_sent_at = now();
        $customer->save();

        $phoneNumber = $this->formatPhoneNumber($customer->phone);

        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/send-code", [
                    'phone' => $phoneNumber,
                    'code' => $code,
                    'customer_name' => $customer->full_name,
                ]);

            if ($response->successful()) {
                Log::info("Código WhatsApp enviado a {$customer->full_name} ({$phoneNumber})");
                return [
                    'success' => true,
                    'message' => 'Código de verificación enviado exitosamente',
                ];
            }

            Log::error("Error al enviar código WhatsApp: {$response->body()}");
            return [
                'success' => false,
                'message' => 'Error al enviar el código. Intente nuevamente.',
            ];

        } catch (\Exception $e) {
            Log::error("Excepción al enviar código WhatsApp: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'No se pudo conectar con el servicio de WhatsApp.',
            ];
        }
    }

    /**
     * Verificar código ingresado por el cliente
     */
    public function verifyCode(Customer $customer, string $code): array
    {
        // Verificar si el código existe y no ha expirado (10 minutos)
        if (!$customer->verification_code || $customer->verification_code !== $code) {
            return [
                'success' => false,
                'message' => 'Código inválido.',
            ];
        }

        $codeSentAt = $customer->verification_code_sent_at;
        if ($codeSentAt && $codeSentAt->diffInMinutes(now()) > 10) {
            // Código expirado
            $customer->verification_code = null;
            $customer->verification_code_sent_at = null;
            $customer->save();
            
            return [
                'success' => false,
                'message' => 'El código ha expirado. Solicite uno nuevo.',
            ];
        }

        // Marcar como verificado
        $customer->phone_verified = true;
        $customer->verified_at = now();
        $customer->verification_code = null;
        $customer->verification_code_sent_at = null;
        $customer->save();

        Log::info("Cliente {$customer->full_name} verificado exitosamente");

        return [
            'success' => true,
            'message' => 'Teléfono verificado exitosamente.',
        ];
    }

    /**
     * Enviar notificación de nuevo contrato creado
     */
    public function sendNewContractNotification(Customer $customer, array $contractData): array
    {
        $phoneNumber = $this->formatPhoneNumber($customer->phone);
        
        $message = "Hola {$customer->full_name},\n\n";
        $message .= "Se ha generado un nuevo contrato:\n";
        $message .= "📄 Número: {$contractData['contract_number']}\n";
        $message .= "🏛️ Cripta: {$contractData['crypt_info']}\n";
        $message .= "💰 Valor: $" . number_format($contractData['price'], 0, ',', '.') . "\n\n";
        $message .= "Pronto recibirás más información para la firma digital.";

        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/send-message", [
                    'phone' => $phoneNumber,
                    'message' => $message,
                ]);

            if ($response->successful()) {
                Log::info("Notificación de contrato enviada a {$customer->full_name}");
                return [
                    'success' => true,
                    'message' => 'Notificación enviada exitosamente',
                ];
            }

            Log::warning("No se pudo enviar notificación: {$response->body()}");
            return [
                'success' => false,
                'message' => 'No se pudo enviar la notificación.',
            ];

        } catch (\Exception $e) {
            Log::error("Excepción al enviar notificación: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error de conexión con WhatsApp.',
            ];
        }
    }

    /**
     * Enviar notificación de contrato firmado
     */
    public function sendContractSignedNotification(Customer $customer, array $contractData): array
    {
        $phoneNumber = $this->formatPhoneNumber($customer->phone);
        
        $message = "Hola {$customer->full_name},\n\n";
        $message .= "✅ Tu contrato ha sido firmado exitosamente!\n";
        $message .= "📄 Número: {$contractData['contract_number']}\n";
        $message .= "📅 Fecha de firma: " . now()->format('d/m/Y') . "\n\n";
        $message .= "El documento está disponible en tu portal de cliente.";

        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/send-message", [
                    'phone' => $phoneNumber,
                    'message' => $message,
                ]);

            if ($response->successful()) {
                Log::info("Notificación de firma enviada a {$customer->full_name}");
                return [
                    'success' => true,
                    'message' => 'Notificación enviada exitosamente',
                ];
            }

            return [
                'success' => false,
                'message' => 'No se pudo enviar la notificación.',
            ];

        } catch (\Exception $e) {
            Log::error("Excepción al enviar notificación de firma: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error de conexión con WhatsApp.',
            ];
        }
    }

    /**
     * Formatear número de teléfono para WhatsApp
     * Elimina espacios, guiones y agrega código de país si es necesario
     */
    protected function formatPhoneNumber(string $phone): string
    {
        // Eliminar caracteres no numéricos
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        
        // Si tiene 10 dígitos (Colombia), agregar 57
        if (strlen($cleaned) === 10) {
            return "57{$cleaned}";
        }
        
        // Si ya tiene código de país, retornar tal cual
        if (strlen($cleaned) >= 11 && substr($cleaned, 0, 2) === '57') {
            return $cleaned;
        }
        
        return $cleaned;
    }

    /**
     * Verificar si el servicio de WhatsApp está disponible
     */
    public function checkServiceStatus(): bool
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/health");
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
