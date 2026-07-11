<?php
    $firstSummary = $summaries->first();
    $subjects = collect(data_get($firstSummary?->snapshot, 'subjects', []))->values();
    $subjectKeys = $subjects->map(fn ($subject) => $subject['subject_code'] ?: $subject['subject']);
?>
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
    <h1><?php echo e($publication->school->name); ?></h1>
    <h2>CLASS RESULT BROADSHEET</h2>
    <div class="meta"><b>Session:</b> <?php echo e($publication->term->academicSession->name); ?> &nbsp; <b>Term:</b> <?php echo e($publication->term->name); ?> &nbsp; <b>Class:</b> <?php echo e($publication->schoolClass->name); ?> &nbsp; <b>Version:</b> <?php echo e($publication->version); ?> &nbsp; <span class="status"><?php echo e(strtoupper($publication->status->value)); ?></span></div>
</div>
<table class="sheet">
<thead>
<tr>
    <th class="num">S/N</th>
    <th class="adm">Admission No.</th>
    <th class="student">Student</th>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
        <th title="<?php echo e($subject['subject']); ?>"><?php echo e($subject['subject_code'] ?: \Illuminate\Support\Str::limit($subject['subject'], 10, '')); ?></th>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    <th class="metric">Total</th>
    <th class="metric">Average</th>
    <th class="metric">Position</th>
</tr>
</thead>
<tbody>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $summaries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $summary): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
    <?php $rows = collect(data_get($summary->snapshot, 'subjects', []))->keyBy(fn ($row) => $row['subject_code'] ?: $row['subject']); ?>
    <tr>
        <td><?php echo e($loop->iteration); ?></td>
        <td class="adm"><?php echo e(data_get($summary->snapshot, 'student.admission_number')); ?></td>
        <td class="student"><?php echo e(data_get($summary->snapshot, 'student.name')); ?></td>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $subjectKeys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <?php $row = $rows->get($key); ?>
            <td><?php echo e(data_get($row, 'status') === 'absent' ? 'ABS' : (data_get($row, 'total_score') ?? '—')); ?></td>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        <td><b><?php echo e(number_format((float) $summary->total_score, 2)); ?></b></td>
        <td><b><?php echo e(number_format((float) $summary->average_score, 2)); ?></b></td>
        <td><b><?php echo e($summary->class_position ?? '—'); ?></b></td>
    </tr>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    <tr><td colspan="<?php echo e(6 + $subjects->count()); ?>">No compiled student results were found.</td></tr>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</tbody>
</table>
<div class="legend"><b>Subject key:</b>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
    <?php echo e($subject['subject_code'] ?: \Illuminate\Support\Str::limit($subject['subject'], 10, '')); ?> = <?php echo e($subject['subject']); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$loop->last): ?>; <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
</div>
<div class="footer">Students: <?php echo e($summaries->count()); ?> | Subjects: <?php echo e($subjects->count()); ?> | Class average: <?php echo e(number_format((float) data_get($publication->statistics, 'class_average', 0), 2)); ?>% | Generated: <?php echo e(now()->format('d M Y H:i')); ?></div>
</body>
</html>
<?php /**PATH /Users/buhafran/iCloud Drive (Archive)/Documents/Code/ResultSystem-2/backend/resources/views/result/pdf/broadsheet.blade.php ENDPATH**/ ?>