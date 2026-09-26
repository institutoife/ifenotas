<?php

namespace Tests\Feature;

use App\Models\SimulatorRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SimulatorStatisticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_charts_are_separate_and_admin_only(): void
    {
        $this->get(route('admin.simulator-charts'))->assertRedirect();
        $user = User::factory()->create(['is_admin'=>false,'phone'=>'+59170000556']);
        $this->actingAs($user)->get(route('admin.simulator-charts'))->assertForbidden();
        $user->update(['is_admin'=>true]);
        $this->get(route('admin.simulator-records'))->assertOk()->assertViewIs('admin-simulator-records')
            ->assertSee('Total de consultas')->assertDontSee('subject-stats-title');
        $this->get(route('admin.simulator-charts'))->assertOk()->assertViewIs('admin-simulator-charts')
            ->assertSee('overall-title')->assertDontSee('Detalle de consultas');
    }

    public function test_percentages_use_all_states_and_respect_subject_and_date_filters(): void
    {
        $admin = User::factory()->create(['is_admin'=>true, 'phone'=>'+59170000555']);
        $subjects = config('ife.subjects');
        foreach ([[$subjects[0],'failed'],[$subjects[0],'passed'],[$subjects[0],'pending'],[$subjects[0],'pending'],[$subjects[1],'passed'],[null,'failed']] as [$subject,$status]) {
            SimulatorRecord::create(['visitor_key'=>str_repeat('a',64),'submission_id'=>(string) Str::uuid(),'first'=>50,'second'=>50,'required_third'=>53,'pass_score'=>153,'subject'=>$subject,'status'=>$status]);
        }
        $this->actingAs($admin)->get(route('admin.simulator-charts', ['subject'=>$subjects[0], 'status'=>'failed']))
            ->assertOk()->assertViewHas('total', 4)
            ->assertViewHas('percentages', fn ($p) => $p['failed'] == 25 && $p['passed'] == 25 && $p['pending'] == 50)
            ->assertViewHas('subjectStats', fn ($s) => $s->count() === 1 && $s[0]['total'] === 4 && $s[0]['pending_percent'] == 50)
            ->assertViewHas('records', fn ($r) => $r->total() === 1);
        $this->get(route('admin.simulator-charts'))->assertOk()->assertViewHas('subjectStats', fn ($s) => $s->count() === 3)
            ->assertSee('Sin materia (registro anterior)');
        $this->get(route('admin.simulator-charts', ['from'=>now()->addDay()->format('Y-m-d')]))->assertOk()
            ->assertViewHas('total', 0)->assertViewHas('percentages', fn ($p) => $p->sum() == 0)
            ->assertSee('Sin consultas para calcular porcentajes.');
    }
}
