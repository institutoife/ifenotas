<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SimulatorSubjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_subject_is_required_and_must_be_in_the_list(): void
    {
        $payload = ['department'=>'La Paz', 'submission_id'=>(string) Str::uuid(), 'first'=>60, 'second'=>70];
        $this->postJson(route('simulator-records.store'), $payload)->assertUnprocessable()->assertJsonValidationErrors('subject');
        $this->postJson(route('simulator-records.store'), [...$payload, 'subject'=>'inventada'])->assertUnprocessable();
        $this->assertDatabaseCount('simulator_records', 0);
        $this->postJson(route('simulator-records.store'), [...$payload, 'subject'=>config('ife.subjects.0')])->assertCreated();
        $this->assertDatabaseHas('simulator_records', ['subject'=>config('ife.subjects.0'), 'first'=>60, 'second'=>70]);
    }

    public function test_department_is_required_validated_and_saved(): void
    {
        $payload = ['submission_id' => (string) Str::uuid(), 'subject' => config('ife.subjects.0'), 'first' => 60, 'second' => 70];
        $this->postJson(route('simulator-records.store'), $payload)->assertUnprocessable()->assertJsonValidationErrors('department');
        $this->postJson(route('simulator-records.store'), [...$payload, 'department' => 'inventado'])->assertUnprocessable()->assertJsonValidationErrors('department');
        $this->assertDatabaseCount('simulator_records', 0);
        foreach (config('ife.departments') as $department) {
            $this->postJson(route('simulator-records.store'), [...$payload, 'submission_id' => (string) Str::uuid(), 'department' => $department])->assertCreated();
            $this->assertDatabaseHas('simulator_records', ['department' => $department, 'first' => 60, 'second' => 70]);
        }
    }

    public function test_admin_can_filter_consultations_by_subject(): void
    {
        foreach (array_slice(config('ife.subjects'), 0, 2) as $subject) {
            $this->postJson(route('simulator-records.store'), ['department'=>'La Paz', 'submission_id'=>(string) Str::uuid(), 'first'=>60, 'second'=>70, 'subject'=>$subject])->assertCreated();
        }
        $admin = User::factory()->create(['is_admin'=>true, 'phone'=>'+59170000202']);
        $this->actingAs($admin)->get(route('admin.simulator-records', ['subject'=>config('ife.subjects.0')]))
            ->assertOk()->assertViewHas('records', fn ($records) => $records->total() === 1)
            ->assertViewHas('counts', fn ($counts) => $counts->sum() === 1);
    }
}
