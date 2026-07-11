@php
    use App\Support\ResultQrCode;

    $settings = $template?->settings ?? [];
    $primary = $settings['primary_color'] ?? '#0f766e';
    $accent = $settings['accent_color'] ?? '#f59e0b';
    $subjects = $snapshot['subjects'] ?? [];
    $sum = $snapshot['summary'] ?? [];
    $showClassPosition = (bool) ($summary->publication?->school?->setting('results.show_class_position', data_get($snapshot, 'settings.show_class_position', true)) ?? data_get($snapshot, 'settings.show_class_position', true));
    $showSubjectPosition = (bool) ($settings['show_subject_position'] ?? true);
    $showVerification = (bool) ($settings['show_verification_code'] ?? true);

    $logoRelative = data_get($snapshot, 'school.logo_path');
    $signatureRelative = data_get($snapshot, 'school.principal_signature_path');
    $photoRelative = data_get($snapshot, 'student.photo_path');

    $logo = $logoRelative && is_file(storage_path('app/public/'.$logoRelative)) ? storage_path('app/public/'.$logoRelative) : null;
    $signature = $signatureRelative && is_file(storage_path('app/public/'.$signatureRelative)) ? storage_path('app/public/'.$signatureRelative) : null;
    $studentPhoto = $photoRelative && is_file(storage_path('app/public/'.$photoRelative)) ? storage_path('app/public/'.$photoRelative) : null;

    $verificationUrl = route('results.show', ['summary' => $summary->public_token]);
    $verificationCode = strtoupper(substr(str_replace('-', '', $summary->public_token), 0, 16));
    $verificationQr = $showVerification ? ResultQrCode::dataUri($verificationUrl, 130) : null;
@endphp
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<style>
@page{margin:24px}body{font-family:DejaVu Sans,sans-serif;color:#18303f;font-size:10px}.header{border:2px solid {{ $primary }};padding:12px;text-align:center;position:relative}.logo{position:absolute;left:14px;top:12px;width:62px;height:62px;object-fit:contain}.header h1{margin:0 70px 5px;font-size:21px;color:{{ $primary }}}.title{background:{{ $primary }};color:#fff;text-align:center;padding:8px;font-weight:bold;letter-spacing:1px;margin:8px 0;border-bottom:3px solid {{ $accent }}}.info{width:100%;border-collapse:collapse;margin:8px 0 12px}.info td{border:1px solid #bfcdd4;padding:7px;width:33%}.info small{display:block;color:#6a7e89;font-size:8px;text-transform:uppercase}.student-photo-cell{width:92px;text-align:center;vertical-align:middle}.student-photo{width:78px;height:90px;object-fit:cover;border:1px solid #9eb2bd;padding:2px;background:#fff}.results{width:100%;border-collapse:collapse}.results th{background:#e9f3f1;border:1px solid #9eb9b3;padding:6px}.results td{border:1px solid #c4d0d5;padding:6px}.subject-name{font-weight:bold}.subject-subtitle{display:block;font-size:8px;color:#526875;margin-top:2px}.center{text-align:center}.summary{width:100%;border-collapse:separate;border-spacing:5px;margin-top:10px}.summary td{background:#edf4f5;text-align:center;padding:8px}.comments{width:100%;border-collapse:separate;border-spacing:6px;margin-top:10px}.comments td{border:1px solid #c4d0d5;padding:8px;width:50%;height:58px;vertical-align:top}.approvals{width:100%;margin-top:18px}.approvals td{width:50%;text-align:center;vertical-align:bottom;padding:0 45px}.line{border-top:1px solid #718894;padding-top:4px}.signature{height:35px;max-width:120px;object-fit:contain}.verify{border:1px dashed {{ $primary }};margin-top:10px;padding:8px;font-size:8px;display:table;width:100%}.verify-text{display:table-cell;vertical-align:middle}.verify-qr{display:table-cell;width:74px;text-align:right;vertical-align:middle}.verify-qr img{width:68px;height:68px}.scale{font-size:8px;margin-top:7px}
</style>
</head>
<body>
<div class="header">
    @if($logo)<img class="logo" src="{{ $logo }}" alt="">@endif
    <h1>{{ data_get($snapshot,'school.name') }}</h1>
    <div>{{ data_get($snapshot,'school.address') }}</div>
    <em>{{ data_get($snapshot,'school.motto') }}</em>
</div>
<div class="title">{{ $settings['header_note'] ?? 'TERMINAL ACADEMIC REPORT' }}</div>
<table class="info">
    <tr>
        <td><small>Student</small><b>{{ data_get($snapshot,'student.name') }}</b></td>
        <td><small>Admission number</small><b>{{ data_get($snapshot,'student.admission_number') }}</b></td>
        <td><small>Class</small><b>{{ data_get($snapshot,'academic.class') }}</b></td>
        @if($studentPhoto)<td class="student-photo-cell" rowspan="2"><img class="student-photo" src="{{ $studentPhoto }}" alt=""></td>@endif
    </tr>
    <tr>
        <td><small>Session</small><b>{{ data_get($snapshot,'academic.session') }}</b></td>
        <td><small>Term</small><b>{{ data_get($snapshot,'academic.term') }}</b></td>
        <td><small>Gender</small><b>{{ ucfirst(data_get($snapshot,'student.gender','—')) }}</b></td>
    </tr>
</table>
<table class="results">
    <thead>
        <tr>
            <th>Subject</th><th>CA (30)</th><th>Exam (70)</th><th>Total</th><th>Grade</th>
            @if($showSubjectPosition)<th>Position</th>@endif
            <th>Remark</th>
        </tr>
    </thead>
    <tbody>
        @foreach($subjects as $row)
            <tr>
                <td><span class="subject-name">{{ $row['subject'] }}</span>@if(!empty($row['subject_subtitle']))<span class="subject-subtitle">{{ $row['subject_subtitle'] }}</span>@endif</td>
                <td class="center">{{ $row['ca_score']??'—' }}</td>
                <td class="center">{{ $row['exam_score']??'—' }}</td>
                <td class="center">{{ $row['total_score']??'—' }}</td>
                <td class="center"><b>{{ $row['grade'] }}</b></td>
                @if($showSubjectPosition)<td class="center">{{ $row['position']??'—' }}</td>@endif
                <td>{{ $row['remark'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
<table class="summary">
    <tr>
        <td>Total<br><b>{{ number_format($sum['total_score']??0,2) }}</b></td>
        <td>Average<br><b>{{ number_format($sum['average_score']??0,2) }}%</b></td>
        @if($showClassPosition)<td>Position<br><b>{{ $sum['class_position']??'—' }}</b></td>@endif
        <td>Class size<br><b>{{ $sum['class_size']??'—' }}</b></td>
        <td>Subjects<br><b>{{ $sum['subject_count']??count($subjects) }}</b></td>
        <td>Class high<br><b>{{ number_format($sum['highest_average']??0,2) }}%</b></td>
        <td>Class low<br><b>{{ number_format($sum['lowest_average']??0,2) }}%</b></td>
    </tr>
</table>
<table class="comments"><tr><td><b>Class teacher's comment</b><br><br>{{ $summary->teacher_comment?:'—' }}</td><td><b>Principal's comment</b><br><br>{{ $summary->principal_comment?:'—' }}</td></tr></table>
<table class="approvals"><tr><td><div class="line">Class Teacher</div></td><td>@if($signature)<img class="signature" src="{{ $signature }}" alt=""><br>@endif<div class="line">{{ data_get($snapshot,'school.principal_name') ?: 'Principal' }}</div></td></tr></table>
@if(data_get($snapshot,'next_term_begins_on'))<div class="scale"><b>Next term begins:</b> {{ \Carbon\Carbon::parse(data_get($snapshot,'next_term_begins_on'))->format('d F Y') }}</div>@endif
@if($settings['show_grading_scale']??true)<div class="scale"><b>Grading scale:</b> @foreach(($snapshot['grading_scale']??[]) as $band) {{ $band['grade'] }} ({{ $band['min'] }}+) {{ $band['remark'] }}@if(!$loop->last) | @endif @endforeach</div>@endif
@if($showVerification)
    <div class="verify">
        <div class="verify-text"><b>Verification:</b> Scan the QR code to verify this result.<br><b>Code:</b> {{ $verificationCode }}</div>
        <div class="verify-qr"><img src="{{ $verificationQr }}" alt="Verification QR code"></div>
    </div>
@endif
</body></html>
