<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class Subject extends Model
{
    protected $fillable = ['school_id','name','code','is_active'];
    protected function casts(): array { return ['is_active'=>'boolean']; }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function classes(): BelongsToMany { return $this->belongsToMany(SchoolClass::class, 'class_subjects')->withPivot('school_id')->withTimestamps(); }
}
