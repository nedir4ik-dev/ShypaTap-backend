<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Payment extends Model {
 protected $fillable=['appointment_id','amount','currency','status','payment_method','paid_at','provider_reference'];
 protected function casts(): array { return ['amount'=>'decimal:2','paid_at'=>'datetime']; }
 public function appointment(): BelongsTo { return $this->belongsTo(Appointment::class); }
}
