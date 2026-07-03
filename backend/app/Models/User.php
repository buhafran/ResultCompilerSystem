<?php

namespace App\Models;

use App\Enums\MembershipRole;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser, HasTenants
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'is_super_admin', 'last_school_id'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
        ];
    }
    public function teacherAssignments()
    {
        return $this->hasMany(TeacherAssignment::class);
    }

    public function schools(): BelongsToMany
    {
        return $this->belongsToMany(School::class)
            ->withPivot(['id', 'role', 'is_active'])
            ->withTimestamps();
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(SchoolMembership::class);
    }

    public function activeSchools(): BelongsToMany
    {
        return $this->belongsToMany(School::class)
            ->withPivot(['id', 'role', 'is_active'])
            ->wherePivot('is_active', true)
            ->where('schools.is_active', true)
            ->withTimestamps();
    }

    public function lastSchool(): BelongsTo
    {
        return $this->belongsTo(School::class, 'last_school_id');
    }



    public function accessibleSchools(): Collection
    {
        if ($this->is_super_admin) {
            return School::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }

        return $this->activeSchools()
            ->orderBy('schools.name')
            ->get();
    }

    public function getTenants(Panel $panel): Collection
    {
        return $this->accessibleSchools();
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return $tenant instanceof School && (
            $this->is_super_admin || $this->memberships()
                ->where('school_id', $tenant->getKey())
                ->where('is_active', true)
                ->exists()
        );
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'platform'
            ? $this->is_super_admin
            : $this->is_super_admin || $this->memberships()->where('is_active', true)->exists();
    }

    public function roleIn(School|int $school): ?MembershipRole
    {
        $schoolId = $school instanceof School ? $school->getKey() : $school;
        $value = $this->memberships()
            ->where('school_id', $schoolId)
            ->where('is_active', true)
            ->value('role');

        return $value instanceof MembershipRole ? $value : ($value ? MembershipRole::tryFrom($value) : null);
    }

    public function isSchoolManager(School|int $school): bool
    {
        return $this->is_super_admin || in_array(
            $this->roleIn($school),
            [MembershipRole::SchoolAdmin, MembershipRole::ExamOfficer],
            true,
        );
    }
    
}
