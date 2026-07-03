@php
    $firstSummary = $summaries->first();

    $subjects = collect(
        data_get($firstSummary?->snapshot, 'subjects', [])
    )->values();

    $subjectKeys = $subjects->map(
        fn ($subject) => $subject['subject_code'] ?: $subject['subject']
    );

    /*
    |--------------------------------------------------------------------------
    | Score value helpers
    |--------------------------------------------------------------------------
    |
    | These fallbacks allow the broadsheet to work with different snapshot
    | field names. The first available value will be displayed.
    |
    */
    $getCaScore = function (?array $row) {
        if (!$row) {
            return null;
        }

        return data_get($row, 'ca_score')
            ?? data_get($row, 'continuous_assessment_score')
            ?? data_get($row, 'continuous_assessment')
            ?? data_get($row, 'ca')
            ?? data_get($row, 'assessment_score');
    };

    $getExamScore = function (?array $row) {
        if (!$row) {
            return null;
        }

        return data_get($row, 'exam_score')
            ?? data_get($row, 'examination_score')
            ?? data_get($row, 'examination')
            ?? data_get($row, 'exam')
            ?? data_get($row, 'ex');
    };

    $getTotalScore = function (?array $row) {
        if (!$row) {
            return null;
        }

        return data_get($row, 'total_score')
            ?? data_get($row, 'total')
            ?? data_get($row, 'tt');
    };

    $formatScore = function ($score) {
        if ($score === null || $score === '') {
            return '—';
        }

        $score = (float) $score;

        return floor($score) == $score
            ? number_format($score, 0)
            : number_format($score, 2);
    };

    /*
    |--------------------------------------------------------------------------
    | Table column count
    |--------------------------------------------------------------------------
    |
    | Fixed columns:
    | S/N, Admission No., Student, Total, Average and Position = 6
    |
    | Every subject contributes CA, EX and TT = 3 columns.
    |
    */
    $tableColumnCount = 6 + ($subjects->count() * 3);
@endphp

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">

    <style>
        @page {
            size: A3 landscape;
            margin: 14px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #172a3a;
            font-size: 7px;
        }

        .header {
            text-align: center;
            margin-bottom: 9px;
        }

        .header h1 {
            font-size: 17px;
            margin: 0;
        }

        .header h2 {
            font-size: 12px;
            margin: 4px 0;
        }

        .meta {
            margin: 3px 0;
        }

        .sheet {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .sheet th,
        .sheet td {
            border: 1px solid #7d8e99;
            padding: 2px;
            text-align: center;
            vertical-align: middle;
            overflow: hidden;
            word-wrap: break-word;
        }

        .sheet th {
            background: #e8f1f3;
            font-size: 6.5px;
            font-weight: bold;
        }

        .sheet .subject-heading {
            background: #d7e7eb;
            font-size: 6.5px;
        }

        .sheet .score-heading {
            background: #edf4f6;
            font-size: 6px;
            width: 18px;
        }

        .sheet .student {
            text-align: left;
            width: 115px;
        }

        .sheet .adm {
            text-align: left;
            width: 65px;
        }

        .sheet .num {
            width: 20px;
        }

        .sheet .metric {
            width: 33px;
        }

        .sheet .ca {
            background: #f8fbfc;
        }

        .sheet .exam {
            background: #f5f9fa;
        }

        .sheet .total-score {
            font-weight: bold;
        }

        .absent {
            font-size: 6px;
            font-weight: bold;
        }

        .legend {
            margin-top: 9px;
            line-height: 1.6;
        }

        .footer {
            margin-top: 8px;
            font-size: 7px;
            color: #526875;
        }

        .status {
            display: inline-block;
            padding: 2px 5px;
            border: 1px solid #7d8e99;
            border-radius: 8px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>{{ $publication->school->name }}</h1>

        <h2>CLASS RESULT BROADSHEET</h2>

        <div class="meta">
            <b>Session:</b>
            {{ $publication->term->academicSession->name }}

            &nbsp;

            <b>Term:</b>
            {{ $publication->term->name }}

            &nbsp;

            <b>Class:</b>
            {{ $publication->schoolClass->name }}

            &nbsp;

            <b>Version:</b>
            {{ $publication->version }}

            &nbsp;

            <span class="status">
                {{ strtoupper($publication->status->value) }}
            </span>
        </div>
    </div>

    <table class="sheet">
        <thead>
            {{-- First header row --}}
            <tr>
                <th class="num" rowspan="2">S/N</th>

                <th class="adm" rowspan="2">
                    Admission No.
                </th>

                <th class="student" rowspan="2">
                    Student
                </th>

                @foreach ($subjects as $subject)
                    @php
                        $subjectName = data_get($subject, 'subject', 'Subject');

                        $subjectCode = data_get($subject, 'subject_code')
                            ?: \Illuminate\Support\Str::limit(
                                $subjectName,
                                10,
                                ''
                            );
                    @endphp

                    <th
                        class="subject-heading"
                        colspan="3"
                        title="{{ $subjectName }}"
                    >
                        {{ $subjectCode }}
                    </th>
                @endforeach

                <th class="metric" rowspan="2">Total</th>
                <th class="metric" rowspan="2">Average</th>
                <th class="metric" rowspan="2">Position</th>
            </tr>

            {{-- Second header row --}}
            <tr>
                @foreach ($subjects as $subject)
                    <th class="score-heading">CA</th>
                    <th class="score-heading">EX</th>
                    <th class="score-heading">TT</th>
                @endforeach
            </tr>
        </thead>

        <tbody>
            @forelse ($summaries as $summary)
                @php
                    $rows = collect(
                        data_get($summary->snapshot, 'subjects', [])
                    )->keyBy(
                        fn ($row) => data_get($row, 'subject_code')
                            ?: data_get($row, 'subject')
                    );
                @endphp

                <tr>
                    <td>
                        {{ $loop->iteration }}
                    </td>

                    <td class="adm">
                        {{ data_get(
                            $summary->snapshot,
                            'student.admission_number'
                        ) }}
                    </td>

                    <td class="student">
                        {{ data_get(
                            $summary->snapshot,
                            'student.name'
                        ) }}
                    </td>

                    @foreach ($subjectKeys as $key)
                        @php
                            $row = $rows->get($key);

                            $isAbsent = strtolower(
                                (string) data_get($row, 'status')
                            ) === 'absent';

                            $caScore = $getCaScore($row);
                            $examScore = $getExamScore($row);
                            $totalScore = $getTotalScore($row);
                        @endphp

                        @if ($isAbsent)
                            <td class="ca">—</td>
                            <td class="exam">—</td>

                            <td class="total-score absent">
                                ABS
                            </td>
                        @else
                            <td class="ca">
                                {{ $formatScore($caScore) }}
                            </td>

                            <td class="exam">
                                {{ $formatScore($examScore) }}
                            </td>

                            <td class="total-score">
                                {{ $formatScore($totalScore) }}
                            </td>
                        @endif
                    @endforeach

                    <td>
                        <b>
                            {{ number_format(
                                (float) $summary->total_score,
                                2
                            ) }}
                        </b>
                    </td>

                    <td>
                        <b>
                            {{ number_format(
                                (float) $summary->average_score,
                                2
                            ) }}
                        </b>
                    </td>

                    <td>
                        <b>
                            {{ $summary->class_position ?? '—' }}
                        </b>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $tableColumnCount }}">
                        No compiled student results were found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="legend">
        <b>Score key:</b>
        CA = Continuous Assessment;
        EX = Examination;
        TT = Subject Total.

        <br>

        <b>Subject key:</b>

        @foreach ($subjects as $subject)
            @php
                $subjectName = data_get($subject, 'subject', 'Subject');

                $subjectCode = data_get($subject, 'subject_code')
                    ?: \Illuminate\Support\Str::limit(
                        $subjectName,
                        10,
                        ''
                    );
            @endphp

            {{ $subjectCode }} = {{ $subjectName }}@if (!$loop->last); @endif
        @endforeach
    </div>

    <div class="footer">
        Students: {{ $summaries->count() }}

        |

        Subjects: {{ $subjects->count() }}

        |

        Class average:
        {{ number_format(
            (float) data_get(
                $publication->statistics,
                'class_average',
                0
            ),
            2
        ) }}%

        |

        Generated: {{ now()->format('d M Y H:i') }}
    </div>
</body>
</html>