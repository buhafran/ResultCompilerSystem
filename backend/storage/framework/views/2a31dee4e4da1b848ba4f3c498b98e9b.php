<?php
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
?>

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
        <h1><?php echo e($publication->school->name); ?></h1>

        <h2>CLASS RESULT BROADSHEET</h2>

        <div class="meta">
            <b>Session:</b>
            <?php echo e($publication->term->academicSession->name); ?>


            &nbsp;

            <b>Term:</b>
            <?php echo e($publication->term->name); ?>


            &nbsp;

            <b>Class:</b>
            <?php echo e($publication->schoolClass->name); ?>


            &nbsp;

            <b>Version:</b>
            <?php echo e($publication->version); ?>


            &nbsp;

            <span class="status">
                <?php echo e(strtoupper($publication->status->value)); ?>

            </span>
        </div>
    </div>

    <table class="sheet">
        <thead>
            
            <tr>
                <th class="num" rowspan="2">S/N</th>

                <th class="adm" rowspan="2">
                    Admission No.
                </th>

                <th class="student" rowspan="2">
                    Student
                </th>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php
                        $subjectName = data_get($subject, 'subject', 'Subject');

                        $subjectCode = data_get($subject, 'subject_code')
                            ?: \Illuminate\Support\Str::limit(
                                $subjectName,
                                10,
                                ''
                            );
                    ?>

                    <th
                        class="subject-heading"
                        colspan="3"
                        title="<?php echo e($subjectName); ?>"
                    >
                        <?php echo e($subjectCode); ?>

                    </th>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

                <th class="metric" rowspan="2">Total</th>
                <th class="metric" rowspan="2">Average</th>
                <th class="metric" rowspan="2">Position</th>
            </tr>

            
            <tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <th class="score-heading">CA</th>
                    <th class="score-heading">EX</th>
                    <th class="score-heading">TT</th>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </tr>
        </thead>

        <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $summaries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $summary): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php
                    $rows = collect(
                        data_get($summary->snapshot, 'subjects', [])
                    )->keyBy(
                        fn ($row) => data_get($row, 'subject_code')
                            ?: data_get($row, 'subject')
                    );
                ?>

                <tr>
                    <td>
                        <?php echo e($loop->iteration); ?>

                    </td>

                    <td class="adm">
                        <?php echo e(data_get(
                            $summary->snapshot,
                            'student.admission_number'
                        )); ?>

                    </td>

                    <td class="student">
                        <?php echo e(data_get(
                            $summary->snapshot,
                            'student.name'
                        )); ?>

                    </td>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $subjectKeys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
                            $row = $rows->get($key);

                            $isAbsent = strtolower(
                                (string) data_get($row, 'status')
                            ) === 'absent';

                            $caScore = $getCaScore($row);
                            $examScore = $getExamScore($row);
                            $totalScore = $getTotalScore($row);
                        ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isAbsent): ?>
                            <td class="ca">—</td>
                            <td class="exam">—</td>

                            <td class="total-score absent">
                                ABS
                            </td>
                        <?php else: ?>
                            <td class="ca">
                                <?php echo e($formatScore($caScore)); ?>

                            </td>

                            <td class="exam">
                                <?php echo e($formatScore($examScore)); ?>

                            </td>

                            <td class="total-score">
                                <?php echo e($formatScore($totalScore)); ?>

                            </td>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

                    <td>
                        <b>
                            <?php echo e(number_format(
                                (float) $summary->total_score,
                                2
                            )); ?>

                        </b>
                    </td>

                    <td>
                        <b>
                            <?php echo e(number_format(
                                (float) $summary->average_score,
                                2
                            )); ?>

                        </b>
                    </td>

                    <td>
                        <b>
                            <?php echo e($summary->class_position ?? '—'); ?>

                        </b>
                    </td>
                </tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <tr>
                    <td colspan="<?php echo e($tableColumnCount); ?>">
                        No compiled student results were found.
                    </td>
                </tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tbody>
    </table>

    <div class="legend">
        <b>Score key:</b>
        CA = Continuous Assessment;
        EX = Examination;
        TT = Subject Total.

        <br>

        <b>Subject key:</b>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <?php
                $subjectName = data_get($subject, 'subject', 'Subject');

                $subjectCode = data_get($subject, 'subject_code')
                    ?: \Illuminate\Support\Str::limit(
                        $subjectName,
                        10,
                        ''
                    );
            ?>

            <?php echo e($subjectCode); ?> = <?php echo e($subjectName); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$loop->last): ?>; <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </div>

    <div class="footer">
        Students: <?php echo e($summaries->count()); ?>


        |

        Subjects: <?php echo e($subjects->count()); ?>


        |

        Class average:
        <?php echo e(number_format(
            (float) data_get(
                $publication->statistics,
                'class_average',
                0
            ),
            2
        )); ?>%

        |

        Generated: <?php echo e(now()->format('d M Y H:i')); ?>

    </div>
</body>
</html><?php /**PATH /Users/buhafran/Downloads/ResultSystem-2/backend/resources/views/result/pdf/broadsheet.blade.php ENDPATH**/ ?>