<?php

namespace App\Services;

use App\Models\ResultSummary;
use Illuminate\Support\Facades\Http;

final class AiCommentService
{
    /** @return array{teacher_comment:string,principal_comment:string,source:string} */
    public function generate(ResultSummary $summary): array
    {
        $snapshot = $summary->snapshot;
        $metrics = [
            'average' => data_get($snapshot, 'summary.average_score'),
            'subjects' => collect(data_get($snapshot, 'subjects', []))->map(fn (array $row) => [
                'subject' => $row['subject'],
                'score' => $row['total_score'],
                'grade' => $row['grade'],
                'status' => $row['status'],
            ])->values()->all(),
        ];

        $apiKey = config('services.gemini.key');
        if (! config('services.gemini.enabled', true) || ! $apiKey) {
            return $this->fallback((float) $summary->average_score);
        }

        $model = config('services.gemini.model', 'gemini-2.5-flash');
        $response = Http::timeout(20)->retry(2, 300)->post(
            "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}",
            [
                'contents' => [[
                    'parts' => [[
                        'text' => "Create encouraging, specific school-result comments from anonymous performance data. Never infer identity, health, disability, religion, ethnicity, or family circumstances. Avoid shaming and guarantees. Return concise English comments. Data: ".json_encode($metrics, JSON_THROW_ON_ERROR),
                    ]],
                ]],
                'generationConfig' => [
                    'temperature' => 0.3,
                    'responseMimeType' => 'application/json',
                    'responseSchema' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'teacher_comment' => ['type' => 'STRING'],
                            'principal_comment' => ['type' => 'STRING'],
                        ],
                        'required' => ['teacher_comment', 'principal_comment'],
                    ],
                ],
            ],
        );

        if (! $response->successful()) {
            return $this->fallback((float) $summary->average_score);
        }

        $text = $response->json('candidates.0.content.parts.0.text');
        $result = is_string($text) ? json_decode($text, true) : null;
        if (! is_array($result) || empty($result['teacher_comment']) || empty($result['principal_comment'])) {
            return $this->fallback((float) $summary->average_score);
        }

        return [
            'teacher_comment' => mb_substr(trim($result['teacher_comment']), 0, 500),
            'principal_comment' => mb_substr(trim($result['principal_comment']), 0, 500),
            'source' => 'gemini',
        ];
    }

    /** @return array{teacher_comment:string,principal_comment:string,source:string} */
    private function fallback(float $average): array
    {
        [$teacher, $principal] = match (true) {
            $average >= 80 => ['An excellent performance. Maintain the strong study habits and continue to seek deeper understanding.', 'Outstanding progress this term. Keep demonstrating consistency, curiosity, and discipline.'],
            $average >= 70 => ['A very good performance. Continue revising consistently and aim for even greater mastery.', 'Commendable work this term. Keep building on these strong results.'],
            $average >= 60 => ['A good performance with clear potential for improvement. Give extra attention to the lower-scoring subjects.', 'Good progress. Greater consistency and focused revision will produce stronger outcomes.'],
            $average >= 50 => ['A fair performance. A structured revision plan and regular practice are recommended.', 'There is room for growth. Work closely with your teachers and practise consistently.'],
            default => ['More focused support and regular practice are needed. Begin with the weakest subjects and celebrate steady progress.', 'Do not be discouraged. Consistent effort, guidance, and improved study routines can lead to meaningful progress.'],
        };
        return ['teacher_comment' => $teacher, 'principal_comment' => $principal, 'source' => 'deterministic'];
    }
}
