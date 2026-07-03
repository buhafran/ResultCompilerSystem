<?php

namespace App\Support;

use InvalidArgumentException;

final class GradeScale
{
    /** @param array<int, array{grade:string,min:float|int,remark:string}> $bands */
    public function __construct(private readonly array $bands)
    {
        if ($bands === []) {
            throw new InvalidArgumentException('At least one grade band is required.');
        }
    }

    /** @param array<int, array{grade:string,min:float|int,remark:string}>|null $bands */
    public static function from(?array $bands = null): self
    {
        $bands ??= config('result-system.grading_scale', [
            ['grade' => 'A', 'min' => 70, 'remark' => 'Excellent'],
            ['grade' => 'B', 'min' => 60, 'remark' => 'Very Good'],
            ['grade' => 'C', 'min' => 50, 'remark' => 'Good'],
            ['grade' => 'D', 'min' => 45, 'remark' => 'Fair'],
            ['grade' => 'E', 'min' => 40, 'remark' => 'Pass'],
            ['grade' => 'F', 'min' => 0, 'remark' => 'Needs Improvement'],
        ]);

        usort($bands, fn (array $a, array $b): int => $b['min'] <=> $a['min']);

        return new self($bands);
    }

    /** @return array{grade:string,remark:string} */
    public function evaluate(float $score): array
    {
        foreach ($this->bands as $band) {
            if ($score >= (float) $band['min']) {
                return ['grade' => (string) $band['grade'], 'remark' => (string) $band['remark']];
            }
        }

        $last = $this->bands[array_key_last($this->bands)];
        return ['grade' => (string) $last['grade'], 'remark' => (string) $last['remark']];
    }

    /** @return array<int, array{grade:string,min:float|int,remark:string}> */
    public function bands(): array
    {
        return $this->bands;
    }
}
