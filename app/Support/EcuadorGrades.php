<?php

namespace App\Support;

final class EcuadorGrades
{
    public const TARGET_TOTAL_CENTS = 2100;
    public const MAX_GRADE_CENTS = 1000;

    /** @return array{required_third_cents: int, maximum_average_cents: int, target_total_cents: int, status: string} */
    public static function calculate(int $firstCents, int $secondCents): array
    {
        foreach ([$firstCents, $secondCents] as $grade) {
            if ($grade < 0 || $grade > self::MAX_GRADE_CENTS) {
                throw new \InvalidArgumentException('Las notas deben estar entre 0 y 1000 centésimas.');
            }
        }
        $required = self::TARGET_TOTAL_CENTS - $firstCents - $secondCents;

        return [
            'required_third_cents' => $required,
            // Only the displayed average is rounded, never the exact pass threshold.
            'maximum_average_cents' => intdiv($firstCents + $secondCents + self::MAX_GRADE_CENTS + 1, 3),
            'target_total_cents' => self::TARGET_TOTAL_CENTS,
            'status' => $required <= self::MAX_GRADE_CENTS ? 'possible' : 'impossible',
        ];
    }

    public static function format(int $cents): string
    {
        return intdiv($cents, 100).','.str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }
}
