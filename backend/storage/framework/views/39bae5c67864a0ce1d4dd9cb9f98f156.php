<?php
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
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<style>
@page{margin:24px}body{font-family:DejaVu Sans,sans-serif;color:#18303f;font-size:10px}.header{border:2px solid <?php echo e($primary); ?>;padding:12px;text-align:center;position:relative}.logo{position:absolute;left:14px;top:12px;width:62px;height:62px;object-fit:contain}.header h1{margin:0 70px 5px;font-size:21px;color:<?php echo e($primary); ?>}.title{background:<?php echo e($primary); ?>;color:#fff;text-align:center;padding:8px;font-weight:bold;letter-spacing:1px;margin:8px 0;border-bottom:3px solid <?php echo e($accent); ?>}.info{width:100%;border-collapse:collapse;margin:8px 0 12px}.info td{border:1px solid #bfcdd4;padding:7px;width:33%}.info small{display:block;color:#6a7e89;font-size:8px;text-transform:uppercase}.student-photo-cell{width:92px;text-align:center;vertical-align:middle}.student-photo{width:78px;height:90px;object-fit:cover;border:1px solid #9eb2bd;padding:2px;background:#fff}.results{width:100%;border-collapse:collapse}.results th{background:#e9f3f1;border:1px solid #9eb9b3;padding:6px}.results td{border:1px solid #c4d0d5;padding:6px}.subject-name{font-weight:bold}.subject-subtitle{display:block;font-size:8px;color:#526875;margin-top:2px}.center{text-align:center}.summary{width:100%;border-collapse:separate;border-spacing:5px;margin-top:10px}.summary td{background:#edf4f5;text-align:center;padding:8px}.comments{width:100%;border-collapse:separate;border-spacing:6px;margin-top:10px}.comments td{border:1px solid #c4d0d5;padding:8px;width:50%;height:58px;vertical-align:top}.approvals{width:100%;margin-top:18px}.approvals td{width:50%;text-align:center;vertical-align:bottom;padding:0 45px}.line{border-top:1px solid #718894;padding-top:4px}.signature{height:35px;max-width:120px;object-fit:contain}.verify{border:1px dashed <?php echo e($primary); ?>;margin-top:10px;padding:8px;font-size:8px;display:table;width:100%}.verify-text{display:table-cell;vertical-align:middle}.verify-qr{display:table-cell;width:74px;text-align:right;vertical-align:middle}.verify-qr img{width:68px;height:68px}.scale{font-size:8px;margin-top:7px}
</style>
</head>
<body>
<div class="header">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($logo): ?><img class="logo" src="<?php echo e($logo); ?>" alt=""><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <h1><?php echo e(data_get($snapshot,'school.name')); ?></h1>
    <div><?php echo e(data_get($snapshot,'school.address')); ?></div>
    <em><?php echo e(data_get($snapshot,'school.motto')); ?></em>
</div>
<div class="title"><?php echo e($settings['header_note'] ?? 'TERMINAL ACADEMIC REPORT'); ?></div>
<table class="info">
    <tr>
        <td><small>Student</small><b><?php echo e(data_get($snapshot,'student.name')); ?></b></td>
        <td><small>Admission number</small><b><?php echo e(data_get($snapshot,'student.admission_number')); ?></b></td>
        <td><small>Class</small><b><?php echo e(data_get($snapshot,'academic.class')); ?></b></td>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($studentPhoto): ?><td class="student-photo-cell" rowspan="2"><img class="student-photo" src="<?php echo e($studentPhoto); ?>" alt=""></td><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </tr>
    <tr>
        <td><small>Session</small><b><?php echo e(data_get($snapshot,'academic.session')); ?></b></td>
        <td><small>Term</small><b><?php echo e(data_get($snapshot,'academic.term')); ?></b></td>
        <td><small>Gender</small><b><?php echo e(ucfirst(data_get($snapshot,'student.gender','—'))); ?></b></td>
    </tr>
</table>
<table class="results">
    <thead>
        <tr>
            <th>Subject</th><th>CA (30)</th><th>Exam (70)</th><th>Total</th><th>Grade</th>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showSubjectPosition): ?><th>Position</th><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <th>Remark</th>
        </tr>
    </thead>
    <tbody>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <tr>
                <td><span class="subject-name"><?php echo e($row['subject']); ?></span><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($row['subject_subtitle'])): ?><span class="subject-subtitle"><?php echo e($row['subject_subtitle']); ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></td>
                <td class="center"><?php echo e($row['ca_score']??'—'); ?></td>
                <td class="center"><?php echo e($row['exam_score']??'—'); ?></td>
                <td class="center"><?php echo e($row['total_score']??'—'); ?></td>
                <td class="center"><b><?php echo e($row['grade']); ?></b></td>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showSubjectPosition): ?><td class="center"><?php echo e($row['position']??'—'); ?></td><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <td><?php echo e($row['remark']); ?></td>
            </tr>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </tbody>
</table>
<table class="summary">
    <tr>
        <td>Total<br><b><?php echo e(number_format($sum['total_score']??0,2)); ?></b></td>
        <td>Average<br><b><?php echo e(number_format($sum['average_score']??0,2)); ?>%</b></td>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showClassPosition): ?><td>Position<br><b><?php echo e($sum['class_position']??'—'); ?></b></td><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <td>Class size<br><b><?php echo e($sum['class_size']??'—'); ?></b></td>
        <td>Subjects<br><b><?php echo e($sum['subject_count']??count($subjects)); ?></b></td>
        <td>Class high<br><b><?php echo e(number_format($sum['highest_average']??0,2)); ?>%</b></td>
        <td>Class low<br><b><?php echo e(number_format($sum['lowest_average']??0,2)); ?>%</b></td>
    </tr>
</table>
<table class="comments"><tr><td><b>Class teacher's comment</b><br><br><?php echo e($summary->teacher_comment?:'—'); ?></td><td><b>Principal's comment</b><br><br><?php echo e($summary->principal_comment?:'—'); ?></td></tr></table>
<table class="approvals"><tr><td><div class="line">Class Teacher</div></td><td><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($signature): ?><img class="signature" src="<?php echo e($signature); ?>" alt=""><br><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><div class="line"><?php echo e(data_get($snapshot,'school.principal_name') ?: 'Principal'); ?></div></td></tr></table>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(data_get($snapshot,'next_term_begins_on')): ?><div class="scale"><b>Next term begins:</b> <?php echo e(\Carbon\Carbon::parse(data_get($snapshot,'next_term_begins_on'))->format('d F Y')); ?></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($settings['show_grading_scale']??true): ?><div class="scale"><b>Grading scale:</b> <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($snapshot['grading_scale']??[]); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $band): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?> <?php echo e($band['grade']); ?> (<?php echo e($band['min']); ?>+) <?php echo e($band['remark']); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$loop->last): ?> | <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?> <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showVerification): ?>
    <div class="verify">
        <div class="verify-text"><b>Verification:</b> Scan the QR code to verify this result.<br><b>Code:</b> <?php echo e($verificationCode); ?></div>
        <div class="verify-qr"><img src="<?php echo e($verificationQr); ?>" alt="Verification QR code"></div>
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</body></html>
<?php /**PATH /Users/buhafran/iCloud Drive (Archive)/Documents/Code/ResultSystem-2/backend/resources/views/result/pdf/report.blade.php ENDPATH**/ ?>