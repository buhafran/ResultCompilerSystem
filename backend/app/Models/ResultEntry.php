<?php
namespace App\Models;
use App\Enums\ResultEntryStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ResultEntry extends Model
{
    protected $fillable = ['school_id','academic_term_id','school_class_id','subject_id','student_id','teacher_id','ca_score','exam_score','total_score','grade','remark','status','subject_position','lock_version','submitted_at'];
    protected function casts(): array { return ['ca_score'=>'decimal:2','exam_score'=>'decimal:2','total_score'=>'decimal:2','status'=>ResultEntryStatus::class,'submitted_at'=>'datetime']; }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function term(): BelongsTo { return $this->belongsTo(AcademicTerm::class, 'academic_term_id'); }
    public function schoolClass(): BelongsTo { return $this->belongsTo(SchoolClass::class); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function teacher(): BelongsTo { return $this->belongsTo(User::class, 'teacher_id'); }
}
