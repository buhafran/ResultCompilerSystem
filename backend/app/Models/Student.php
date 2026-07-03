<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
class Student extends Model
{
    use SoftDeletes;
    protected $fillable = ['school_id','school_class_id','admission_number','first_name','middle_name','last_name','gender','date_of_birth','photo_path','portal_pin_hash','is_active'];
    protected $hidden = ['portal_pin_hash'];
    protected function casts(): array { return ['date_of_birth'=>'date','is_active'=>'boolean']; }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function schoolClass(): BelongsTo { return $this->belongsTo(SchoolClass::class); }
    public function resultEntries(): HasMany { return $this->hasMany(ResultEntry::class); }
    public function summaries(): HasMany { return $this->hasMany(ResultSummary::class); }
    public function getFullNameAttribute(): string { return trim("{$this->first_name} {$this->middle_name} {$this->last_name}"); }
}
