<?php

namespace App\Support;

final class CompetitionRanker
{
    /**
     * Standard competition ranking: 100, 90, 90, 80 => 1, 2, 2, 4.
     *
     * @param array<int|string, float|int> $scores keyed by record identifier
     * @return array<int|string, int>
     */
    public static function rank(array $scores): array
    {
        arsort($scores, SORT_NUMERIC);
        $ranks = [];
        $position = 0;
        $previousScore = null;
        $previousRank = 0;

        foreach ($scores as $key => $score) {
            $position++;
            if ($previousScore !== null && (float) $score === (float) $previousScore) {
                $rank = $previousRank;
            } else {
                $rank = $position;
            }
            $ranks[$key] = $rank;
            $previousScore = $score;
            $previousRank = $rank;
        }

        return $ranks;
    }
}
