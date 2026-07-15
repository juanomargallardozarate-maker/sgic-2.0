<?php

namespace App\Exceptions;

use Exception;

class CryptNotAvailableException extends Exception
{
    /**
     * Reportar la excepción (log)
     */
    public function report(): void
    {
        // Laravel maneja el log automáticamente
    }

    /**
     * Renderizar la excepción como respuesta HTTP
     */
    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $this->getMessage(),
            ], 422);
        }

        return redirect()->back()
            ->with('error', $this->getMessage());
    }
}