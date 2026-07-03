<?php
namespace App\Models;
use App\Enums\PublicationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class ResultPublication extends Model
{
    protected $fillable = ['school_id','academic_term_id','school_class_id','result_template_id','version','status','statistics','checksum','compiled_by','released_by','compiled_at','released_at'];
    protected function casts(): array { return ['status'=>PublicationStatus::class,'statistics'=>'array','compiled_at'=>'datetime','released_at'=>'datetime']; }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function term(): BelongsTo { return $this->belongsTo(AcademicTerm::class, 'academic_term_id'); }
    public function schoolClass(): BelongsTo { return $this->belongsTo(SchoolClass::class); }
    public function template(): BelongsTo { return $this->belongsTo(ResultTemplate::class, 'result_template_id'); }
    public function summaries(): HasMany { return $this->hasMany(ResultSummary::class); }
}
