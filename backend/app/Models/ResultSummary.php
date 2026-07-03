<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ResultSummary extends Model
{
    protected $fillable = ['result_publication_id','school_id','academic_term_id','school_class_id','student_id','total_score','average_score','subject_count','class_position','teacher_comment','principal_comment','ai_comment_generated','public_token','snapshot','released_at'];
    protected function casts(): array { return ['total_score'=>'decimal:2','average_score'=>'decimal:2','ai_comment_generated'=>'boolean','snapshot'=>'array','released_at'=>'datetime']; }
    public function publication(): BelongsTo { return $this->belongsTo(ResultPublication::class, 'result_publication_id'); }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function term(): BelongsTo { return $this->belongsTo(AcademicTerm::class, 'academic_term_id'); }
    public function schoolClass(): BelongsTo { return $this->belongsTo(SchoolClass::class); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
}
