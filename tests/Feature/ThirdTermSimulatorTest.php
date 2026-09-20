<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
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
            ->assertSee('Calcula si')
            ->assertSee('cuántos puntos te faltan')
            ->assertDontSee('Simula tus notas y descubre')
            ->assertSee('/simulador-notas?mode=one', false)
            ->assertSee('/simulador-notas?mode=two', false)
            ->assertSee('¿Qué materia necesitas reforzar?')
            ->assertSee('Matemáticas')
            ->assertSee('Lectura y escritura')
            ->assertSee('Programación')
            ->assertSee('Robótica')
            ->assertSee('Cubo Rubik')
            ->assertSee('Instituto de Formación Educabol')
            ->assertSee('meta name="description"', false)
            ->assertSee('logo-ife-educabol-instituto-formacion-educabol.svg')
            ->assertSee('hero-boletin-ife-notas.png')
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

    public function test_homepage_counter_starts_at_eight_hundred_thousand_and_increments(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('800.000')
            ->assertSee('personas ya usaron IFE Notas');

        $this->get('/')
            ->assertOk()
            ->assertSee('800.001');

        $this->assertDatabaseHas('page_counters', [
            'page' => 'homepage',
            'visits' => 800001,
        ]);
    }

    public function test_tiktok_followers_are_hidden_until_a_real_value_exists(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('<br>seguidores', false);

        SiteSetting::create([
            'key' => 'tiktok_followers',
            'value' => '125000',
            'source' => 'manual',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('125.000')
            ->assertSee('<br>seguidores', false);
    }

    public function test_an_administrator_can_update_tiktok_followers(): void
    {
        $administrator = User::factory()->create([
            'phone' => '+59170000001',
            'is_admin' => true,
        ]);

        $this->actingAs($administrator)
            ->post(route('admin.tiktok-followers.update'), ['followers' => 125000])
            ->assertRedirect()
            ->assertSessionHas('status', 'Seguidores de TikTok actualizados correctamente.');

        $this->assertDatabaseHas('site_settings', [
            'key' => 'tiktok_followers',
            'value' => '125000',
            'source' => 'manual',
            'updated_by' => $administrator->id,
        ]);

        $this->actingAs($administrator)
            ->get(route('admin'))
            ->assertOk()
            ->assertSee('Seguidores de TikTok')
            ->assertSee('value="125000"', false)
            ->assertSee('Última actualización');
    }

    public function test_a_non_administrator_cannot_update_tiktok_followers(): void
    {
        $user = User::factory()->create([
            'phone' => '+59170000002',
            'is_admin' => false,
        ]);

        $this->actingAs($user)
            ->post(route('admin.tiktok-followers.update'), ['followers' => 125000])
            ->assertRedirect(route('admin'));

        $this->assertDatabaseMissing('site_settings', [
            'key' => 'tiktok_followers',
        ]);
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
