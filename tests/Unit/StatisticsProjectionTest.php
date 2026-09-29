<?php

namespace Tests\Unit;

use App\Services\StatisticsProjection;
use PHPUnit\Framework\TestCase;

class StatisticsProjectionTest extends TestCase
{
    public function test_two_stage_projection_preserves_percentages_and_exact_totals(): void
    {
        $service = new StatisticsProjection();
        $rows = [
            ['subject'=>'A','passed'=>3,'pending'=>2,'failed'=>2],
            ['subject'=>'B','passed'=>2,'pending'=>1,'failed'=>1],
            ['subject'=>'C','passed'=>0,'pending'=>0,'failed'=>1],
        ];
        foreach ([0,1,2,10,800005] as $universe) {
            $data = $service->build($rows, $universe);
            $this->assertSame(12, $data['sample']);
            $this->assertSame($universe, array_sum(array_column($data['states'], 'projected')));
            $this->assertEqualsWithDelta(100, array_sum(array_column($data['states'], 'display_percentage')), .00001);
            $this->assertEqualsWithDelta(4/12*100, $data['states']['failed']['percentage'], .000001);
            foreach ($data['states'] as $state) {
                $this->assertSame($state['projected'], array_sum(array_column($state['subjects'], 'projected')));
                $this->assertEqualsWithDelta(100, array_sum(array_column($state['subjects'], 'display_percentage')), .00001);
            }
            $this->assertSame('A', $data['states']['failed']['subjects'][0]['subject']);
            $this->assertEquals(50, $data['states']['failed']['subjects'][0]['percentage']);
        }
        $this->assertSame($service->build($rows, 2), $service->build($rows, 2));
    }

    public function test_empty_states_missing_counter_and_rounding_ties(): void
    {
        $service = new StatisticsProjection();
        $empty=$service->build([],800005);
        $this->assertFalse($empty['projectionAvailable']);
        $this->assertSame(0,$empty['sample']);
        $this->assertNull($empty['states']['failed']['projected']);
        $rows=[['subject'=>'Sin materia','passed'=>0,'pending'=>2,'failed'=>0]];
        $missing=$service->build($rows,null);
        $this->assertFalse($missing['projectionAvailable']);
        $this->assertEquals(100,$missing['states']['pending']['percentage']);
        $this->assertSame([],$missing['states']['failed']['subjects']);
        $this->assertSame([1,0,0],$service->allocate([1,1,1],1));
        $this->assertSame([334,333,333],$service->allocate([1,1,1],1000));
    }
}
