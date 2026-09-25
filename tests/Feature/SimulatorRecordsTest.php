<?php

namespace Tests\Feature;

use App\Models\SimulatorRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SimulatorRecordsTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_records_are_classified_on_server_and_retries_do_not_duplicate(): void
    {
        $this->withSession(['simulator_visitor_key' => (string) Str::uuid()]);
        $id = (string) Str::uuid();
        $payload = ['subject'=>config('ife.subjects.0'), 'submission_id'=>$id, 'first'=>1, 'second'=>2, 'status'=>'passed'];
        $this->postJson(route('simulator-records.store'), $payload)->assertCreated()->assertJsonPath('status', 'failed');
        $this->postJson(route('simulator-records.store'), $payload)->assertOk();
        $this->assertDatabaseCount('simulator_records', 1);
        $this->assertDatabaseHas('simulator_records', ['first'=>1, 'second'=>2, 'required_third'=>150, 'status'=>'failed']);

        foreach ([[100,53,'passed'],[26,27,'pending'],[25,27,'failed'],[100,52,'pending']] as [$first,$second,$status]) {
            $this->postJson(route('simulator-records.store'), ['subject'=>config('ife.subjects.0'), 'submission_id'=>(string) Str::uuid(),'first'=>$first,'second'=>$second])
                ->assertCreated()->assertJsonPath('status', $status);
        }
        $this->assertDatabaseCount('simulator_records', 5);
    }

    public function test_grades_must_be_complete_integers_between_zero_and_one_hundred(): void
    {
        foreach ([['first'=>101,'second'=>20], ['first'=>20,'second'=>-1], ['first'=>50.5,'second'=>20], ['first'=>20], ['first'=>'abc','second'=>20]] as $notes) {
            $this->postJson(route('simulator-records.store'), ['subject'=>config('ife.subjects.0'), 'submission_id'=>(string) Str::uuid(), ...$notes])->assertUnprocessable();
        }
        $this->assertDatabaseCount('simulator_records', 0);
    }

    public function test_only_admin_can_read_records_and_filter_them(): void
    {
        $this->get(route('admin.simulator-records'))->assertRedirect();
        $user = User::factory()->create(['phone'=>'+59170000101','is_admin'=>false]);
        $this->actingAs($user)->get(route('admin.simulator-records'))->assertForbidden();
        $user->update(['is_admin'=>true]);
        foreach ([[1,2],[100,100],[60,60]] as [$first,$second]) {
            $this->postJson(route('simulator-records.store'), ['subject'=>config('ife.subjects.0'), 'submission_id'=>(string) Str::uuid(),'first'=>$first,'second'=>$second])->assertCreated();
        }
        $this->get(route('admin.simulator-records', ['status'=>'failed']))->assertOk()
            ->assertViewHas('counts', fn ($counts) => $counts['failed'] === 1 && $counts['passed'] === 1 && $counts['pending'] === 1)
            ->assertViewHas('records', fn ($records) => $records->total() === 1 && $records->first()->status === 'failed');
        $this->get(route('admin.simulator-records', ['from'=>now()->addDay()->format('Y-m-d')]))->assertOk()
            ->assertViewHas('records', fn ($records) => $records->total() === 0);
    }
}
