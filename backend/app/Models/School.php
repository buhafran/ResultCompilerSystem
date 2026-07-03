<?php

namespace App\Models;

use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class School extends Model implements HasName
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'custom_domain', 'motto', 'about', 'address', 'phone', 'email',
        'logo_path', 'principal_name', 'principal_signature_path', 'next_term_begins_on',
        'settings', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'is_active' => 'boolean',
            'next_term_begins_on' => 'date',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getFilamentName(): string
    {
        return $this->name;
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['role', 'is_active'])
            ->withTimestamps();
    }

    public function academicSessions(): HasMany { return $this->hasMany(AcademicSession::class); }
    public function terms(): HasMany { return $this->hasMany(AcademicTerm::class); }
    public function classes(): HasMany { return $this->hasMany(SchoolClass::class); }
    public function subjects(): HasMany { return $this->hasMany(Subject::class); }
    public function students(): HasMany { return $this->hasMany(Student::class); }
    public function templates(): HasMany { return $this->hasMany(ResultTemplate::class); }
    public function slides(): HasMany { return $this->hasMany(SchoolSlide::class)->orderBy('sort_order')->orderBy('id'); }

    public function setting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings ?? [], $key, $default);
    }
}
