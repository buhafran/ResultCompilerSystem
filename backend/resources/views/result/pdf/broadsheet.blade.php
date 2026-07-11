@php
    $firstSummary = $summaries->first();
    $subjects = collect(data_get($firstSummary?->snapshot, 'subjects', []))->values();
    $subjectKeys = $subjects->map(fn ($subject) => $subject['subject_code'] ?: $subject['subject']);
@endphp
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<style>
@page{margin:18px}body{font-family:DejaVu Sans,sans-serif;color:#172a3a;font-size:8px}.header{text-align:center;margin-bottom:9px}.header h1{font-size:17px;margin:0}.header h2{font-size:12px;margin:4px 0}.meta{margin:3px 0}.sheet{width:100%;border-collapse:collapse;table-layout:fixed}.sheet th,.sheet td{border:1px solid #7d8e99;padding:3px;text-align:center;overflow:hidden}.sheet th{background:#e8f1f3;font-size:7px}.sheet .student{text-align:left;width:125px}.sheet .adm{text-align:left;width:70px}.sheet .num{width:22px}.sheet .metric{width:36px}.legend{margin-top:9px;line-height:1.6}.footer{margin-top:8px;font-size:7px;color:#526875}.status{display:inline-block;padding:2px 5px;border:1px solid #7d8e99;border-radius:8px}
</style>
</head>
<body>
<div class="header">
    <h1>{{ $publication->school->name }}</h1>
    <h2>CLASS RESULT BROADSHEET</h2>
    <div class="meta"><b>Session:</b> {{ $publication->term->academicSession->name }} &nbsp; <b>Term:</b> {{ $publication->term->name }} &nbsp; <b>Class:</b> {{ $publication->schoolClass->name }} &nbsp; <b>Version:</b> {{ $publication->version }} &nbsp; <span class="status">{{ strtoupper($publication->status->value) }}</span></div>
</div>
<table class="sheet">
<thead>
<tr>
    <th class="num">S/N</th>
    <th class="adm">Admission No.</th>
    <th class="student">Student</th>
    @foreach($subjects as $subject)
        <th title="{{ $subject['subject'] }}">{{ $subject['subject_code'] ?: \Illuminate\Support\Str::limit($subject['subject'], 10, '') }}</th>
    @endforeach
    <th class="metric">Total</th>
    <th class="metric">Average</th>
    <th class="metric">Position</th>
</tr>
</thead>
<tbody>
@forelse($summaries as $summary)
    @php $rows = collect(data_get($summary->snapshot, 'subjects', []))->keyBy(fn ($row) => $row['subject_code'] ?: $row['subject']); @endphp
    <tr>
        <td>{{ $loop->iteration }}</td>
        <td class="adm">{{ data_get($summary->snapshot, 'student.admission_number') }}</td>
        <td class="student">{{ data_get($summary->snapshot, 'student.name') }}</td>
        @foreach($subjectKeys as $key)
            @php $row = $rows->get($key); @endphp
            <td>{{ data_get($row, 'status') === 'absent' ? 'ABS' : (data_get($row, 'total_score') ?? '—') }}</td>
        @endforeach
        <td><b>{{ number_format((float) $summary->total_score, 2) }}</b></td>
        <td><b>{{ number_format((float) $summary->average_score, 2) }}</b></td>
        <td><b>{{ $summary->class_position ?? '—' }}</b></td>
    </tr>
@empty
    <tr><td colspan="{{ 6 + $subjects->count() }}">No compiled student results were found.</td></tr>
@endforelse
</tbody>
</table>
<div class="legend"><b>Subject key:</b>
@foreach($subjects as $subject)
    {{ $subject['subject_code'] ?: \Illuminate\Support\Str::limit($subject['subject'], 10, '') }} = {{ $subject['subject'] }}@if(!$loop->last); @endif
@endforeach
</div>
<div class="footer">Students: {{ $summaries->count() }} | Subjects: {{ $subjects->count() }} | Class average: {{ number_format((float) data_get($publication->statistics, 'class_average', 0), 2) }}% | Generated: {{ now()->format('d M Y H:i') }}</div>
</body>
</html>
