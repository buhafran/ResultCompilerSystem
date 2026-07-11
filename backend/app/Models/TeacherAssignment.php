<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class TeacherAssignment extends Model
{
    protected $fillable = ['school_id','user_id','academic_term_id','school_class_id','subject_id'];
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function teacher(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
    public function term(): BelongsTo { return $this->belongsTo(AcademicTerm::class, 'academic_term_id'); }
    public function schoolClass(): BelongsTo { return $this->belongsTo(SchoolClass::class); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
}
