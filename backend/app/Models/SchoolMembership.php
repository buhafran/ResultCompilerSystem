<?php
namespace App\Models;
use App\Enums\MembershipRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class SchoolMembership extends Model
{
    protected $table = 'school_user';
    protected $fillable = ['school_id','user_id','role','is_active'];
    protected function casts(): array { return ['role'=>MembershipRole::class,'is_active'=>'boolean']; }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
