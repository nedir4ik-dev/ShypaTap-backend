<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class UserPermissionOverride extends Model {
 protected $fillable = ['user_id','permission_id','clinic_id','effect'];
 public function permission(): BelongsTo { return $this->belongsTo(Permission::class); }
}
