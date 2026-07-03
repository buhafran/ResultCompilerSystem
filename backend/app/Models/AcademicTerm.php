<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class AcademicTerm extends Model
{
    protected $fillable = ['school_id','academic_session_id','name','starts_on','ends_on','is_active','is_locked'];
    protected function casts(): array { return ['starts_on'=>'date','ends_on'=>'date','is_active'=>'boolean','is_locked'=>'boolean']; }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function academicSession(): BelongsTo { return $this->belongsTo(AcademicSession::class); }
    public function entries(): HasMany { return $this->hasMany(ResultEntry::class); }
}
