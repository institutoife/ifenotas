<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThirdTermSimulatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_third_term_simulator_is_publicly_available(): void
    {
        $this->get('/simulador-notas')
            ->assertOk()
            ->assertSee('¿Cuántas notas ya tienes?')
            ->assertSee('UNA NOTA')
            ->assertSee('DOS NOTAS')
            ->assertSee('Enviar resultado por WhatsApp')
            ->assertDontSee('data-quick', false)
            ->assertDontSee('Promedio');
    }

    public function test_homepage_prioritizes_both_simulator_modes_and_ife_services(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('¿Cuánto necesitas para pasar de curso?')
            ->assertSee('/simulador-notas?mode=one', false)
            ->assertSee('/simulador-notas?mode=two', false)
            ->assertSee('Apoyo escolar')
            ->assertSee('Instituto de Formación Educabol')
            ->assertSee('meta name="description"', false)
            ->assertSee('logo-ife-educabol-instituto-formacion-educabol.svg')
            ->assertSee('david-flores-ife-educabol-instituto-formacion-educabol.png')
            ->assertSee('https://wa.me/59171324941', false)
            ->assertSee('https://www.tiktok.com/@ife_educabol', false)
            ->assertSee('https://www.facebook.com/ife.educabol', false)
            ->assertSee('https://www.instagram.com/ife_educabol', false)
            ->assertSee('https://www.youtube.com/@ife_educabol', false);
    }

    public function test_live_notes_redirects_to_unified_simulator(): void
    {
        $this->get('/live-notas')
            ->assertRedirect('/simulador-notas');
    }

    public function test_legacy_third_term_url_redirects_to_unified_simulator(): void
    {
        $this->get('/simulador-tercer-trimestre')
            ->assertRedirect('/simulador-notas');
    }

    public function test_chato_routes_were_removed(): void
    {
        $this->assertFalse(app('router')->getRoutes()->hasNamedRoute('ai.chat'));
        $this->assertFalse(app('router')->getRoutes()->hasNamedRoute('simulations.save'));
    }

    public function test_dashboard_uses_the_same_unified_flow_without_chato(): void
    {
        $user = User::factory()->create(['phone' => '+59170000000']);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('¿Cuántas notas ya tienes?')
            ->assertDontSee('CHATO');
    }
}
