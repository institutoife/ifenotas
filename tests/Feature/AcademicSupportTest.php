<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicSupportTest extends TestCase
{
    use RefreshDatabase;

    public function test_both_surfaces_use_the_configured_support_component_and_keep_the_assets_available(): void
    {
        config(['ife.support_whatsapp' => '59160902299', 'ife.school_year_end_date' => '2026-12-02']);
        $responses = [$this->get(route('notes.simulator', ['mode' => 'two']))];
        $user = User::factory()->create(['phone' => '+59170000031']);
        $responses[] = $this->actingAs($user)->get(route('dashboard'));

        foreach ($responses as $response) {
            $response->assertOk()
                ->assertSee('data-support-whatsapp="59160902299"', false)
                ->assertSee('data-school-year-end-date="2026-12-02"', false)
                ->assertSee('data-support-cta', false)
                ->assertSee('APOYO ESCOLAR IFE')
                ->assertSee('Compartir nota')
                ->assertDontSee('Enviar resultado por WhatsApp')
                ->assertDontSee('Consulta por WhatsApp · Clases de apoyo escolar IFE')
                ->assertDontSee('chicolte.png')
                ->assertDontSee('jalon.png')
                ->assertDontSee('feliz.png');
            foreach (config('ife.subjects') as $subject) {
                $response->assertSee('value="'.e($subject).'"', false);
            }
        }
        foreach (['chicolte.png', 'jalon.png', 'feliz.png'] as $asset) {
            $this->assertFileExists(public_path('images/'.$asset));
        }
    }

    public function test_support_configuration_is_independent_of_general_contact(): void
    {
        config(['ife.whatsapp' => '59171324941', 'ife.support_whatsapp' => '59160902299']);
        $this->get(route('notes.simulator'))->assertOk()
            ->assertSee('data-support-whatsapp="59160902299"', false)
            ->assertDontSee('59171324941');
    }
}
