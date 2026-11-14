<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EcuadorSimulatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_preserves_existing_content_and_adds_country_links_with_the_real_counter(): void
    {
        $visits = DB::table('page_counters')->where('page', 'homepage')->value('visits');
        $this->get('/')->assertOk()
            ->assertSee('href="'.route('notes.simulator').'"', false)
            ->assertSee('href="'.route('notes.ecuador').'"', false)
            ->assertSee('Elige tu país')
            ->assertSee('data-country-choice="bolivia"', false)
            ->assertSee('data-country-choice="ecuador"', false)
            ->assertDontSee('data-ecuador-simulator', false)
            ->assertDontSee('ife-grade-simulator.js', false)
            ->assertSee('hero-boletin-ife-notas.png')
            ->assertSee('Servicios de IFE')
            ->assertSee('Calcular mi nota')
            ->assertSee('personas ya usaron IFE Notas')
            ->assertSee('Sigue al creador de esta herramienta')
            ->assertSeeInOrder(['id="heroTitle"', 'id="countryTitle"', 'id="supportTitle"', 'id="statisticsTitle"', 'id="servicesTitle"', 'id="institutionalTitle"'], false);
        $this->assertDatabaseHas('page_counters', ['page' => 'homepage', 'visits' => $visits + 1]);
        $this->assertDatabaseCount('simulator_records', 0);
    }

    public function test_whatsapp_destination_is_taken_from_the_country_configuration(): void
    {
        config(['ife.countries.ecuador.whatsapp' => '+593 99 123 4567']);
        $this->get(route('notes.ecuador'))->assertOk()
            ->assertSee('data-whatsapp-base="https://wa.me/593991234567?text="', false)
            ->assertSee('Cambiar país')
            ->assertSee('href="'.route('auth').'"', false)
            ->assertDontSee('data-ec-regime', false)->assertDontSee('régimen escolar')
            ->assertSee('data-ec-province', false)->assertSee('data-ec-subject', false)
            ->assertSee('Otra')->assertSee('data-ec-other-subject', false)
            ->assertSee('sobre 10')->assertSee('Enviar mi resultado por WhatsApp')
            ->assertSee('data-ec-modes', false)
            ->assertSee('APOYO ESCOLAR IFE')->assertSee('Compartir nota')
            ->assertSeeInOrder(['data-ec-first', 'data-ec-second', 'data-ec-province', 'data-ec-subject', 'data-ec-calculate'], false);
    }

    public function test_registering_another_configured_country_adds_its_link_without_a_template_change(): void
    {
        config(['ife.countries.test-country' => [
            'name' => 'País de prueba',
            'flag' => '🌍',
            'route' => 'notes.ecuador',
        ]]);
        $this->get('/')->assertOk()
            ->assertSee('data-country-choice="test-country"', false)
            ->assertSee('País de prueba')
            ->assertSee('href="'.route('notes.ecuador').'"', false);
    }

    public function test_bolivia_preserves_both_direct_modes_and_its_configured_contact(): void
    {
        config(['ife.countries.bolivia.whatsapp' => '59170000000']);
        foreach (['/simulador-notas', '/simulador-notas?mode=one', '/simulador-notas?mode=two'] as $path) {
            $this->get($path)->assertOk()
                ->assertSee('data-support-whatsapp="59170000000"', false)
                ->assertSee('data-mode="one"', false)->assertSee('data-mode="two"', false)
                ->assertSee('TENGO 1 NOTA')
                ->assertSee('TENGO 2 NOTAS')
                ->assertSee('Cambiar país');
        }
    }

    public function test_ecuador_provinces_and_subjects_come_from_country_configuration(): void
    {
        $this->assertCount(24, config('ife.countries.ecuador.provinces'));
        $response = $this->get(route('notes.ecuador'))->assertOk();
        foreach (array_merge(config('ife.countries.ecuador.provinces'), config('ife.countries.ecuador.subjects')) as $option) {
            $response->assertSee('value="'.e($option).'"', false);
        }
        $this->assertDatabaseCount('simulator_records', 0);
    }

    public function test_logged_in_students_can_also_change_country(): void
    {
        $user = User::factory()->create(['phone' => '+59170000042']);
        $this->actingAs($user)->get('/')->assertOk()->assertSee('Elige tu país');
    }

    public function test_ecuador_uses_the_classic_layout_controls_and_decimal_range_without_bolivia_records(): void
    {
        foreach (['notes.simulator', 'notes.ecuador'] as $routeName) {
            $this->get(route($routeName))->assertOk()
                ->assertSee('class="page-head"', false)
                ->assertSee('class="mode-card"', false)
                ->assertSee('class="sim-workspace"', false)
                ->assertSee('class="known-fields"', false)
                ->assertSee('class="sim-zones"', false)
                ->assertSee('class="pass-marker"', false)
                ->assertSee('class="support-action"', false);
        }
        $this->get(route('notes.ecuador'))->assertOk()
            ->assertSee('max="10" step="0.01"', false)
            ->assertSee('data-ec-maximum-average', false)
            ->assertDontSee('ife-simulator-records.js', false)
            ->assertDontSee('class="ec-range"', false);
        $this->assertDatabaseCount('simulator_records', 0);
    }
}
