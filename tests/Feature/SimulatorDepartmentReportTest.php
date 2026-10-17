<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SimulatorDepartmentReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_department_reports_require_admin(): void
    {
        foreach (['admin.simulator-departments', 'admin.simulator-department-charts'] as $route) {
            $this->get(route($route))->assertRedirect();
        }
        $this->actingAs(User::factory()->create(['is_admin' => false]));
        foreach (['admin.simulator-departments', 'admin.simulator-department-charts'] as $route) {
            $this->get(route($route))->assertForbidden();
        }
    }

    public function test_department_reports_filter_records_counts_and_charts_without_changing_existing_reports(): void
    {
        foreach ([['La Paz', 20, 20], ['La Paz', 80, 80], ['Beni', 60, 60]] as [$department, $first, $second]) {
            $this->postJson(route('simulator-records.store'), [
                'submission_id' => (string) Str::uuid(), 'department' => $department,
                'subject' => config('ife.subjects.0'), 'first' => $first, 'second' => $second,
            ])->assertCreated();
        }
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        foreach (['admin.simulator-departments', 'admin.simulator-department-charts'] as $route) {
            $this->get(route($route, ['department' => 'La Paz']))->assertOk()
                ->assertViewHas('total', 2)
                ->assertViewHas('counts', fn ($counts) => $counts['failed'] === 1 && $counts['passed'] === 1)
                ->assertViewHas('projection', fn ($projection) => $projection['sample'] === 2 && $projection['universe'] === 2)
                ->assertViewHas('records', fn ($records) => $records->total() === 2 && $records->every(fn ($record) => $record->department === 'La Paz'));
            $this->get(route($route, ['department' => 'La Paz', 'status' => 'failed']))->assertOk()
                ->assertViewHas('records', fn ($records) => $records->total() === 1);
            $this->get(route($route, ['department' => 'La Paz', 'subject' => config('ife.subjects.1')]))->assertOk()->assertViewHas('total', 0);
            $this->get(route($route, ['department' => 'Pando']))->assertOk()->assertViewHas('total', 0);
            $this->get(route($route, ['department' => 'La Paz', 'from' => now()->addDay()->format('Y-m-d')]))->assertOk()->assertViewHas('total', 0);
            $this->get(route($route, ['department' => 'inventado']))->assertSessionHasErrors('department');
            $this->get(route($route))->assertOk()->assertViewHas('total', 3);
        }
        $this->get(route('admin.simulator-records', ['department' => 'La Paz']))->assertOk()->assertViewHas('total', 3);
        $this->get(route('admin.simulator-charts'))->assertOk()->assertViewHas('total', 3);
    }
}
