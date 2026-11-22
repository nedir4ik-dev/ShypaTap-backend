<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class Role extends Model {
 protected $fillable = ['name','slug','is_system'];
 protected $casts = ['is_system'=>'boolean'];
 public function permissions(): BelongsToMany { return $this->belongsToMany(Permission::class)->withTimestamps(); }
 public function clinics(): BelongsToMany { return $this->belongsToMany(Clinic::class, 'role_clinic_scopes')->withTimestamps(); }
}
