@php
    $settings = $template?->settings ?? [];
    $primary = $settings['primary_color'] ?? '#0f766e';
    $logoRelative = $publication->school->logo_path;
    $signatureRelative = $publication->school->principal_signature_path;
    $logo = $logoRelative && is_file(storage_path('app/public/'.$logoRelative)) ? storage_path('app/public/'.$logoRelative) : null;
    $signature = $signatureRelative && is_file(storage_path('app/public/'.$signatureRelative)) ? storage_path('app/public/'.$signatureRelative) : null;
@endphp
<!doctype html>
<html>
<head><meta charset="utf-8"><style>
@page{margin:22px}body{font-family:DejaVu Sans,sans-serif;color:#18303f;font-size:9px}.card{page-break-after:always}.card:last-child{page-break-after:auto}.header{border:2px solid {{ $primary }};padding:10px;text-align:center;position:relative}.logo{position:absolute;left:12px;top:9px;width:55px;height:55px;object-fit:contain}.header h1{margin:0 65px 4px;font-size:19px;color:{{ $primary }}}.title{background:{{ $primary }};color:#fff;text-align:center;padding:6px;font-weight:bold;margin:7px 0}.info,.results,.summary,.comments,.approvals{width:100%;border-collapse:collapse}.info td,.results th,.results td,.comments td{border:1px solid #bfcdd4;padding:5px}.results th{background:#e9f3f1}.center{text-align:center}.summary{margin-top:7px}.summary td{text-align:center;background:#edf4f5;padding:6px;border:3px solid #fff}.comments{margin-top:8px}.comments td{width:50%;height:45px;vertical-align:top}.approvals{margin-top:15px}.approvals td{width:50%;text-align:center;padding:0 45px}.line{border-top:1px solid #718894;padding-top:3px}.signature{height:30px;max-width:110px}.small{font-size:7px;margin-top:6px}
</style></head>
<body>
@foreach($summaries as $summary)
@php $snapshot=$summary->snapshot; $subjects=$snapshot['subjects']??[]; $sum=$snapshot['summary']??[]; @endphp
<div class="card">
<div class="header">@if($logo)<img class="logo" src="{{ $logo }}">@endif<h1>{{ data_get($snapshot,'school.name') }}</h1><div>{{ data_get($snapshot,'school.address') }}</div><em>{{ data_get($snapshot,'school.motto') }}</em></div>
<div class="title">{{ $settings['header_note'] ?? 'TERMINAL ACADEMIC REPORT' }}</div>
<table class="info"><tr><td><b>Student:</b> {{ data_get($snapshot,'student.name') }}</td><td><b>Admission:</b> {{ data_get($snapshot,'student.admission_number') }}</td><td><b>Class:</b> {{ data_get($snapshot,'academic.class') }}</td></tr><tr><td><b>Session:</b> {{ data_get($snapshot,'academic.session') }}</td><td><b>Term:</b> {{ data_get($snapshot,'academic.term') }}</td><td><b>Gender:</b> {{ ucfirst(data_get($snapshot,'student.gender','—')) }}</td></tr></table>
<table class="results"><thead><tr><th>Subject</th><th>CA</th><th>Exam</th><th>Total</th><th>Grade</th>@if($settings['show_subject_position']??true)<th>Position</th>@endif<th>Remark</th></tr></thead><tbody>@foreach($subjects as $row)<tr><td>{{ $row['subject'] }}</td><td class="center">{{ $row['ca_score']??'—' }}</td><td class="center">{{ $row['exam_score']??'—' }}</td><td class="center">{{ $row['total_score']??'—' }}</td><td class="center"><b>{{ $row['grade'] }}</b></td>@if($settings['show_subject_position']??true)<td class="center">{{ $row['position']??'—' }}</td>@endif<td>{{ $row['remark'] }}</td></tr>@endforeach</tbody></table>
<table class="summary"><tr><td>Total<br><b>{{ number_format((float)($sum['total_score']??0),2) }}</b></td><td>Average<br><b>{{ number_format((float)($sum['average_score']??0),2) }}%</b></td><td>Position<br><b>{{ $sum['class_position']??'—' }}</b></td><td>Class size<br><b>{{ $sum['class_size']??'—' }}</b></td><td>Subjects<br><b>{{ $sum['subject_count']??count($subjects) }}</b></td></tr></table>
<table class="comments"><tr><td><b>Class teacher's comment</b><br><br>{{ $summary->teacher_comment?:'—' }}</td><td><b>Principal's comment</b><br><br>{{ $summary->principal_comment?:'—' }}</td></tr></table>
<table class="approvals"><tr><td><div class="line">Class Teacher</div></td><td>@if($signature)<img class="signature" src="{{ $signature }}"><br>@endif<div class="line">{{ data_get($snapshot,'school.principal_name') ?: 'Principal' }}</div></td></tr></table>
@if(data_get($snapshot,'next_term_begins_on'))<div class="small"><b>Next term begins:</b> {{ \Carbon\Carbon::parse(data_get($snapshot,'next_term_begins_on'))->format('d F Y') }}</div>@endif
@if($settings['show_grading_scale']??true)<div class="small"><b>Grading scale:</b> @foreach(($snapshot['grading_scale']??[]) as $band) {{ $band['grade'] }} ({{ $band['min'] }}+) {{ $band['remark'] }}@if(!$loop->last) | @endif @endforeach</div>@endif
<div class="small">Result version {{ $publication->version }} · {{ strtoupper($publication->status->value) }}@if($settings['show_verification_code']??true) · Verification code {{ strtoupper(substr(str_replace('-','',$summary->public_token),0,16)) }}@endif</div>
</div>
@endforeach
</body></html>
