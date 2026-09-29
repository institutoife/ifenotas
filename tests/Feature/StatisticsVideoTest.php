<?php

namespace Tests\Feature;

use App\Models\SimulatorRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class StatisticsVideoTest extends TestCase
{
    use RefreshDatabase;

    public function test_view_uses_current_counter_without_writing_or_changing_classification(): void
    {
        DB::table('page_counters')->where('page','homepage')->update(['visits'=>101]);
        foreach ([['passed',100,100],['pending',60,60],['failed',1,2]] as [$status,$first,$second]) {
            SimulatorRecord::create(['visitor_key'=>str_repeat('a',64),'submission_id'=>(string)Str::uuid(),'subject'=>null,'first'=>$first,'second'=>$second,'status'=>$status,'pass_score'=>153,'required_third'=>max(0,153-$first-$second)]);
        }
        $before=SimulatorRecord::all()->toArray();
        $admin=User::factory()->create(['is_admin'=>true,'phone'=>'+59171111111']);
        $this->actingAs($admin)->get(route('admin.simulator-charts'))->assertOk()
            ->assertViewHas('projection',fn($p)=>$p['universe']===101 && $p['sample']===3 && array_sum(array_column($p['states'],'projected'))===101)
            ->assertSee('EN RIESGO')->assertDontSee('En carrera')->assertSee('#FFA500',false)->assertSee('#FF2C2C',false)
            ->assertSee('Sin materia')->assertSee('no representa visitantes')
            ->assertSee('data-frame="subject-0"', false)->assertSee('Comparativo de estados')
            ->assertDontSee('video-values')->assertDontSee('data-real=', false)
            ->assertViewHas('projection', fn($p) => $p['comparisons'][0]['projected'] === 101 && $p['comparisons'][0]['states']['passed']['projected'] === 34);
        $this->assertSame($before,SimulatorRecord::all()->toArray());
        $this->assertDatabaseHas('page_counters',['page'=>'homepage','visits'=>101]);
        auth()->logout();
        $this->get('/')->assertOk()->assertSeeInOrder(['supportTitle', 'statisticsTitle', 'servicesTitle'], false)
            ->assertSee('TOTAL ESTIMADO')->assertSee('APLAZADOS')->assertSee('APROBADOS')->assertSee('EN RIESGO')
            ->assertViewHas('projection', fn($p) => $p['universe'] === 102 && array_sum(array_column($p['states'], 'projected')) === 102);
        $this->assertSame($before, SimulatorRecord::all()->toArray());
        $this->actingAs($admin)->get(route('admin.simulator-charts',['from'=>now()->addDay()->format('Y-m-d')]))->assertOk()
            ->assertViewHas('projection',fn($p)=>$p['sample']===0 && !$p['projectionAvailable']);
    }
}
