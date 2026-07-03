<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
class SchoolClass extends Model
{
    protected $fillable = ['school_id','name','level','arm','class_teacher_id','is_active'];
    protected function casts(): array { return ['is_active'=>'boolean']; }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function classTeacher(): BelongsTo { return $this->belongsTo(User::class, 'class_teacher_id'); }
    public function students(): HasMany { return $this->hasMany(Student::class); }
    public function subjects(): BelongsToMany { return $this->belongsToMany(Subject::class, 'class_subjects')->withPivot('school_id')->withTimestamps(); }
    public function assignments(): HasMany { return $this->hasMany(TeacherAssignment::class); }
}
