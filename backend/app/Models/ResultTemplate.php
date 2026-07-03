<?php
namespace App\Models;
use App\Enums\TemplateLayout;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ResultTemplate extends Model
{
    protected $fillable = ['school_id','name','layout','settings','is_default'];
    protected function casts(): array { return ['layout'=>TemplateLayout::class,'settings'=>'array','is_default'=>'boolean']; }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
}
