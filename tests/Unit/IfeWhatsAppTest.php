<?php

namespace Tests\Unit;

use App\Support\IfeWhatsApp;
use PHPUnit\Framework\TestCase;

class IfeWhatsAppTest extends TestCase
{
    public function test_it_builds_a_subject_message_with_the_configured_phone(): void
    {
        $url = IfeWhatsApp::subjectUrl('+591 71324941', 'Física');
        $parts = parse_url($url);
        parse_str($parts['query'], $query);

        $this->assertSame('wa.me', $parts['host']);
        $this->assertSame('/59171324941', $parts['path']);
        $this->assertSame(
            'Hola, vengo de IFE Notas y necesito reforzar mis notas en la materia de Física.',
            $query['text'],
        );
    }

    public function test_it_builds_a_service_message_dynamically(): void
    {
        $url = IfeWhatsApp::serviceUrl('59171324941', 'Robótica');
        parse_str(parse_url($url, PHP_URL_QUERY), $query);

        $this->assertSame(
            'Hola, vengo de IFE Notas y quiero más información sobre el servicio de Robótica.',
            $query['text'],
        );
    }
}
