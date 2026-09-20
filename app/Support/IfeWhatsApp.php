<?php

namespace App\Support;

final class IfeWhatsApp
{
    public static function subjectUrl(string $phone, string $subject): string
    {
        return self::url(
            $phone,
            "Hola, vengo de IFE Notas y necesito reforzar mis notas en la materia de {$subject}.",
        );
    }

    public static function serviceUrl(string $phone, string $service): string
    {
        return self::url(
            $phone,
            "Hola, vengo de IFE Notas y quiero más información sobre el servicio de {$service}.",
        );
    }

    public static function generalUrl(string $phone): string
    {
        return self::url($phone, 'Hola, vengo de IFE Notas y quiero más información sobre IFE.');
    }

    private static function url(string $phone, string $message): string
    {
        $normalizedPhone = preg_replace('/\D+/', '', $phone) ?? '';

        return 'https://wa.me/'.$normalizedPhone.'?text='.rawurlencode($message);
    }
}
