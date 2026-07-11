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
            // 90% - 100%: Exceptional / Elite
            $average >= 90 => [
                'An exceptional performance reflecting profound understanding and academic maturity. Exceptional work.',
                'Superb leadership and academic excellence. You set a magnificent standard for your peers.'
            ],
        
            // 85% - 89%: Excellent / High Distinction
            $average >= 85 => [
                'An excellent standard of work. Your dedication to mastering complex concepts is highly commendable.',
                'Outstanding achievements this term. Continue to channel this impressive work ethic and curiosity.'
            ],
        
            // 80% - 84%: Very Good to Excellent
            $average >= 80 => [
                'A very strong performance. Maintain these robust study habits and continue to push your boundaries.',
                'Great progress this term. Your consistency and disciplined approach are paying off handsomely.'
            ],
        
            // 75% - 79%: Solid Commendable / Above Average
            $average >= 75 => [
                'A highly commendable effort. You demonstrate a strong grasp of the material; keep aiming for top marks.',
                'Very pleasing results. With continued focus and refinement, you are well on your way to excellence.'
            ],
        
            // 70% - 74%: Good / Competent
            $average >= 70 => [
                'A good, consistent performance. Continue revising regularly to solidify your mastery of finer details.',
                'A successful term. Keep building on these steady results by maintaining active classroom engagement.'
            ],
        
            // 65% - 69%: Developing / High Pass
            $average >= 65 => [
                'A pleasing performance showing clear capability. Target your minor weak areas to elevate your grade further.',
                'Good overall progress. Greater attention to detail and proactive revision will unlock your full potential.'
            ],
        
            // 60% - 64%: Satisfactory / Clear Room for Improvement
            $average >= 60 => [
                'A satisfactory effort, though there is clear potential for higher marks. Focus extra attention on lower-scoring topics.',
                'Steady progress made, but a more rigorous and organized study routine will yield much stronger outcomes.'
            ],
        
            // 55% - 59%: Pass / Approaching Average
            $average >= 55 => [
                'A fair performance. A more structured revision plan and active participation in class are highly recommended.',
                'You are holding steady, but there is definite room for growth. Increase your focus in the coming term.'
            ],
        
            // 50% - 54%: Marginal Pass
            $average >= 50 => [
                'You have met the basic requirements, but regular practice and seeking immediate help on difficult topics are vital.',
                'A marginal pass. Work closely with your teachers, clear up misunderstandings early, and practice consistently.'
            ],
        
            // 40% - 49%: Needs Immediate Support / Narrow Fail
            $average >= 40 => [
                'More focused support, structured revision, and daily practice are urgently required. Begin with the foundational concepts.',
                'Do not be discouraged by this setback. Regular attendance, focused intervention, and a fresh routine can turn this around.'
            ],
        
            // Below 40%: Serious Concern
            default => [
                'Critical gaps in understanding need to be addressed immediately. A personalized learning plan and close monitoring are essential.',
                'A challenging term. We urge a parent-teacher consultation to establish a collaborative plan to support your academic progress.'
            ],
        };
        return ['teacher_comment' => $teacher, 'principal_comment' => $principal, 'source' => 'deterministic'];
    }
}
