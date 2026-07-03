<?php
return [
    'ca_max' => (float) env('RESULT_CA_MAX', 30),
    'exam_max' => (float) env('RESULT_EXAM_MAX', 70),
    'grading_scale' => [
        ['grade' => 'A', 'min' => 70, 'remark' => 'Excellent'],
        ['grade' => 'B', 'min' => 60, 'remark' => 'Very Good'],
        ['grade' => 'C', 'min' => 50, 'remark' => 'Good'],
        ['grade' => 'D', 'min' => 45, 'remark' => 'Fair'],
        ['grade' => 'E', 'min' => 40, 'remark' => 'Pass'],
        ['grade' => 'F', 'min' => 0, 'remark' => 'Needs Improvement'],
    ],
];
