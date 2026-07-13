<?php

namespace App\Services;

class RfcValidator
{
    private const GENERIC_RFCS = [
        'XAXX010101000',
        'XEXX010101000',
    ];

    /**
     * Valida un RFC mexicano (PF o PM)
     * 
     * @param bool $strict Si es false, no valida el dígito verificador (útil en desarrollo)
     */
    public static function isValid(string $rfc, bool $strict = true): bool
    {
        $rfc = strtoupper(trim($rfc));

        // 1. Validar RFCs genéricos primero
        if (in_array($rfc, self::GENERIC_RFCS, true)) {
            return true;
        }

        // 2. Determinar tipo y validar formato
        $length = strlen($rfc);

        if ($length === 13) {
            return self::validatePersonaFisica($rfc, $strict);
        }

        if ($length === 12) {
            return self::validatePersonaMoral($rfc, $strict);
        }

        return false;
    }

    /**
     * Valida RFC de Persona Física (13 caracteres)
     */
    private static function validatePersonaFisica(string $rfc, bool $strict = true): bool
    {
        if (!preg_match('/^[A-ZÑ&]{4}\d{6}[A-Z0-9]{3}$/', $rfc)) {
            return false;
        }

        if (!self::validateDate(substr($rfc, 4, 6))) {
            return false;
        }

        // En modo desarrollo, no validar dígito verificador
        if (!$strict) {
            return true;
        }

        return self::calculateVerifier(substr($rfc, 0, 12)) === substr($rfc, -1);
    }

    /**
     * Valida RFC de Persona Moral (12 caracteres)
     */
    private static function validatePersonaMoral(string $rfc, bool $strict = true): bool
    {
        if (!preg_match('/^[A-ZÑ&]{3}\d{6}[A-Z0-9]{3}$/', $rfc)) {
            return false;
        }

        if (!self::validateDate(substr($rfc, 3, 6))) {
            return false;
        }

        // En modo desarrollo, no validar dígito verificador
        if (!$strict) {
            return true;
        }

        return self::calculateVerifier(substr($rfc, 0, 11)) === substr($rfc, -1);
    }

    /**
     * Valida que la fecha embebida en el RFC sea válida
     */
    private static function validateDate(string $dateStr): bool
    {
        if (strlen($dateStr) !== 6 || !ctype_digit($dateStr)) {
            return false;
        }

        $year = (int) substr($dateStr, 0, 2);
        $month = (int) substr($dateStr, 2, 2);
        $day = (int) substr($dateStr, 4, 2);

        if ($month < 1 || $month > 12 || $day < 1 || $day > 31) {
            return false;
        }

        $fullYear = $year > 50 ? 1900 + $year : 2000 + $year;

        return checkdate($month, $day, $fullYear);
    }

    /**
     * Calcula el dígito verificador del RFC
     */
    private static function calculateVerifier(string $rfcBase): string
    {
        $charMap = [
            '0' => 0, '1' => 1, '2' => 2, '3' => 3, '4' => 4,
            '5' => 5, '6' => 6, '7' => 7, '8' => 8, '9' => 9,
            'A' => 10, 'B' => 11, 'C' => 12, 'D' => 13, 'E' => 14,
            'F' => 15, 'G' => 16, 'H' => 17, 'I' => 18, 'J' => 19,
            'K' => 20, 'L' => 21, 'M' => 22, 'N' => 23, 'O' => 24,
            'P' => 25, 'Q' => 26, 'R' => 27, 'S' => 28, 'T' => 29,
            'U' => 30, 'V' => 31, 'W' => 32, 'X' => 33, 'Y' => 34,
            'Z' => 35, 'Ñ' => 36, '&' => 37,
        ];

        $factors = [13, 12, 11, 10, 9, 8, 7, 6, 5, 4, 3, 2];

        $chars = str_split($rfcBase);
        $sum = 0;

        for ($i = 0; $i < count($chars); $i++) {
            $char = $chars[$i];
            $value = $charMap[$char] ?? 0;
            $factor = $factors[$i] ?? 2;
            $sum += $value * $factor;
        }

        $remainder = $sum % 11;
        $verifier = 11 - $remainder;

        if ($verifier === 10) {
            return 'A';
        }
        if ($verifier === 11) {
            return '0';
        }

        return (string) $verifier;
    }

    public static function getType(string $rfc): string
    {
        $rfc = strtoupper(trim($rfc));

        if (in_array($rfc, self::GENERIC_RFCS, true)) {
            return 'generic';
        }

        return strlen($rfc) === 13 ? 'persona_fisica' : 'persona_moral';
    }

    public static function generateTestRfc(string $type = 'fisica'): string
    {
        if ($type === 'moral') {
            $base = 'SGI' . date('ymd') . 'AB';
        } else {
            $base = 'TEST' . date('ymd') . 'AB';
        }

        return $base . self::calculateVerifier($base);
    }
}