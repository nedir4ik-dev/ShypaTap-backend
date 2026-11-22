<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\Pivot;

#[Fillable(['clinic_id', 'user_id', 'permissions'])]
class ClinicStaff extends Pivot
{
    protected $table = 'clinic_staff';


    public $incrementing = true;

    protected function casts(): array
    {
        return ['permissions' => 'array'];
    }
}
