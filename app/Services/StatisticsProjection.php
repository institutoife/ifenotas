<?php

namespace App\Services;

class StatisticsProjection
{
    /** Largest remainder allocation; input order breaks ties deterministically. */
    public function allocate(array $weights, int $universe): array
    {
        $sum = array_sum($weights);
        $result = array_fill_keys(array_keys($weights), 0);
        if ($sum <= 0 || $universe <= 0) {
            return $result;
        }
        $remainders = [];
        foreach ($weights as $key => $weight) {
            $numerator = $weight * $universe;
            $result[$key] = intdiv($numerator, $sum);
            $remainders[$key] = $numerator % $sum;
        }
        arsort($remainders, SORT_NUMERIC);
        $remaining = $universe - array_sum($result);
        foreach (array_keys($remainders) as $key) {
            if ($remaining-- <= 0) break;
            $result[$key]++;
        }
        return $result;
    }

    public function build(array $subjects, ?int $universe): array
    {
        $keys = ['passed', 'pending', 'failed'];
        $counts = array_fill_keys($keys, 0);
        foreach ($subjects as $subject) {
            foreach ($keys as $key) $counts[$key] += (int) $subject[$key];
        }
        $sample = array_sum($counts);
        $projectionAvailable = $universe !== null && $sample > 0;
        $projected = $this->allocate($counts, $universe ?? 0);
        // Percentages always use the real counts, never projected integers.
        $displayTenths = $this->allocate($counts, 1000);
        $states = [];
        foreach ($keys as $key) {
            $rows = array_values(array_filter($subjects, fn ($row) => $row[$key] > 0));
            usort($rows, fn ($a, $b) => $b[$key] <=> $a[$key] ?: strcmp($a['subject'], $b['subject']));
            $weights = array_map(fn ($row) => (int) $row[$key], $rows);
            $subjectProjection = $this->allocate($weights, $projected[$key]);
            $subjectTenths = $this->allocate($weights, 1000);
            $states[$key] = [
                'real' => $counts[$key],
                'percentage' => $sample ? $counts[$key] * 100 / $sample : 0,
                'display_percentage' => $displayTenths[$key] / 10,
                'projected' => $projectionAvailable ? $projected[$key] : null,
                'subjects' => array_map(fn ($row, $index) => [
                    'subject' => $row['subject'], 'real' => (int) $row[$key],
                    'percentage' => $row[$key] * 100 / $counts[$key],
                    'display_percentage' => $subjectTenths[$index] / 10,
                    'projected' => $projectionAvailable ? $subjectProjection[$index] : null,
                ], $rows, array_keys($rows)),
            ];
        }
        $comparisons = [];
        foreach ($subjects as $subject) {
            $weights = [];
            foreach ($keys as $key) $weights[$key] = (int) $subject[$key];
            $subjectTotal = array_sum($weights);
            $tenths = $this->allocate($weights, 1000);
            $cells = [];
            foreach ($keys as $key) {
                $allocated = 0;
                foreach ($states[$key]['subjects'] as $row) {
                    if ($row['subject'] === $subject['subject']) $allocated = $row['projected'];
                }
                $cells[$key] = [
                    'projected' => $projectionAvailable ? $allocated : null,
                    'percentage' => $subjectTotal ? $weights[$key] * 100 / $subjectTotal : 0,
                    'display_percentage' => $tenths[$key] / 10,
                ];
            }
            $comparisons[] = ['subject' => $subject['subject'], 'states' => $cells,
                'projected' => $projectionAvailable ? array_sum(array_column($cells, 'projected')) : null];
        }
        return compact('sample', 'universe', 'projectionAvailable', 'states', 'comparisons');
    }
}
