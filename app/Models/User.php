<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'email', 'phone', 'avatar_path', 'password', 'role', 'is_active'])]
#[Hidden(['password', 'remember_token', 'avatar_path'])]
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $appends = ['avatar_url'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if (! $this->avatar_path || ! Storage::disk('public')->exists($this->avatar_path)) {
            return null;
        }

        return Storage::disk('public')->url($this->avatar_path);
    }

    public function doctorProfile(): HasOne
    {
        return $this->hasOne(DoctorProfile::class);
    }

    public function patientAppointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'patient_id');
    }

    public function createdAppointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'created_by_user_id');
    }

    public function staffClinics(): BelongsToMany
    {
        return $this->belongsToMany(Clinic::class, 'clinic_staff')
            ->using(ClinicStaff::class)
            ->withTimestamps();
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function settings(): HasMany
    {
        return $this->hasMany(UserSetting::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')->withPivot('clinic_id')->withTimestamps();
    }

    public function permissionOverrides(): HasMany
    {
        return $this->hasMany(UserPermissionOverride::class);
    }

    public function hasPermission(string $permission, ?int $clinicId = null): bool
    {
        if ($this->role === 'superadmin') return true;

        $permissionId = Permission::query()->where('name', $permission)->value('id');
        if (! $permissionId) return false;

        $override = $this->permissionOverrides()->where('permission_id', $permissionId)
            ->where(function ($query) use ($clinicId) { $query->whereNull('clinic_id'); if ($clinicId) $query->orWhere('clinic_id', $clinicId); })
            ->orderByRaw('clinic_id is null')->value('effect');
        if ($override !== null) return $override === 'allow';

        return $this->roles()->whereHas('permissions', fn ($query) => $query->where('name', $permission))
            ->where(function ($query) use ($clinicId) { $query->whereNull('user_roles.clinic_id'); if ($clinicId) $query->orWhere('user_roles.clinic_id', $clinicId); })
            ->exists();
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
